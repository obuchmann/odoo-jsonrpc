<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

/**
 * Class CheckAccessRights
 *
 * Checks permissions
 * @package Obuchmann\OdooJsonRpc\Odoo\Request
 */
class CheckAccessRights extends Request
{

    /**
     * CheckAccessRights constructor.
     * @param string $model
     * @param string $permission
     */
    public function __construct(
        string $model,
        private string $permission
    )
    {
        parent::__construct($model, 'check_access_rights');
    }


    public function toArray(): array
    {
        return [
            $this->permission
        ];
    }
}