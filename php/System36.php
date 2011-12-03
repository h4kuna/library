<?php
namespace Utility;
use Nette\Image, Nette\Utils\Html, Models\Helpers;

/**
 *
 * @author Milan Matějček
 */
class System36 extends \Nette\Object{

    const TO_ASCII = 55;

    private static $img;

    /**
     * @param int $num
     * @return string
     */
    private static function fromDec($num)
    {
        $out = '';
        $mod = 0;
        do{
            $mod = $num % 36;
            $num = \intval($num / 36);
            $out .= ($mod > 9)? chr(self::TO_ASCII + $mod): (string)$mod;
        }while($num);

        return self::fillCode(\strrev($out));
    }

    /**
     *
     * @param string $string
     */
    public static function toDec($string)
    {
        $string = \strrev(self::checkCode(($string)));
        $len = \strlen($string);

        $num = $out = 0;
        for($i=0; $i<$len; $i++)
        {
            $num = !\is_numeric($string{$i}) ?
                \ord($string{$i}) - self::TO_ASCII :
                (int) $string{$i} ;
            $out += $num * \pow(36, $i);
        }
        return $out;
    }


    /**
     * ZZZZZZ -> 2,176,782,335
     * @param int $code
     * @return Html
     */
    public static function getImageCode($code)
    {
        $code = self::fillCode(self::checkCode($code));
        $name = $code .'.png';
        $file = Helpers::getWebTemp() . $name;

        if(!\file_exists($file))
        {
            $img = Image::fromBlank(93, 28, Image::rgb(255, 255, 255));
            $img->ttftext(30, 0, 1, 28, Image::rgb(0, 0, 0),
                    \APP_DIR . '/../tools/free3of9.ttf', $code);
            $img->save($file);
        }

        $pic = self::getImgEl();
        $pic->src = Helpers::getWebUrl() . $name;
        $pic->alt = $name;
        return $pic;
    }

    public static function getImageId($num)
    {
        return self::getImageCode(self::fromDec($num));
    }

    /**
     * @return Html
     */
    private static function getImgEl()
    {
        if(self::$img === NULL)
        {
            self::$img = Html::el('img');
        }
        return clone self::$img;
    }

    private static function checkCode($code)
    {
        $code = \strtoupper($code);
        if(!\preg_match('~^[0-9A-Z]{1,6}$~', $code))
            throw new System36Exception ('The code is not valid, must be alphanumeric string.');
        return $code;
    }

    private static function fillCode($code)
    {
        return sprintf('%06s', $code);
    }
}


class System36Exception extends \RuntimeException
{
}
