<?php

namespace App\Model\PIDApi\request;

use App\Model\PIDApi\response\PidApiDeparturesResponse;

class PidApiDeparturesRequest extends PidApiRequest
{
    public $names;

    public $ids;

    public $aswIds;

    public $cisIds;

    public $minutesBefore = 30;

    public $minutesAfter = 90;

    public $timeFrom;

    public $includeMetroTrains;

    public $preferredTimezone;

    public $mode;

    public $order;

    public $filter;

    public $skip;

    public $total;

    public static function getRoute()
    {
        return '/pid/departureboards';
    }

    public static function getResponseClass() {
        return PidApiDeparturesResponse::class;
    }
}