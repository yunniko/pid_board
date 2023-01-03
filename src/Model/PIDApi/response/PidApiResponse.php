<?php

namespace App\Model\PIDApi\response;

abstract class PidApiResponse
{
    private $_rawData;

    public function __construct(array $data)
    {
        $map = $this->getMap();
        if ($map) {
            $data = $this->map($map, $data);
        }
        $timeColumns = $this->getTimeColumns();
        if ($timeColumns) {
            $data = $this->processTime($data, $timeColumns);
        }
        $this->set($data);
        $this->_rawData = $data;
    }

    public function getRawData()
    {
        return $this->_rawData;
    }

    public function getTimeColumns()
    {
        return [
            'arrival_predicted',
            'arrival_scheduled',
            'departure_predicted',
            'departure_scheduled'
        ];
    }

    public function getMap()
    {
        return [
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
        ];
    }

    public function set(array $settings = [])
    {
        foreach ($settings as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }

    private function map(array $map, array $array): array
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

    private function processTime(array $array, array $columns)
    {
        foreach ($array as $n => $arrayItem) {
            foreach ($columns as $column) {
                if (empty($array[$n][$column])) {
                    continue;
                }
                $dateTime = new \DateTime($array[$n][$column]);
                $array[$n][$column] = $dateTime;
            }
        }

        return $array;
    }
}