<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

class Write extends Request
{

    /**
     * Write constructor.
     * @param string $model
     * @param int[] $ids
     * @param array $values
     */
    public function __construct(
        string $model,
        private array $ids,
        private array $values
    )
    {
        parent::__construct($model, 'write');
    }

    public function toArray(): array
    {
        return [
            $this->ids, $this->values
        ];
    }

}