<?php

namespace App\Filters;

use App\Model\PIDApi\interfaces\PidApiResponseItemInterface;

class FilterByDestinationPrefix extends Filter
{

    public final function filter(PidApiResponseItemInterface $object): bool
    {
        $destination = $item->destination ?? '';

        foreach ($this->filterValues as $prefix) {
            if (mb_strpos($destination, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}