<?php

namespace Obuchmann\OdooJsonRpc\Tests\Models;

use Obuchmann\OdooJsonRpc\Attributes\BelongsTo;
use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Model('stock.picking')]
class StockPicking extends OdooModel
{
    #[Field]
    public string $name;

    #[BelongsTo(name: 'partner_id', class: Partner::class)]
    public ?Partner $partner;

    #[Field('location_id')]
    public ?int $locationId;

    #[Field('location_dest_id')]
    public ?int $locationDestId;

    #[Field('picking_type_id')]
    public ?int $pickingTypeId;
}
