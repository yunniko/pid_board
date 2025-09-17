<?php

namespace App\Model\PIDApi\interfaces;

use FilterInterface;

interface PidApiResponseInterface
{
    public function getData();

    public function getFilteredData($filters): array;
}