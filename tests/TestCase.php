<?php


namespace Obuchmann\OdooJsonRpc\Tests;


use Obuchmann\OdooJsonRpc\Odoo;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected $odoo;

    /**
     * Demo credentials set.
     */
    protected $host;
    protected $database;
    protected $username;
    protected $password;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDemoCredentials();
        $this->odoo = new Odoo(new Odoo\Config($this->database, $this->host, $this->username, $this->password));
        $this->odoo->connect();

    }

    /**
     * Set odoo.com test credentials
     */
    protected function setDemoCredentials()
    {
        $this->host = getenv('ODOO_HOST');
        $this->username = getenv('ODOO_USERNAME');
        $this->password = getenv('ODOO_PASSWORD');
        $this->database = getenv('ODOO_DATABASE');
    }

}