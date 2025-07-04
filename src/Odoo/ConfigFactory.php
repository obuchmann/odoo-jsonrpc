<?php


namespace Obuchmann\LaravelOdooApi\Odoo;

use Obuchmann\OdooJsonRpc\Exceptions\ConfigurationException;
use Obuchmann\OdooJsonRpc\Odoo\Config;

class ConfigFactory
{
    protected $config;

    /**
     * Config constructor.
     *
     * Prepare with config values
     *
     * @param array $config
     * @throws ConfigurationException
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return Config
     * @throws ConfigurationException
     */
    public function build(): Config
    {
        return new Config(
            $this->getRequired('database'),
            $this->getRequired('host'),
            $this->getRequired('username'),
            $this->getRequired('password'),
            $this->config['ssl_verify'] ?? true,
            $this->config['fixed_user_id'] ?? null
        );
    }

    private function getRequired(string $key)
    {
        if(!array_key_exists($key, $this->config)){
            throw new ConfigurationException("Missing required config $key");
        }
        return $this->config[$key];
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->config['host'] = $host;
    }

    /**
     * @param mixed $database
     */
    public function setDatabase($database): void
    {
        $this->config['database'] = $database;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username): void
    {
        $this->config['username'] = $username;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->config['password'] = $password;
    }



}