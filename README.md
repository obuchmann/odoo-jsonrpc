# Access Odoo via JsonRPC

Connect to odoo via the json-rpc api. If you are in a laravel project, this package registers a provider. But laravel is not required for this package.

## Installation

You can install the package via composer:

```bash
composer require obuchmann/odoo-jsonrpc
```

The service provider will automatically register itself if you are in a laravel project.

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Obuchmann\OdooJsonRpc\OdooServiceProvider" --tag="config"
```

## Usage

### Basic Usage

```php
use Obuchmann\OdooJsonRpc\Odoo;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;

$this->host = 'http://localhost:8069';
$this->username = 'admin';
$this->password = 'password';
$this->database = 'odoo';

// Connect to Odoo
$odoo = new Odoo(new Odoo\Config($database, $host, $username, $password));
$odoo->connect();


// Check Access rights (bool)
$check = $odoo->checkAccessRights('res.partner', 'read');

// List model fields
$fields = $odoo->listModelFields('res.partner');

// Check Access rights in model syntax

$check = $odoo->model('res.partner')
            ->can('read');
            
// Use Domain for Search
// You can also use orWhere() when building domains: $domain->orWhere('field', 'operator', 'value');
$isCompanyDomain = (new Domain())->where('is_company', '=', true);
$companyIds = $odoo->search('res.partner', $isCompanyDomain);

// read ids
$companies = $odoo->read('res.partner', $companyIds);

// search_read with model Syntax
$companies = $odoo->model('res.partner')
            ->where('is_company', '=', true)
            ->get();
            
// search_read with single item
$company = $odoo->model('res.partner')
            ->where('is_company', '=', true)
            ->where('name', '=', 'My Company')
            ->first();

// Count records directly
$count = $odoo->count('res.partner', $isCompanyDomain);

// Count records with model syntax
$count = $odoo->model('res.partner')
            ->where('is_company', '=', true)
            ->count();

// Group records and aggregate fields
$groupedData = $odoo->model('res.partner')
    ->where('active', '=', true)
    ->groupBy(['country_id']) // Fields to group by (e.g., 'country_id', ['category_id', 'state_id'])
    ->fields(['id', 'name']) // Fields to retrieve in each group
    ->get();
// This typically returns an array of objects, where each object has the grouped fields and any aggregated fields.
            
// create with model syntax
$partner = $odoo->model('res.partner')
            ->create([
                'name' => 'My Company',
                'is_company' => true
            ]);
            
// update with model syntax
$partner = $odoo->model('res.partner')
            ->where('name', '=', 'My Company')
            ->update([
                'name' => 'My New Company'
            ]);
// direct update by id            
$myCompanyId = 1;
$partner = $odoo->updateById('res.partner', $myCompanyId, [
    'name' => 'My New Company'
]);

// delete by id
$odoo->deleteById('res.partner', $myCompanyId);

// Execute arbitrary keywords (custom RPC calls)
$customResult = $odoo->executeKw('res.partner', 'check_access_rule', [[$myCompanyId], 'read']);
// The arguments for executeKw (the third parameter) depend on the specific Odoo method being called.

```


### Laravel Usage

```php

class Controller{

