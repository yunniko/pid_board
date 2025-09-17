<?php

namespace App\Filters;

use App\Model\PIDApi\interfaces\PidApiResponseItemInterface;

class FilterByExcludeRouteNumber extends Filter
{
    public final function filter(PidApiResponseItemInterface $object): bool
    {
        $route = $object->route_number ?? '';

        return !in_array($route, $this->filterValues);
    }
}