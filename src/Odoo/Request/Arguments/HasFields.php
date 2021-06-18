<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;


trait HasFields
{
    protected ?array $fields = null;

    /**
     * @param array $fields
     * @return static
     */
    public function fields(array $fields): static
    {
        $this->fields = $fields;
        return $this;
    }
}