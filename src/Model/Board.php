<?php

namespace App\Model;

use Symfony\Component\HttpClient\HttpClient;

class Board
{
    public $settings = [
        'minutesBefore' => 30,
        'minutesAfter' => 180,
        'limit' => 50
    ];

    private $api;

    public function __construct()
    {
        $this->api = new PidApi(HttpClient::create([]));
    }

    public function getData()
    {
        return [
            'buses1' => $this->getSidlisteDepartures(),
            'buses2' => $this->getKrystalovaDepartures(),
            'trains' => $this->getTrainDepartures(),
            'buses4' => $this->getKralovaDepartures(),
            'buses3' => $this->getCukrovarDepartures(),
        ];
    }

    private function _getRawDepartures($name, $data = [])
    {
        $settings = $this->settings + $data;
        $response = $this->api->getDepartures($name, $settings);
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
            'train_number' => 'trip.short_name'
        ], $departures);

        return $this->processTime($departures, [
            'arrival_predicted',
            'arrival_scheduled',
            'departure_predicted',
            'departure_scheduled'
        ]);
    }

    private function getSidlisteDepartures()
    {
        $name = 'Sídliště Čakovice';
        $departures = $this->_getRawDepartures($name);
        $departures = $this->filterByTime($departures, 'departure_predicted_ts', 1, 5, 60);

        //TODO: check arrivals for delay
        return [
            'stop' => $name,
            'departures' => $departures
        ];
    }

    private function getKrystalovaDepartures()
    {
        $name = 'Krystalová';

        $departures = $this->_getRawDepartures($name, [
            'ids' => ['U114Z3']
        ]);
        $departures = array_filter($departures, function ($item) {
            $route = $item['route_number'] ?? '';

            return ($route !== '136');
        });
        $departures = $this->filterByTime($departures, 'departure_predicted_ts', 1, 5, 60);

        return [
            'stop' => $name,
            'departures' => $departures
        ];
    }

    private function getKralovaDepartures()
    {
        $name = 'Králova';

        $departures = $this->_getRawDepartures($name, [
            'ids' => ['U293Z2P']
        ]);
        $departures = $this->filterByTime($departures, 'departure_predicted_ts', 1, 5, 60);

        return [
            'stop' => $name,
            'departures' => $departures
        ];
    }

    private function getCukrovarDepartures()
    {
        $name = 'Cukrovar Čakovice';

        $departures = $this->_getRawDepartures($name, [
            'ids' => ['U63Z2P']
        ]);
        $departures = $this->filterByTime($departures, 'departure_predicted_ts', 1, 5, 60);

        return [
            'stop' => $name,
            'departures' => $departures
        ];
    }

    private function getTrainDepartures()
    {
        $name = 'Praha-Čakovice';
        $departures = $this->_getRawDepartures($name);
        $departures = array_filter($departures, function ($item) {
            $destination = $item['destination'] ?? '';

            return (mb_strpos($destination, 'Praha') !== false);
        });
        $departures = $this->filterByTime($departures, 'departure_predicted_ts');

        return [
            'stop' => $name,
            'departures' => $departures
        ];
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
        foreach ($array as $n => $arrayItem) {
            foreach ($columns as $column) {
                if (empty($array[$n][$column])) {
                    continue;
                }
                $dateTime = new \DateTime($array[$n][$column]);
                $array[$n][$column] = $dateTime->format('H:i');
                $array[$n][$column . '_ts'] = $dateTime->getTimestamp();
            }
        }

        return $array;
    }

    private function filterByTime(
        $array,
        $columnTimestamp,
        $pastCount = 1,
        $futureCount = 4,
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