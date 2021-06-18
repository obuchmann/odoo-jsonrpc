<?php


namespace Obuchmann\OdooJsonRpc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class KeyName implements OdooAttribute
{

    public function __construct(
    )
    {
    }
}