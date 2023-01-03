<?php

namespace App\Model\PIDApi\response;

use App\Model\PIDApi\responseItem\PidApiStopsResponseItem;

class PidApiStopsResponse extends PidApiResponse
{
    public function getItemClass(): string
    {
        return PidApiStopsResponseItem::class;
    }
}