<?php


namespace Obuchmann\OdooJsonRpc\JsonRpc;


use GuzzleHttp\Exception\GuzzleException;
use Obuchmann\OdooJsonRpc\Exceptions\OdooException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private \GuzzleHttp\Client $client;
    private ?ResponseInterface $lastResponse = null;

    public function __construct(string $baseUri, private string $service = 'object')
    {

        $this->client = new \GuzzleHttp\Client([
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'base_uri' => $baseUri,
            'verify' => config('odoo.ssl_verify',true),
        ]);

    }

    public function __call(string $method, array $arguments)
    {
        try {
            $response = $this->client->request('POST', 'jsonrpc', [
                'json' => [
                    'jsonrpc' => '2.0',
                    'method' => 'call',
                    'params' => [
                        'service' => $this->service,
                        'method' => $method,
                        'args' => $arguments
                    ],
                    'id' => rand(0, 1000000000)
                ]
            ]);
        } catch (GuzzleException $e) {
            throw new OdooException(null, $e->getMessage(), $e->getCode(), $e);
        }
        $this->lastResponse = $response;

        return match($response->getStatusCode()) {
            200 => $this->makeResponse($response), // TODO ->result kann auch nicht definiert sein. Normal wenn ->error gegeben ist.
            default => throw new OdooException($response)
        };
    }

    public function lastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    private function makeResponse(ResponseInterface $response)
    {
        $json = json_decode($response->getBody());
        if(isset($json->error)){
            $message = "Odoo Exception";
            if(isset($json->error->message)){
                $message = $json->error->message;
            }
            if(isset($json->error->data) && isset($json->error->data->message)){
                $message .= ': '.$json->error->data->message;
            }
            throw new OdooException($response, $message, $json->error->code ?? null);
        }
        return $json->result ?? $json->id;
    }
}