<?php

namespace App\Model\PIDApi\response;

use App\Model\PIDApi\responseItem\PidApiStopsResponseItem;

class PidApiStopsResponse extends PidApiResponse
{
    protected $rootKey = 'features';

    public function getItemClass(): string
    {
        return PidApiStopsResponseItem::class;
    }
}