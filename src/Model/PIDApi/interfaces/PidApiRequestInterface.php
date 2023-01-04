<?php

namespace App\Model\PIDApi\interfaces;

interface PidApiRequestInterface
{
    public function toArray();

    public function makeResponse(array $data): PidApiResponseInterface;
}