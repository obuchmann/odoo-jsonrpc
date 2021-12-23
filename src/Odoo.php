<?php


namespace Obuchmann\OdooJsonRpc;


use Obuchmann\OdooJsonRpc\Odoo\Casts\Cast;
use Obuchmann\OdooJsonRpc\Odoo\Casts\CastHandler;
use Obuchmann\OdooJsonRpc\Odoo\Config;
use Obuchmann\OdooJsonRpc\Odoo\Context;
use Obuchmann\OdooJsonRpc\Odoo\Endpoint\CommonEndpoint;
use Obuchmann\OdooJsonRpc\Odoo\Endpoint\ObjectEndpoint;
use Obuchmann\OdooJsonRpc\Odoo\Models\Version;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Options;
use Obuchmann\OdooJsonRpc\Odoo\Request\Request;

class Odoo
{
    protected CommonEndpoint $common;
    protected ObjectEndpoint $object;

    protected ?int $uid = null;
    protected Context $context;

    public function __construct(
        protected Config $config,
        ?Context $context = null
    )
    {
        $this->common = new CommonEndpoint($this->config);
        $this->context = $context ?? new Context();
    }

    public static function registerCast(Cast $cast)
    {
        CastHandler::registerCast($cast);
    }


    /**
     * @param Context $context
     */
    public function setContext(Context $context): void
    {
        $this->context = $context;
        $this->object?->setContext($context);
    }

    private function authenticate()
    {
        $this->uid = $this->common->authenticate();
        $this->object = new ObjectEndpoint($this->config, $this->context, $this->uid);
    }

    public function connect(): void
    {
        if(!$this->uid){
            $this->authenticate();
        }
    }

    public function getUid(): int
    {
        return $this->uid;
    }

    public function version(): Version
    {
        return $this->common->version();
    }

    public function checkAccessRights(
        string $model,
        string $permission,
        ?Options $options = null
    ): bool
    {
        $this->connect();
        return $this->object->checkAccessRights($model, $permission, $options);
    }

    public function can(
        string $model,
        string $permission,
        ?Options $options = null
    ): bool
    {
        return $this->checkAccessRights($model, $permission, $options);
    }

    public function model(string $model): Odoo\Request\RequestBuilder
    {
        $this->connect();
        return $this->object->model($model);
    }

    public function search(
        string $model,
        ?Domain $domain = null,
        int $offset = 0,
        ?int $limit = null,
        ?string $order = null,
        ?Options $options = null
    ): array
    {
        $this->connect();
        return $this->object->search($model, $domain, $offset, $limit, $order, $options);
    }

    public function read(
        string $model,
        array $ids,
        array $fields = [],
        ?Options $options = null
    ): array
    {
        $this->connect();
        return $this->object->read($model, $ids, $fields, $options);
    }

    public function find(
        string $model,
        int $id,
        array $fields = [],
        ?Options $options = null
    ): ?object
    {
        return $this->read($model, [$id], $fields, $options)[0] ?? null;
    }

    public function searchRead(
        string $model,
        ?Domain $domain = null,
        ?array $fields = null,
        int $offset = 0,
        ?int $limit = null,
        ?string $order = null,
        ?Options $options = null
    ): array
    {
        $this->connect();
        return $this->object->searchRead($model, $domain, $fields, $offset, $limit, $order, $options);
    }

    public function readGroup(
        string $model,
        array $groupBy,
        ?Domain $domain = null,
        ?array $fields = null,
        int $offset = 0,
        ?int $limit = null,
        ?string $order = null,
        ?Options $options = null
    )
    {
        $this->connect();
        return $this->object->readGroup($model, $groupBy, $domain, $fields, $offset, $limit, $order, $options);
    }

    public function fieldsGet(
        string $model,
        ?array $fields = null,
        ?array $attributes = null,
        ?Options $options = null
    ): object
    {
        $this->connect();
        return $this->object->fieldsGet($model, $fields, $attributes, $options);
    }

    public function listModelFields(
        string $model,
        ?array $fields = null,
        ?array $attributes = null,
        ?Options $options = null
    ): object
    {
        return $this->fieldsGet($model, $fields, $attributes, $options);
    }

    public function create(
        string $model,
        array $values,
        ?Options $options = null
    ): bool|int
    {
        $this->connect();
        return $this->object->create($model, $values, $options);
    }

    public function unlink(
        string $model,
        array $ids,
        ?Options $options = null
    ): bool
    {
        $this->connect();
        return $this->object->unlink($model, $ids, $options);
    }

    public function deleteById(
        string $model,
        int $id,
        ?Options $options = null
    ): bool
    {
        return $this->unlink($model, [$id], $options);
    }

    public function write(
        string $model,
        array $ids,
        array $values,
        ?Options $options = null
    ): bool
    {
        $this->connect();
        return $this->object->write($model, $ids, $values, $options);
    }

    public function updateById(
        string $model,
        int $id,
        array $values,
        ?Options $options = null
    ): bool
    {
        return $this->write($model, [$id], $values, $options);
    }

    public function execute(
        Request $request,
        ?Options $options = null
    )
    {
        $this->connect();
        return $this->object->execute($request, $options);
    }


}