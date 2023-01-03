<?php

namespace App\Model\PIDApi\response;

class PidApiDeparturesResponse extends PidApiResponse
{
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
}