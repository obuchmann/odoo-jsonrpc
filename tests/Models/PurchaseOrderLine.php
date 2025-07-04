<?php


namespace Obuchmann\OdooJsonRpc\Tests\Models;


use Obuchmann\OdooJsonRpc\Attributes\BelongsTo;
use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\Key;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Model('purchase.order.line')]
class PurchaseOrderLine extends OdooModel
{
    #[Field]
    public string $name;

    #[BelongsTo('order_id', PurchaseOrder::class)]
    public PurchaseOrder $order;

    #[Field('order_id'), Key]
    public ?int $orderId;

    #[Field('product_id'), Key]
    public int $productId;

    #[Field('product_qty')]
    public int $productQuantity;

    #[Field('price_unit')]
    public float $priceUnit;

}