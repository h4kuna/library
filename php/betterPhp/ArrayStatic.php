<?php

namespace Utility;

class ArrayStatic {

    /**
     * without iterator
     * @param array $keysarray
     * @param array $valuesarray
     * @param type $value
     * @return type
     */
    static function arrayCombine(array $keysarray, array $valuesarray, $value = NULL) {
        $diff = count($keysarray) - count($valuesarray);

        if ($diff > 0) {
            $valuesarray = array_merge($valuesarray, array_fill(0, $diff, $value));
        }

        return array_combine($keysarray, $valuesarray);
    }

    static function concatWs($glue, $array, $keys /* , ... */) {
        $args = array_slice(func_get_args(), 2);
        $out = '';
        foreach ($args as $v) {
            if (isset($array[$v]) && $array[$v] !== NULL) {
                $out .= $array[$v] . $glue;
            }
        }
        return substr($out, 0, strlen($glue) * -1);
    }

    /**
     * udělá z pole array(0 => array('id' => 5, 'name' => 'foo'), 1 => array('id' => 6, 'name' => 'bar') ===> array(5 => 'foo', 6 => 'bar')
     * @param type $array
     * @param type $valueKey
     * @param type $keyKey
     * @return type
     */
    static function forSelectBox($array, $valueKey, $keyKey = NULL) {
        $out = array();
        foreach ($array as $k => $v) {
            $out[$keyKey === NULL ? $k : $v[$keyKey]] = $v[$valueKey];
        }
        return $out;
    }

}