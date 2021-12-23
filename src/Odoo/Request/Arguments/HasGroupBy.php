<?php

namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;

trait HasGroupBy
{
    protected ?array $groupBy = null;

    /**
     * @param array $groupBy
     * @return static
     */
    public function groupBy(array $groupBy): static
    {
        $this->groupBy = $groupBy;
        return $this;
    }

    public function hasGroupBy()
    {
        return null !== $this->groupBy;
    }
}