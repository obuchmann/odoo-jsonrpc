<?php


namespace Obuchmann\OdooJsonRpc\Tests\Models;


use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Model('product.product')]
class Product extends OdooModel
{
    #[Field]
    public string $name;
}