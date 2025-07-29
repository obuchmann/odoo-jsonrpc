<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

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
     * @param bool $count // Changed default to false
     */
    public function __construct(
        string $model,
        protected Domain $domain,
        protected int $offset = 0,
        protected ?int $limit = null,
        protected ?string $order = null,
        protected bool $count = false, // Changed default to false
    )
    {
        parent::__construct( $model, $count ? 'search_count' : 'search'); // Dynamically set method
    }

    public function toArray(): array
    {
        if ($this->count) {
            return [
                $this->domain->toArray(),
            ];
        }

        return [
            $this->domain->toArray(),
            $this->offset,
            $this->limit,
            $this->order,
            // $this->count is no longer sent as a parameter for 'search'
        ];
    }
}