<?php


namespace Obuchmann\OdooJsonRpc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Field implements OdooAttribute
{
    public function __construct(
        public ?string $name = null,
    )
    {

    }
}