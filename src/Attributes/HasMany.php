<?php

namespace Obuchmann\OdooJsonRpc\Attributes;

use Attribute;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Attribute(Attribute::TARGET_PROPERTY)] // Target the property that will hold the array of related objects
class HasMany implements OdooAttribute
{
    /**
     * @param class-string<OdooModel> $related The related OdooModel class FQCN.
     * @param string $foreignKey The foreign key field name on the *related* model's Odoo definition that points back to this model.
     * @param string|null $odooRelationshipField The actual Odoo field name on *this* model that holds the list of IDs (e.g., 'order_line').
     *                                            Often not needed for loading, but useful for saving/creating. Can be omitted if loading only.
     */
    public function __construct(
        public string $related,
        public string $foreignKey, // e.g., 'order_id' (the Odoo field name on the related model)
        public ?string $odooRelationshipField = null // e.g., 'order_line' (Odoo field on this model) - Kept for potential dehydrate use
    )
    {
        // Note: The 'name' property from the original HasMany attribute seems redundant
        // if we target the PHP property directly. We derive the PHP property name via reflection.
        // We keep '$odooRelationshipField' for potential use in dehydrate/saving logic if needed.
    }
}