<?php

namespace App\Model\PIDApi\response;

use App\Model\PIDApi\responseItem\PidApiDeparturesResponseItem;

class PidApiDeparturesResponse extends PidApiResponse
{
    protected $rootKey = 'departures';

    public function getItemClass(): string
    {
        return PidApiDeparturesResponseItem::class;
    }
}