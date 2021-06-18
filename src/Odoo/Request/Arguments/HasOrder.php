<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;


use JetBrains\PhpStorm\ExpectedValues;

trait HasOrder
{
    protected array $order = [];

    public function orderBy(string $order, #[ExpectedValues(['asc', 'desc'])] string $direction = 'asc')
    {
        $this->order[] = "$order $direction";
        return $this;
    }

    protected function getOrderString(): ?string
    {
        return empty($this->order) ? null : join(',', $this->order);
    }
}