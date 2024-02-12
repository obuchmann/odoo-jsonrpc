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


For more examples take a look at the tests directory.


## Tests

```bash
composer test
```