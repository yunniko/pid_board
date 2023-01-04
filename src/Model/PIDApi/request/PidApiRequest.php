<?php

namespace App\Model\PIDApi\request;

use App\Model\PIDApi\interfaces\PidApiRequestInterface;
use App\Model\PIDApi\interfaces\PidApiResponseInterface;

abstract class PidApiRequest implements PidApiRequestInterface
{
    public $limit;

    public $offset;

    public function __construct(array $settings = null)
    {
        if ($settings) {
            $this->set($settings);
        }
    }

    public static abstract function getRoute();

    public static abstract function getResponseClass();

    public function makeResponse(array $data): PidApiResponseInterface
    {
        $class = static::getResponseClass();

        return new $class($data);
    }

    public function toArray(bool $includeUndefined = false): array
    {
        $parameters = get_object_vars($this);
        foreach ($parameters as $var => $default) {
            $parameters[$var] = $this->$var;
        }
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
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}