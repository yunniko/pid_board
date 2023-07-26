<?php

namespace App\Model;

use App\Model\PIDApi\PidApi;
use App\Model\PIDApi\request\PidApiDeparturesRequest;
use Symfony\Component\HttpClient\HttpClient;

class Board
{
    public $defaultSettings = [
        'minutesBefore' => 30,
        'minutesAfter' => 180,
        'total' => 50,
        'limit' => 50
    ];

    private $api;

    public function __construct()
    {
        $this->api = new PidApi(HttpClient::create([]));
    }

    public function getData($settings)
    {
        $defaults = $this->defaultSettings;
        $result = [];
        foreach ($settings as $data) {
            $query = $data['query'] ?? [];
            $filterCallback = $data['filterCallback'] ?? null;
            $name = $data['name'] ?? '';
            $settingsObject = new PidApiDeparturesRequest(array_merge($defaults, $query));
            $response = $this->api->get($settingsObject);
            $departures = $response->getFilteredData($filterCallback);
            $departures = $this->filterByTime($departures, 'departure_predicted_ts', $data['past_count'] ?? 1, $data['future_count'] ?? 5, $data['max_timerange_minutes'] ?? 90);
            $result[] = [
                'stop' => $name,
                'departures' => $departures
            ];
        }

        return $result;
    }

    private function filterByTime(
        $array,
        $columnTimestamp,
        $pastCount = 1,
        $futureCount = 5,
        $maxTimerangeMinutes = 90
    ) {
        $past = [];
        $future = [];
        $now = time();
        $maxTimerangeSeconds = $maxTimerangeMinutes * 60;
        foreach ($array as $item) {
            $itemTime = $item->$columnTimestamp ?? 0;
            if ($itemTime < $now && $now - $itemTime < $maxTimerangeSeconds) {
                $past[] = $item;
            } else if ($itemTime - $now < $maxTimerangeSeconds) {
                $future[] = $item;
            }
        }

        return array_merge(array_slice($past, $pastCount * -1), array_slice($future, 0, $futureCount));
    }
}
