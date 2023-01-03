<?php

namespace App\Model\PIDApi\response;

abstract class PidApiResponse
{
    private $data;

    public function __construct(array $data)
    {
        $this->setData($data['departures'] ?? []);
    }

    public function setData(array $data) {
        $result = [];
        $itemClass = $this->getItemClass();
        foreach ($data as $item) {
            $result[] = new $itemClass($item);
        }
        $this->data = $result;
    }

    public abstract function getItemClass(): string;

    public function getData() {
        return $this->data;
    }

    public function getFilteredData($filterCallback): array
    {
        if ($filterCallback) {
            return array_map($filterCallback, $this->getData());
        } else {
            return $this->getData();
        }
        
    }
}