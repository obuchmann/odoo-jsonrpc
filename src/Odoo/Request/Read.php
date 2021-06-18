<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

class Read extends Request
{

    /**
     * Read constructor.
     * @param string $model
     * @param int[] $ids
     * @param string[] $fields
     */
    public function __construct(
        string $model,
        private array $ids,
        private array $fields = []
    )
    {
        parent::__construct($model, 'read');
    }

    public function toArray(): array
    {
        return [
            $this->ids, $this->fields
        ];
    }
}