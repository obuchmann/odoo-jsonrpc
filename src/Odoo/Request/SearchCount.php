<?php

namespace Obuchmann\OdooJsonRpc\Odoo\Request;

use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;

class SearchCount extends Request
{
    public function __construct(
        string $model,
        protected Domain $domain
    )
    {
        parent::__construct( $model, 'search_count');
    }

    public function toArray(): array
    {
        return [
            $this->domain->toArray(),
        ];
    }
}
