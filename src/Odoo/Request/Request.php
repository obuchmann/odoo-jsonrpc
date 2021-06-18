<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Request;

use Obuchmann\OdooJsonRpc\JsonRpc\Client;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Options;

abstract class Request
{
    /**
     * Request constructor.
     * @param string $model
     * @param string $method
     */
    public function __construct(
        protected string $model,
        protected string $method
    )
    {
    }

    public abstract function toArray(): array;

    public function execute(
        Client $client,
        string $database,
        int $uid,
        string $password,
        Options $options
    )
    {
        return $client->execute_kw($database, $uid, $password, $this->model, $this->method, $this->toArray(), $options->toArray());
    }
}