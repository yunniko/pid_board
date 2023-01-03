<?php

namespace App\Model\PIDApi\responseItem;

class PidApiStopsResponseItem extends PidApiResponseItem
{
    public function getTimeColumns(): array
    {
        return [];
    }

    public function getMap(): array
    {
        return [
            'id' => 'features.properties.stop_id',
            'name' => 'features.properties.stop_name',
            'platform' => 'features.properties.platform_code',
            'zone' => 'features.properties.zone_id'
        ];
    }
}