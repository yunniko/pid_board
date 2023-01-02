<?php
namespace App\Model;

class PidApiResponse
{
    private $data;
    public function __construct(array $data) {
        $this->data = $data;
    }
    private function _getByKey($array, $keys) {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                $array = $array[$key];
            } else {
                $array = null;
                break;
            }
        }
        return $array;
    }

    public function getByKey($key) {
        if (empty($key)) return null;
        $keys = explode('.', $key);
        return $this->_getByKey($this->data, $keys);
    }
}