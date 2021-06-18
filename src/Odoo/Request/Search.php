<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

use Obuchmann\OdooJsonRpc\JsonRpc\Client;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;

/**
 * Class Search
 *
 * Searches for model ids
 * @package Obuchmann\OdooJsonRpc\Odoo\Request
 */
class Search extends Request
{
    /**
     * Search constructor.
     * @param string $model
     * @param Domain $domain
     * @param int $offset
     * @param int|null $limit
     * @param string|null $order
     * @param bool|null $count
     */
    public function __construct(
        string $model,
        protected Domain $domain,
        protected int $offset = 0,
        protected ?int $limit = null,
        protected ?string $order = null,
        protected ?bool $count = null,
    )
    {
        parent::__construct( $model, 'search');
    }

    public function toArray(): array
    {
        return [
            $this->domain->toArray(),
            $this->offset,
            $this->limit,
            $this->order,
            $this->count
        ];
    }
}