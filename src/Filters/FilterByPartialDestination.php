<?php

namespace App\Filters;

use App\Model\PIDApi\interfaces\PidApiResponseItemInterface;

class FilterByPartialDestination extends Filter
{

    public final function filter(PidApiResponseItemInterface $object): bool
    {
        $destination = $item->destination ?? '';

        foreach ($this->filterValues as $part) {
            if (mb_strpos($destination, $part) !== false) {
                return true;
            }
        }

        return false;
    }
}