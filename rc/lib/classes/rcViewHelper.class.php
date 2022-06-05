<?php

class rcViewHelper extends waAppViewHelper
{
    /**
     * @param $array
     * @param $key
     */
    public function unsetValue(&$array, $key)
    {
        if (isset($array[$key])) {
            unset($array[$key]);
        }
    }
}