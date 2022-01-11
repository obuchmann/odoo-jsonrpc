<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Endpoint;


use Obuchmann\OdooJsonRpc\Odoo\Config;
use Obuchmann\OdooJsonRpc\Odoo\Context;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Domain;
use Obuchmann\OdooJsonRpc\Odoo\Request\Arguments\Options;
use Obuchmann\OdooJsonRpc\Odoo\Request\CheckAccessRights;
use Obuchmann\OdooJsonRpc\Odoo\Request\Create;
use Obuchmann\OdooJsonRpc\Odoo\Request\FieldsGet;
use Obuchmann\OdooJsonRpc\Odoo\Request\Read;
use Obuchmann\OdooJsonRpc\Odoo\Request\ReadGroup;
use Obuchmann\OdooJsonRpc\Odoo\Request\Request;
use Obuchmann\OdooJsonRpc\Odoo\Request\RequestBuilder;
use Obuchmann\OdooJsonRpc\Odoo\Request\Search;
use Obuchmann\OdooJsonRpc\Odoo\Request\SearchRead;
use Obuchmann\OdooJsonRpc\Odoo\Request\Unlink;
use Obuchmann\OdooJsonRpc\Odoo\Request\Write;

class ObjectEndpoint extends Endpoint
{
    protected string $service = 'object';

    /**
     * ObjectEndpoint constructor.
     * @param Config $config
     * @param Context $context
     * @param int $uid
     */
    public function __construct(Config $config, protected Context $context, protected int $uid)
    {
        parent::__construct($config);
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context): void
    {
        $this->context = $context;
    }


    public function execute(Request $request, ?Options $options = null)
    {
        $options ??= new Options();

        $value = $request->execute(
            client: $this->getClient(),
            database: $this->getConfig()->getDatabase(),
            uid: $this->uid,
            password: $this->getConfig()->getPassword(),
            options: $options->withContext($this->context)
        );
        return $value;
    }

    public function model(string $model, ?Domain $domain = null): RequestBuilder
    {
        return new RequestBuilder(
            endpoint: $this,
            model: $model,
            domain: $domain ?? new Domain()
        );
    }

    public function checkAccessRights(string $model, string $permission, ?Options $options = null): bool
    {
        return $this->execute(new CheckAccessRights(
            model: $model,
            permission: $permission
        ), $options);
    }

    public function count(string $model, ?Domain $domain = null, int $offset = 0, ?int $limit = null, ?string $order = null, ?Options $options = null): int
    {
        return $this->execute(new Search(
            model: $model,
            domain: $domain ?? new Domain(),
            offset: $offset,
            limit: $limit,
            order: $order,
            count: true
        ), $options);
    }

    public function search(string $model, ?Domain $domain = null, int $offset = 0, ?int $limit = null, ?string $order = null, ?Options $options = null): array
    {
        return $this->execute(new Search(
            model: $model,
            domain: $domain ?? new Domain,
            offset: $offset,
            limit: $limit,
            order: $order
        ), $options);
    }

    public function read(string $model, array $ids, array $fields = [], ?Options $options = null): array
    {
        return $this->execute(new Read(
            model: $model,
            ids: $ids,
            fields: $fields
        ), $options);
    }


    public function searchRead(string $model, ?Domain $domain = null, ?array $fields = null, int $offset = 0, ?int $limit = null, ?string $order = null, ?Options $options = null): array
    {
        return $this->execute(new SearchRead(
            model: $model,
            domain: $domain ?? new Domain,
            fields: $fields,
            offset: $offset,
            limit: $limit,
            order: $order
        ), $options);
    }

    public function readGroup(string $model, array $groupBy, ?Domain $domain = null, ?array $fields = null, int $offset = 0, ?int $limit = null, ?string $order = null, ?Options $options = null): array
    {
        return $this->execute(new ReadGroup(
            model: $model,
            groupBy: $groupBy,
            domain: $domain,
            fields: $fields,
            offset: $offset,
            limit: $limit,
            order: $order
        ), $options);
    }

    public function fieldsGet(string $model, ?array $fields = null, ?array $attributes = null, ?Options $options = null): object
    {
        return $this->execute(new FieldsGet(
            model: $model,
            fields: $fields,
            attributes: $attributes
        ), $options);
    }

    public function create(string $model, array $values, ?Options $options = null): bool|int
    {
        return $this->execute(new Create(
            model: $model,
            values: $values
        ), $options);
    }

    public function unlink(string $model, array $ids, ?Options $options = null): bool
    {
        return $this->execute(new Unlink(
            model: $model,
            ids: $ids
        ), $options);
    }

    public function write(string $model, array $ids, array $values, ?Options $options = null): bool
    {
        return $this->execute(new Write(
            model: $model,
            ids: $ids,
            values: $values
        ), $options);
    }
}