<?php

namespace App\Model;

use App\Model\PIDApi\request\PidApiRequest;
use Exception;
use Symfony\Component\VarDumper\Cloner\Data;
use Symfony\Contracts\HttpClient\HttpClientInterface;

//https://api.golemio.cz/v2/pid/docs/openapi/#
class PidApi
{
    private $client;

    private $version = '/v2';

    public function __construct(HttpClientInterface $client)
    {
        $apiKey = $_ENV['PID_KEY'] ?? '';
        $baseUrl = $_ENV['PID_URL'] ?? 'https://api.golemio.cz';
        if (empty($apiKey) || empty($baseUrl)) {
            throw new Exception('Api is not configured');
        }
        $this->client = $client->withOptions([
            'base_uri' => $baseUrl,
            'headers' => ['X-Access-Token' => $apiKey]
        ]);
    }

    public function makeUrl($method)
    {
        //TODO: check for version in method, check all slashes;
        return $this->version . $method;
    }

    public function get(PidApiRequest $data)
    {
        $url = $this->makeUrl($data::getRoute());
        $response = $this->client->request(
            'GET',
            $url,
            [
                'query' => $data->toArray()
            ]
        );
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('CODE ' . $response->getStatusCode() . ' (' . var_export($response->getInfo(),
                    true) . ')');
        }
        $responseClass = $data->getResponseClass();

        return new $responseClass($response->toArray());
    }


}