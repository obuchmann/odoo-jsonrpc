<?php


namespace Obuchmann\OdooJsonRpc\Tests\Models;

use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\HasMany;
use Obuchmann\OdooJsonRpc\Attributes\Key;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Model('purchase.order')]
class PurchaseOrder extends OdooModel
{
    #[HasMany(PurchaseOrderLine::class, 'order_line')]
    public array|\ArrayAccess $lines;

    #[Field('partner_id'), Key]
    public int $partnerId;

    #[Field('date_order')]
    public \DateTime $orderDate;

    #[Field('date_approve')]
    public ?\DateTime $approveDate;
}