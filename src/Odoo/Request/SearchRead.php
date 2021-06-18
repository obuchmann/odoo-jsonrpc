<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;
use Obuchmann\OdooJsonRpc\JsonRpc\Client;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;

/**
 * Class SearchRead
 *
 * Searches for models and returns values
 * @package Obuchmann\OdooJsonRpc\Odoo\Request
 */
class SearchRead extends Request
{
    /**
     * SearchRead constructor.
     * @param string $model
     * @param Domain $domain
     * @param array|null $fields
     * @param int $offset
     * @param int|null $limit
     * @param string|null $order
     */
    public function __construct(
        string $model,
        protected Domain $domain,
        protected ?array $fields = null,
        protected int $offset = 0,
        protected ?int $limit = null,
        protected ?string $order = null
    )
    {
        parent::__construct($model, 'search_read');
    }

    public function toArray(): array
    {
        return [
            $this->domain->toArray(),
            $this->fields,
            $this->offset,
            $this->limit,
            $this->order
        ];
    }
}