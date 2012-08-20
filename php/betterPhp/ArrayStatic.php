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

}