<?php

namespace App\Model\PIDApi\request;

class PidApiStopsRequest extends PidApiRequest
{
    public $names;

    public $ids;

    public $aswIds;

    public $cisIds;

    public static function getRoute()
    {
        return '/gtfs/stops';
    }

    public static function getResponseClass()
    {
        return 'App\Model\PIDApi\response\PidApiStopsResponse';
    }
}