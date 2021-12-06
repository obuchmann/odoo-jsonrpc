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