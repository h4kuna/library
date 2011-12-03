<?php

namespace Translation;
use Utility\NonObject;

/**
 * cislo na konci metod urcuje kolik je skolonovacich padu, je povinne
 * v administraci podle toho se odvozuje kolik ma nabidnout možností pro sklonění
 */
class Declension extends NonObject
{
    /**
     * urci slovo pro ru/uk/be/bs/hr/sr/sh sklonovani
     * @param $count
     * @return int
     */
    public static function russianDeclension4($count)
    {
        $copy   =(int)$count;
        if( $copy == $count)
        {
            $last   =substr($count, -1);
            switch($last)
            {
                case 1:
                    return 1;

                case 2:
                case 3:
                case 4:
                    return 2;

                default:
                    return 3;
            }
        }
        return null;
    }

    /**
     * urci slovo pro bh/fil/guw/hi
     * @param $count
     * @return int
     */
    public static function oneDeclension2($count)
    {
        return ($count == 1)? 1: null;
    }

    /**
     * urci slovo pro fr
     * @param $count
     * @return int
     */
    public static function romanDeclension2($count)
    {
        return ($count >= 0 && $count < 2)? 1: null;
    }

    /**
     * urci slovo pro af/sq/am/eu/bn/bg/ca/da/nl/eo/et/en/fo/fi/fur/gl/de/el/gu/ha/he/pt_PT/is/it/iw/ku
     * @param $count
     * @return int
     */
    public static function defaultDeclension2($count)
    {
        return ($count == 1 || $count == 0)? 0: 1;
    }

    /**
     * urci slovo pro smn/ga
     * @param $count
     * @return int
     */
    public static function samiDeclension3($count)
    {
        if($count == 1 || $count == 2)
            return $count;
        return null;
    }

    /**
     * nektere staty nesklonuji az/my/zh/dz/ka/hu/id/ja/jv/kn/km/ko
     * @param $count
     * @return null
     */
    public static function basicDeclension0($count)
    {
        return null;
    }


    /**
     * urci slovo pro ceske/slovenske sklonovani
     * @param float $count
     * @return int
     */
    public static function czechDeclension4($count)
    {
        $copy   =(int)$count;
        if($count == $copy)
        {
            switch($copy)
            {
                case 1:
                    return 0;

                case 2:
                case 3:
                case 4:
                    return 1;

                default:
                    return 2;
            }
        }
        return 3;
    }

    /**
     * urci slovo pro ar
     * @param $count
     * @return int
     */
    public static function arabicDeclension5($count)
    {
        $copy   =(int)$count;
        if( $copy == $count)
        {
            $last   =substr($count, -2);
            switch($last)
            {
                case 0:
                    return 0;

                case 1:
                    return 1;

                case 2:
                    return 2;

                default:
                    if($count >= 3 && $count <= 10)
                    {
                        return 3;
                    }
                    else if($count >= 11 && $count <= 99)
                    {
                        return 4;
                    }
                    return null;
            }
        }

        return null;
    }
}