    public function index(\Obuchmann\OdooJsonRpc\Odoo $odoo){
        // Find Model by Id
        $product = $odoo->find('product.template', 1);
        
        // Update Model by ID
        $this->odoo->updateById('product.product', $product->id, [
            'name' => $name,
        ]);
        
        // Create returning ID
        $id = $this->odoo
            ->create('res.partner', [
                'name' => 'Bobby Brown'
            ]);
        
        // Search for Models with or
        $partners = $this->odoo->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->orWhere('name', '=', 'Gregor Green')
            ->limit(5)
            ->orderBy('id', 'desc')
            ->get();
        
        // Update by Query
        $updateResponse = $this->odoo
            ->model('res.partner')
            ->where('name', '=', 'Bobby Brown')
            ->update([
                'name' => 'Dagobert Duck'
            ]);
    }
}
```

### Fixed User ID for Performance

In scenarios where performance is critical and the Odoo user context is static (e.g., in some Dockerized enterprise deployments or for specific integration users), you can bypass repeated authentication calls by using a fixed User ID.

This is achieved by setting the `ODOO_FIXED_USER_ID` environment variable or the `fixed_user_id` key in the configuration file (`config/odoo.php` in Laravel projects).

**How it works:**

If `fixed_user_id` is set to a valid, positive integer, the library will skip the standard username/password authentication step (the `common.authenticate` RPC call to Odoo) and will use this provided User ID for all subsequent operations. This can reduce latency by avoiding an extra network request for authentication on each instantiation or connection attempt.

**Configuration:**

1.  **Environment Variable:**
    ```bash
    ODOO_FIXED_USER_ID=123
    ```

2.  **Configuration File (e.g., `config/odoo.php` for Laravel):**
    ```php
    // config/odoo.php
    return [
        // ... other configurations
        'host' => env('ODOO_HOST',''),
        'database' => env('ODOO_DATABASE',''),
        'username' => env('ODOO_USERNAME', ''),
        'password' => env('ODOO_PASSWORD', ''),
        'fixed_user_id' => env('ODOO_FIXED_USER_ID', null), // <--- Add this line
        // ... other configurations
    ];
    ```

**Important Considerations:**

*   When `fixed_user_id` is used, the `username` and `password` configurations are ignored for the purpose of obtaining the User ID.
*   Ensure the provided User ID has the necessary access rights in Odoo for the operations your application will perform.
*   This feature is intended for specific use cases. For most applications, especially those with dynamic user contexts, the standard authentication flow is more appropriate.

### Laravel Models

Laravel Models are implemented with Attributes

```php
#[Model('res.partner')]
class Partner extends OdooModel
{
    #[Field]
    public string $name;

    #[Field('email')]
    public ?string $email;

    #[Field('country_id')] // The Odoo foreign key field name
    #[BelongsTo('country_id', Country::class)] // First param is Odoo FK name, second is related model class
    public ?Country $country;
    // Defines an inverse one-to-one or many-to-one relationship.
    // Parameters for #[BelongsTo]:
    //   - name (string): The name of the foreign key field in the Odoo model (e.g., 'country_id').
    //                    This name *must* match the name specified in the #[Field] attribute.
    //   - class (string): The fully qualified class name of the related OdooModel (e.g., Country::class).
    // Type hinting:
    //   - Should be type-hinted as nullable (e.g., ?Country) if the relationship can be optional.
    //   - Or non-nullable (e.g., Country) if it's mandatory (though Odoo relations are generally nullable).
    // Data access:
    //   - Accessing the property (e.g., $partner->country) returns an instance of the related model (Country)
    //     or null if the relation is not set or the related record doesn't exist.
    //   - Related model properties can be accessed using the nullsafe operator (e.g., $partner->country?->name).

    #[Field('child_ids')] // The Odoo field name on the current model holding related IDs
    #[HasMany(Partner::class, 'child_ids')] // First param is related model class, second is Odoo field name
    public array $children;
    // Defines a one-to-many or many-to-many relationship.
    // Parameters for #[HasMany]:
    //   - class (string): The fully qualified class name of the related OdooModel (e.g., Partner::class).
    //   - name (string): The name of the Odoo field on the *current* model that holds the IDs of the related models (e.g., 'child_ids').
    //                    This name *must* match the name specified in the #[Field] attribute.
    // Type hinting:
    //   - Should be type-hinted as `array` or `iterable`. The property will be an instance of `LazyHasMany`.

    // Lazy Loading Behavior:
    // The `HasMany` relationship implements lazy loading to optimize performance.
    // 1. Initial Fetch: When a model instance is fetched (e.g., `$partner = Partner::find(1);`),
    //    the `children` property is initialized with a `LazyHasMany` object, but no related data is fetched from Odoo yet.
    //    `$partner->children->isLoaded()` would return `false` at this point.
    // 2. Data Trigger: The actual child records are fetched from Odoo only when the `LazyHasMany` collection is first accessed.
    //    This includes actions like:
    //      - Iterating: `foreach ($partner->children as $child)`
    //      - Counting: `count($partner->children)`
    //      - Accessing an element: `$partner->children[0]`
    //    Once accessed, `$partner->children->isLoaded()` will return `true`.
    // 3. Performance Benefit: This avoids loading potentially large collections of related models unnecessarily
    //    if they are not actually used.

