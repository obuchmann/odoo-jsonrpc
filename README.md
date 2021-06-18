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
php artisan vendor:publish --provider="Obuchmann\OdooServiceProvider.php" --tag="config"
```

## Usage

TODO 

For now take a look at the tests directory.


## Tests

```bash
composer test
```