<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

class Unlink extends Request
{

    /**
     * Unlink constructor.
     * @param string $model
     * @param int[] $ids
     */
    public function __construct(
        string $model,
        private array $ids,
    )
    {
        parent::__construct($model, 'unlink');
    }

    public function toArray(): array
    {
        return [
            $this->ids
        ];
    }

}