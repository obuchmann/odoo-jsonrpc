<?php


namespace Obuchmann\OdooJsonRpc\Attributes;

use Attribute;

#[Attribute]
class HasMany
{

    public function __construct(
        public string $class,
        public string $name
    )
    {
    }
}