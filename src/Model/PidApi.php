<?php
namespace App\Model;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PidApi
{
    private $client;
    private $version = '/v2';
    public function __construct (HttpClientInterface $client) {
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

    public function makeUrl($method) {
        //TODO: check for version in method, check all slashes;
        return $this->version . $method;
    }

    public function get($url, $data) {
        $url = $this->makeUrl($url);
        $response = $this->client->request(
            'GET',
            $url,
            [
                'query' => $data
            ]
        );
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('CODE ' . $response->getStatusCode() . ' (' . var_export($response->getInfo(), true) . ')');
        }
        return new PidApiResponse($response->toArray());
    }

    public function getStops($names = [], $data = []) {
        if (!empty($names) && !is_array($names)) $names = [$names];
        return $this->get('/gtfs/stops', $data + ['names' => $names]);
    }

    public function getDepartures($names = [], $data = []) {
        if (!empty($names) && !is_array($names)) $names = [$names];
        return $this->get('/pid/departureboards', $data + ['names' => $names]);
    }
}