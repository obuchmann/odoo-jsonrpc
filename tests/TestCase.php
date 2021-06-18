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

        $client = new \GuzzleHttp\Client();
        $startRequest = $client->request('POST', 'https://demo.odoo.com/start', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => "{}"
        ]);

        $info = json_decode($startRequest->getBody())->result;

        $this->host = getenv('ODOO_HOST') ?: $info->host;
        $this->username = getenv('ODOO_USERNAME') ?: $info->user;
        $this->password = getenv('ODOO_PASSWORD') ?: $info->password;
        $this->database = getenv('ODOO_DATABASE') ?: $info->database;
    }

}