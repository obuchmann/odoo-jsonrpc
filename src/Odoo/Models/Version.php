<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Models;


use JetBrains\PhpStorm\Immutable;
use Obuchmann\OdooJsonRpc\Attributes\Field;
use Obuchmann\OdooJsonRpc\Odoo\Mapping\HasFields;

class Version
{
    use HasFields;

    // Empty
    public ?int $id = null;

    #[Field('protocol_version')]
    public int $protocolVersion;

    #[Field('server_version')]
    public string $serverVersion;

    #[Field('server_serie')]
    public string $serverSerie;

    #[Field('server_version_info')]
    public array $serverVersionInfo;
}