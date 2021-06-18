<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;


trait HasDomain
{
    protected Domain $domain;
    public function where(string $field, string $operator, $value)
    {
        $this->domain->where($field, $operator, $value);
        return $this;
    }

    public function orWhere(string $field, string $operator, $value)
    {
        $this->domain->orWhere($field, $operator, $value);
        return $this;
    }

}