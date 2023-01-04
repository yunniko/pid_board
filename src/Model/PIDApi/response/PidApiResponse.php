<?php

namespace App\Model\PIDApi\response;

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

    public function getFilteredData($filterCallback): array
    {
        if ($filterCallback) {
            return array_filter($this->getData(), $filterCallback);
        } else {
            return $this->getData();
        }

    }
}