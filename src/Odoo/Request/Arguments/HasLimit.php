<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;


trait HasLimit
{
    protected ?int $limit = null;

    /**
     * @param int|null $limit
     * @return static
     */
    public function limit(?int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

}