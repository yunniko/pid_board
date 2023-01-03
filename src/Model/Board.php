<?php

namespace App\Model;

use App\Model\PIDApi\request\PidApiDeparturesRequest;
use Symfony\Component\HttpClient\HttpClient;

class Board
{
    public $settings = [
        'minutesBefore' => 30,
        'minutesAfter' => 180,
        'limit' => 200
    ];

    private $api;

    public function __construct()
    {
        $this->api = new PidApi(HttpClient::create([]));
    }

    public function getData($settings)
    {
        $settings = $this->makeSettings($settings);
        $result = [];
        foreach ($settings as $name => $data) {
            $result[] = $this->getDepartures($data);
        }

        return $result;
    }

    public function getDepartures($name, $settings = [])
    {
        $callback = $settings['callback'] ?? null;
        $stops = $settings['stops'] ?? null;
        $data = new PidApiDeparturesRequest($this->settings);
        if (!empty($stops)) {
            $data->ids = $stops;
        }
        $departures = $this->_getRawDepartures($settings);
        if ($callback) {
            $departures = $callback($departures);
        } else {
            $departures = $this->filterByTime($departures, 'departure_predicted_ts');
        }

        return [
            'stop' => $name,
            'departures' => $departures
        ];
    }

    private function makeSettings($settings = [])
    {
        $result = [];
        foreach ($settings as $name => $filter) {
            $stops = null;
            if (strpos($name, '|') !== false) {
                [$name, $stops] = explode('|', $name);
                $stops = explode(',', $stops);
            }
            $result[$name] = [
                'stops' => $stops,
                'callback' => function ($departures) use ($filter) {
                    $departures = array_filter($departures, $filter);

                    return $this->filterByTime($departures, 'departure_predicted_ts');
                }
            ];
        }

        return $result;
    }

    private function _getRawDepartures(PidApiDeparturesRequest $settings)
    {
        $response = $this->api->get($settings);
        $departures = $response->getByKey('departures');
        $departures = $this->map([
            'arrival_predicted' => 'arrival_timestamp.predicted',
            'arrival_scheduled' => 'arrival_timestamp.scheduled',
            'departure_predicted' => 'departure_timestamp.predicted',
            'departure_scheduled' => 'departure_timestamp.scheduled',
            'delay' => 'delay.seconds',
            'departure_minutes_left' => 'departure_timestamp.minutes',
            'route_number' => 'route.short_name',
            'destination' => 'trip.headsign',
            'train_number' => 'trip.short_name',
            'stop_id' => 'stop.id',
            'last_stop' => 'last_stop.name'
        ], $departures);

        return $this->processTime($departures, [
            'arrival_predicted',
            'arrival_scheduled',
            'departure_predicted',
            'departure_scheduled'
        ]);
    }

    private function map($map, $array)
    {
        return array_map(function ($item) use ($map) {
            $result = [];
            foreach ($map as $newKey => $oldKey) {
                $oldKey = explode('.', $oldKey);
                $current = $item;
                foreach ($oldKey as $oldKeyItem) {
                    if (isset($current[$oldKeyItem])) {
                        $current = $current[$oldKeyItem];
                    } else {
                        $current = null;
                        break;
                    }
                }
                $result[$newKey] = $current;
            }

            return $result;
        }, $array);
    }

    private function processTime($array, $columns)
    {
        $now = time();
        foreach ($array as $n => $arrayItem) {
            foreach ($columns as $column) {
                if (empty($array[$n][$column])) {
                    continue;
                }
                $dateTime = new \DateTime($array[$n][$column]);
                $array[$n][$column] = $dateTime->format('H:i');
                $array[$n][$column . '_ts'] = $dateTime->getTimestamp();
                $array[$n][$column . '_diff'] = floor(($dateTime->getTimestamp() - $now) / 60);
            }
        }

        return $array;
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
            $itemTime = $item[$columnTimestamp] ?? 0;
            if ($itemTime < $now && $now - $itemTime < $maxTimerangeSeconds) {
                $past[] = $item;
            } else if ($itemTime - $now < $maxTimerangeSeconds) {
                $future[] = $item;
            }
        }

        return array_merge(array_slice($past, $pastCount * -1), array_slice($future, 0, $futureCount));
    }
}