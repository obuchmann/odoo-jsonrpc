<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;


trait HasOffset
{
    protected int $offset = 0;

    /**
     * @param int $offset
     * @return static
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

}