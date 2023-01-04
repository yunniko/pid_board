<?php

namespace App\Model\PIDApi\interfaces;

interface PidApiResponseInterface
{
    public function getData();

    public function getFilteredData($filterCallback): array;
}