<?php

namespace App\Model\PIDApi;

use App\Model\PIDApi\interfaces\PidApiRequestInterface;
use Exception;
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

    public function get(PidApiRequestInterface $data)
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

        return $data->makeResponse($response->toArray());
    }


}