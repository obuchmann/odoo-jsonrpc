<?php


namespace Obuchmann\OdooJsonRpc\JsonRpc;


use GuzzleHttp\Exception\GuzzleException;
use Obuchmann\OdooJsonRpc\Exceptions\OdooException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private \GuzzleHttp\Client $client;
    private ?ResponseInterface $lastResponse = null;

    private ?string $lastResponseContents = null;

    public function __construct(string $baseUri, private string $service = 'object', $sslVerify = true)
    {

        $this->client = new \GuzzleHttp\Client([
            'headers' => [
                'Content-Type' => 'application/json',
                'Connection' => 'close',
            ],
            'base_uri' => $baseUri,
            'verify' => $sslVerify,
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
                    'id' => mt_rand(0, 1000000000)
                ]
            ]);
        } catch (GuzzleException $e) {
            throw new OdooException(null, $e->getMessage(), $e->getCode(), $e);
        }
        $this->lastResponse = $response;
        $this->lastResponseContents = null;

        return match($response->getStatusCode()) {
            200 => $this->makeResponse($response), // TODO ->result kann auch nicht definiert sein. Normal wenn ->error gegeben ist.
            default => throw new OdooException($response)
        };
    }

    public function lastResponse(): ?ResponseInterface
    {
        return $this->lastResponse;
    }

    public function getLastResponseContents(): ?string
    {
        return $this->lastResponseContents;
    }


    private function makeResponse(ResponseInterface $response)
    {
        $body = $response->getBody();
        $contents = $body->getContents();
        $this->lastResponseContents = $contents;
        $body->close();

        if (empty($contents)) {
            throw new OdooException($response, "Received an empty response from Odoo server.", null);
        }

        $json = json_decode($contents);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new OdooException($response, "Failed to decode JSON response: " . json_last_error_msg(), null);
        }

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
        if(property_exists($json, 'result')){
            return $json->result;
        }
        if(property_exists($json, 'id')){
            return $json->id;
        }
        return null;
    }
}