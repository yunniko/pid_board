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
            $this->getTrainDepartures()
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
        return $this->map([
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
}