<?php


namespace Obuchmann\OdooJsonRpc\Tests\Models;


use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Model('purchase.order.line')]
class PurchaseOrderLine extends OdooModel
{
    #[Field]
    public string $name;

    #[Field('product_id')]
    public int $productId;

    #[Field('product_qty')]
    public int $productQuantity;

    #[Field('price_unit')]
    public float $priceUnit;
}