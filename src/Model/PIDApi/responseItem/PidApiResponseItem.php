<?php

namespace App\Model\PIDApi\responseItem;

use App\Model\PIDApi\interfaces\PidApiResponseItemInterface;

abstract class PidApiResponseItem implements PidApiResponseItemInterface
{
    private $_rawData;

    public function __construct(array $data)
    {
        $this->_rawData = $data;
    }

    public function getRawData()
    {
        return $this->_rawData;
    }

    public function __isset($name)
    {
        if (array_key_exists($name, $this->getMap())) {
            return true;
        }
        if (array_key_exists($name, $this->getRawData())) {
            return true;
        }
        $timeKeys = $this->getTimeColumns();
        foreach ($timeKeys as $timeKey) {
            $timeKeysExtended = [
                $timeKey . '_ts',
                $timeKey . '_short',
                $timeKey . '_obj',
                $timeKey . '_diff'
            ];
            if (in_array($name, $timeKeysExtended)) {
                return true;
            }
        }
        $value = $this->__get($name);

        return ($value !== null);
    }

    public function __get($attr)
    {
        $value = $this->getDataItemByKey($attr);
        $timeKeys = $this->getTimeColumns();
        if (!in_array($attr, $timeKeys)) {
            foreach ($timeKeys as $timeKey) {
                $tsKey = $timeKey . '_ts';
                $shortKey = $timeKey . '_short';
                $objKey = $timeKey . '_obj';
                $diffKey = $timeKey . '_diff';

                switch ($attr) {
                    case $tsKey:
                        $value = new \DateTime($this->getDataItemByKey($timeKey));
                        $value = $value->getTimestamp();
                        break;
                    case $objKey:
                        $value = new \DateTime($this->getDataItemByKey($timeKey));
                        break;
                    case $shortKey:
                        $value = new \DateTime($this->getDataItemByKey($timeKey));
                        $value = $value->format('H:i');
                        break;
                    case $diffKey:
                        $value = new \DateTime($this->getDataItemByKey($timeKey));
                        $value = floor(($value->getTimestamp() - time()) / 60);
                        break;
                }
            }
        }

        return $value;
    }

    public abstract function getTimeColumns(): array;

    public abstract function getMap(): array;

    private function getDataItemByKey(string $key)
    {
        $map = $this->getMap();
        if (isset($map[$key])) {
            $key = $map[$key];
        }
        $key = explode('.', $key);
        $current = $this->_rawData;
        foreach ($key as $subkey) {
            if (isset($current[$subkey])) {
                $current = $current[$subkey];
            } else {
                $current = null;
                break;
            }
        }

        return $current;
    }
}