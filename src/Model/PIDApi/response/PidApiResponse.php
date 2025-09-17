<?php

namespace App\Model\PIDApi\response;

use App\Filters\FilterInterface;
use App\Model\PIDApi\interfaces\PidApiResponseInterface;

abstract class PidApiResponse implements PidApiResponseInterface
{
    protected $rootKey;

    private $data;

    public function __construct(array $data)
    {
        if (!empty($this->rootKey)) {
            $this->setData($data[$this->rootKey] ?? []);
        } else {
            $this->setData($data);
        }

    }

    public abstract function getItemClass(): string;

    public function getData()
    {
        return $this->data;
    }

    public function setData(array $data)
    {
        $result = [];
        $itemClass = $this->getItemClass();
        foreach ($data as $item) {
            $result[] = new $itemClass($item);
        }
        $this->data = $result;
    }

    public function getFilteredData($filters): array
    {
        if (empty($filters)) {
            return $this->getData();
        }
        if (!is_array($filters)) {
            $filters = [$filters];
        }

        $data = $this->getData();
        foreach ($filters as $filter) {
            if (is_callable($filter)) {
                $data = array_filter($data, $filter);
            } else if ($filter instanceof FilterInterface) {
                $data = array_filter($data, function ($item) use ($filter) {
                    return ($filter->filter($item));
                });
            }
        }

        return $data;
    }
}