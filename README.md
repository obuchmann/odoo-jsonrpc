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

// Check Access rights in model syntax

$check = $odoo->model('res.partner')
            ->can('read');
            
// Use Domain for Search
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
}


class Controller{

    public function index(){
        // Find Model by Id
        $partner = Partner::find(1);
        
        // Search Model
        $partner = Partner::query()
            ->where('name', '=', 'Azure Interior')
            ->first();
        
        // Update Model
        $partner->name = "Dagobert Duck";
        $partner->save();
        
        // Create returning ID
        $partner = new Partner();
        $partner->name = 'Tester';
        $partner->save();               
    }
}
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


For more examples take a look at the tests directory.


## Tests

```bash
composer test
```