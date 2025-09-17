<?php

namespace App\Filters;

use App\Model\PIDApi\interfaces\PidApiResponseItemInterface;

interface FilterInterface
{
    public function filter(PidApiResponseItemInterface $object): bool;
}