<?php

namespace App\Model\PIDApi\interfaces;

interface PidApiRequestInterface
{
    public static function getRoute();

    public function toArray();

    public function makeResponse(array $data): PidApiResponseInterface;
}