<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Casts;


/**
 * Class Cast<T>
 *
 * @template T
 * @package Obuchmann\OdooJsonRpc\Odoo\Casts
 *
 */
abstract class Cast
{
    const WILDCARD = '*';

    public function applies($value): bool
    {
        return true;
    }

    public function handlesNullValues(): bool
    {
        return true;
    }

    public abstract function getType(): string;

    /**
     * @param $raw
     * @return T
     */
    public abstract function cast($raw);

    /**
     * @param $value T
     * @return mixed
     */
    public abstract function uncast($value);
}