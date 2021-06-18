<?php


namespace Obuchmann\OdooJsonRpc\Odoo\Endpoint;


use Obuchmann\OdooJsonRpc\Exceptions\AuthenticationException;
use Obuchmann\OdooJsonRpc\Odoo\Models\Version;

class CommonEndpoint extends Endpoint
{

    protected string $service = 'common';

    public function authenticate(): int
    {
        $client = $this->getClient(true);
        $uid = $client
            ->authenticate(
                $this->getConfig()->getDatabase(),
                $this->getConfig()->getUsername(),
                $this->getConfig()->getPassword(),
                ['empty' => 'false']
            );
        if ($uid > 0) {
            return $uid;
        }

        throw new AuthenticationException($client->lastResponse(), "Authentication failed!");
    }


    public function version(): Version
    {
        return Version::hydrate(
            $this->getClient()
                ->version()
        );
    }
}