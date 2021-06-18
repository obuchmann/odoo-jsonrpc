<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request\Arguments;



class Domain
{
    protected array $conditions = [];

    public function where(string $field, string $operator, $value)
    {
        $this->conditions[] = [$field, $operator, $value];
        return $this;
    }

    public function orWhere(string $field, string $operator, $value)
    {
        if ($this->isEmpty()) {
            throw new \RuntimeException("Or Term is not possible at start");
        }
        $this->conditions = array_merge(
            array_slice($this->conditions, 0, -1),
            ['|'],
            array_slice($this->conditions, -1, 1),
            [[$field, $operator, $value]]
        );
        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->conditions);
    }

    public function toArray(): array
    {
        return $this->conditions;
    }
}