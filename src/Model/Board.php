<?php
namespace App\Model;

use Symfony\Component\HttpClient\HttpClient;

class Board
{
    private $api;
    public $settings = [
        'minutesBefore' => 30,
        'minutesAfter' => 180,
        'limit' => 50
    ];
    public function __construct() {
        $this->api = new PidApi(HttpClient::create([]));
    }

    public function getData() {
        return [
            /*$this->getSidlisteDepartures(),
            $this->getKrystalovaDepartures(),*/
            'trains' => $this->getTrainDepartures()
        ];
    }
    private function _getRawDepartures($name){
        $settings = $this->settings;

        /*$time = '2023-01-03T07:00:00+01:00';
        $settings['timeFrom'] = $time; //TODO: remove!*/

        $response = $this->api->getDepartures($name, $settings);
        $departures = array_filter($response->getByKey('departures'), function($item) {
            $headsign = $item['trip']['headsign'] ?? '';
            return (mb_strpos($headsign, 'Praha') !== false);
        });
        $departures = $this->map([
            'arrival_predicted' => 'arrival_timestamp.predicted',
            'arrival_scheduled' => 'arrival_timestamp.scheduled',
            'departure_predicted' => 'departure_timestamp.predicted',
            'departure_scheduled' => 'departure_timestamp.scheduled',
            'delay' => 'delay.seconds',
            'departure_minutes_left' => 'departure_timestamp.minutes',
            'route_name' => 'route.short_name',
            'destination' => 'trip.headsign',
            'train_number' => 'trip.short_name'
        ], $departures);
        $departures = $this->convertToTimestring($departures, [
            'arrival_predicted',
            'arrival_scheduled',
            'departure_predicted',
            'departure_scheduled'
        ]);
        $departures = $this->filterByTime($departures, 'departure_predicted');
        return $departures;
    }

    private function getSidlisteDepartures() {
        $name = 'Sídliště Čakovice';
        //TODO: check arrivals for delay
        return $this->_getRawDepartures($name);
    }

    private function getKrystalovaDepartures() {
        $name = 'Krystalová';
        //TODO: filter 136
        return $this->_getRawDepartures($name);
    }

    private function getTrainDepartures() {
        $name = 'Praha-Čakovice';
        return $this->_getRawDepartures($name);
    }

    private function map($map, $array) {
        return array_map(function($item) use($map){
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

    private function convertToTimestring($array, $columns) {
        foreach($array as $n => $arrayItem) {
            foreach ($columns as $column) {
                if (empty($array[$n][$column])) continue;
                $dateTime = new \DateTime($array[$n][$column]);
                $array[$n][$column] = $dateTime->format('H:i');
            }
        }
        return $array;
    }

    private function filterByTime($array, $column, $pastCount = 1, $futureCount = 4) {
        $past = [];
        $future = [];
        $now = date('H:i');
        foreach ($array as $item) {
            if ($item[$column] < $now) {
                $past[] = $item;
            } else {
                $future[] = $item;
            }
        }
        return array_merge(array_slice($past, $pastCount * -1), array_slice($future, 0, $futureCount));
    }
}