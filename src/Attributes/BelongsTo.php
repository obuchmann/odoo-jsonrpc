<?php

namespace Obuchmann\OdooJsonRpc\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)] // Target the property that will hold the related *object*
class BelongsTo implements OdooAttribute
{
    /**
     * @param class-string<OdooModel> $related The related OdooModel class FQCN.
     * @param string $foreignKey The name of the *property* on this model that holds the foreign key ID.
     *                           Typically corresponds to an Odoo field like 'partner_id' which returns [id, name].
     *                           We assume a corresponding property like `public ?int $partnerId` exists, marked with #[Field('partner_id'), Key].
     */
    public function __construct(
        public string $related,
        public string $foreignKey // e.g., 'partnerId' (the PHP property name holding the ID)
    )
    {
    }
}