    // The `LazyHasMany` Wrapper Class:
    // This class wraps the collection of related models and provides array-like access.
    // - Implements `ArrayAccess`, `Iterator`, and `Countable`, allowing it to be used like a standard PHP array.
    // - `isLoaded(): bool`: Checks if the collection's data has been fetched from Odoo without triggering a load.
    //   Example: `if (!$partner->children->isLoaded()) { echo "Children not loaded yet."; }`
    // - `reload(): self`: Discards any currently loaded data and forces a fresh fetch from Odoo
    //   the next time the collection is accessed.
    //   Example: `$partner->children->reload(); // Data will be re-fetched on next access`
}

#[Model('res.country')]
class Country extends OdooModel
{
    #[Field]
    public string $name;
}


class Controller{

    public function examples(){
        // Find Model by Id
        $partner = Partner::find(1);
        
        // Accessing a BelongsTo relationship
        $countryName = $partner->country?->name;

        // Accessing a HasMany relationship (triggers loading if not already loaded)
        echo "Number of children: " . count($partner->children) . "\n";
        foreach($partner->children as $child){
            // $child is a Partner model instance
            echo "Child name: " . $child->name . "\n";
        }

        // Check if HasMany collection is loaded
        if ($partner->children->isLoaded()) {
            echo "Children collection is loaded.\n";
        } else {
            echo "Children collection is NOT loaded.\n";
        }

        // Force reload of HasMany collection
        $partner->children->reload();
        echo "Children collection reloaded. Accessing again will fetch fresh data.\n";
        // Example: The following line would trigger a new fetch from Odoo
        // $firstChild = $partner->children[0] ?? null;


        // List available fields for the Partner model
        $fields = Partner::listFields(); // Returns an array of field definitions

        // Search Model
        $partner = Partner::query()
            ->where('name', '=', 'Azure Interior')
            ->first();
        
        // Search model and retrieve only specific fields
        $partners = Partner::query()
            ->fields(['name', 'email']) // Specify fields to retrieve
            ->where('is_company', '=', true)
            ->get();

        // Search model with ordering
        $sortedPartners = Partner::query()
            ->orderBy('name', 'asc') // Order by name ascending
            ->limit(10)
            ->get();

        // Update Model
        // You can also use $partner->fill(['name' => 'Dagobert Duck', 'email' => 'test@example.com']) for mass assignment
        $partner->name = "Dagobert Duck";
        $partner->save();
        
        // Create model
        $newPartner = new Partner();
        $newPartner->name = 'Tester';
        // or using fill:
        // $newPartner->fill(['name' => 'Tester', 'is_company' => false]);
        $newPartner->save(); // The ID is set on $newPartner->id after saving. save() returns true on success.

        // Comparing models
        $anotherPartner = Partner::find(1);
        if ($partner->equals($anotherPartner)) {
            // Models are considered equal if they are of the same class and have the same ID
        }
    }
}

### Field Type Casting
For handling specific field types like dates or custom Odoo types, please refer to the [Casts](#casts) section.
```

### Casts

You can define a cast for your models. This is useful if you want to convert odoo fields to a specific type. There are some predefined casts for date and datetime fields.

Casts are global and can be registered in the Odoo class.

```php

// The basic datetime cast
\Obuchmann\OdooJsonRpc\Odoo::registerCast(new Odoo\Casts\DateTimeCast());

// a datetime cast that respects the timezone
\Obuchmann\OdooJsonRpc\Odoo::registerCast(new Odoo\Casts\DateTimeTimezoneCast(new \DateTimeZone('Europe/Berlin')));


// you can write custom casts by extending the Obuchmann\OdooJsonRpc\Odoo\Casts\Cast class
// example DateTimeCast

class DateTimeCast extends Cast
{

    public function getType(): string
    {
        return \DateTime::class;
    }

    public function cast($raw)
    {
        if($raw){
            try {
                return new \DateTime($raw);
            } catch (\Exception) {} // If no valid Date return null
        }
        return null;
    }

    public function uncast($value)
    {
        if($value instanceof \DateTime){
            return $value->format('Y-m-d H:i:s');
        }
    }
} 



```


For more detailed examples, please refer to the tests directory.


## Tests

```bash
composer test
```