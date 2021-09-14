<?php


namespace Obuchmann\OdooJsonRpc\Tests\Models;


use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Attributes\Key;
use Obuchmann\OdooJsonRpc\Attributes\Model;
use Obuchmann\OdooJsonRpc\Odoo\OdooModel;

#[Model('res.partner')]
class Partner extends OdooModel
{

    #[Field]
    public string $name;

    #[Field('email')]
    public ?string $email;

    #[Field('parent_id'), Key]
    public ?int $parentId;

    #[Field('child_ids')]
    public ?array $childIds;

    public function parent(): Partner
    {
        return Partner::find($this->parentId);
    }

    public function childs()
    {
        return Partner::read($this->childIds);
    }
}