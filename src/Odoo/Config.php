<?php


namespace Obuchmann\OdooJsonRpc\Odoo;

use JetBrains\PhpStorm\Immutable;

#[Immutable]
class Config
{

    public function __construct(
        protected string $database,
        protected string $host,
        protected string $username,
        protected string $password,
        protected bool $sslVerify
    ) {}

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return boolean
     */
    public function getSslVerify(): bool
    {
        return $this->sslVerify;
    }
}
