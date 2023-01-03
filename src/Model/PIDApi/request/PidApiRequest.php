<?php

namespace App\Model\PIDApi\request;

abstract class PidApiRequest
{
    public $limit;

    public $offset;

    public function __construct(array $settings)
    {
        $this->set($settings);
    }

    public static abstract function getRoute();

    public function toArray(bool $includeUndefined = false): array
    {
        $parameters = get_object_vars($this);
        if (!$includeUndefined) {
            $parameters = array_filter($parameters, function ($item) {
                return $item !== null;
            });
        }

        return $parameters;
    }

    public function set(array $settings = [])
    {
        foreach ($settings as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $value;
            }
        }
    }
}