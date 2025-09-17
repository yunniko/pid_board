<?php

namespace App\Filters;

use App\Model\PIDApi\interfaces\PidApiResponseItemInterface;

abstract class Filter implements FilterInterface
{
    /**
     * @var array
     */
    protected $filterValues = [];

    public function __construct(array $filterValues)
    {
        if (!empty($filterValues)) {
            $this->filterValues = $filterValues;
        }
    }

    public abstract function filter(PidApiResponseItemInterface $object): bool;
}