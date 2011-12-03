<?php

namespace Utility;

final class DateTime extends \DateTime
{
    static protected $timeUnit = array('hour', 'min', 'sec');

    const SQL_DATE = 'Y-m-d';
    const SQL_DATETIME = 'Y-m-d H:i:s';
    const CZECH_DATE = 'j.n.Y';

    public $outFormat = self::SQL_DATE;

    public function __toString()
    {
        return $this->format($this->outFormat);
    }

    public static function timeString($time, $flag='+')
    {
        $array = explode(':', $time);
        $out = NULL;
        foreach($array as $k => $val)
        {
            $out .= $flag . $val .' '. self::$timeUnit[$k] .' ';
        }
        return $out;
    }
}
