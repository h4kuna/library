<?php


namespace Utility;
/**
 *
 * @author Milan Matějček
 * @version 1.0
 */
class ArrayStep extends \ArrayIterator
{
    const POINTER   =0;

    /**
     * nastavi zda se ma prochazet polom dokola
     * @var bool
     */
    protected $loop;

    protected $pointer  =self::POINTER;

    //append
    //asort
    //__construct
    //getArrayCopy
    //getFlags
    //ksort
    //natcasesort
    //natsort
    //offsetExists
    //offsetGet
    //offsetSet
    //offsetUnset
    //serialize, PHP > 5.3.0
    //setFlags
    //uasort
    //uksort
    //unserialize, PHP > 5.3.0
    //valid


    public function __construct($array=null, $loop=true)
    {
        parent::__construct($array);
        $this->setLoop($loop);
    }

//----------uprava standartnich metod-------------------------------------------

    /**
     * posune vnitrni ukazatel vzestpne na dalsi, dojde-li na konec zacne od zacatku
     * @return void
     */
    public function next()
    {
        parent::next();
        $this->pointer++;

        if($this->valid() === false && $this->loop)
            $this->rewind();
    }

    /**
     * pretoci pole na zacatek
     * @return void
     */
    public function rewind()
    {
        $this->pointer  =self::POINTER;
        parent::rewind();
    }

    /**
     * opak pro $this->next();
     * @return mixed
     */
    public function prev()
    {
        $this->pointer--;
        if($this->pointer < 0)
            $this->pointer  =($this->loop)? $this->last(): self::POINTER;

        $this->seek( $this->pointer );
    }

    /**
     * nastavi na hodnotu podle vnitrniho ukazatele
     */
    public function seek($pointer)
    {
        $pointer    =(int)$pointer;
        parent::seek($pointer);
        $this->pointer  =$pointer;
    }


//-------------------nove metody------------------------------------------------

    /**
     * nastavi volbu prochazeni polem
     * @param $flag
     * @return void
     */
    public function setLoop($flag)
    {
        $this->loop =(bool)$flag;
    }

    /**
     * vrati nastaveni procha
     * @return bool
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * change actual flags, 0, 1
     * @return int
     */
    public function flags()
    {
        $flag   =abs($this->getFlags()-1);
        $this->setFlags($flag);
        return $flag;
    }

    /**
     * podle bool hodnoty posune vnitrni ukazatel dopredu 'true' nebo dozadu
     * @param bool $move
     * @return void
     */
    public function move($next=true)
    {
        if($next == true)
            $this->next();
        else
            $this->prev();
    }

    /**
     * vrati aktualni hodnotu a posune vnitrni ukazatel na dalsi polozku
     * @param bool $next
     * @return mixed
     */
    public function item($next=true)
    {
        $current=$this->current();
        $this->move($next);

        return $current;
    }

    /**
     * vrati aktualni klic a posune vnitrni ukazatel na dalsi polozku
     * @param bool $next
     * @return mixed
     */
    public function itemKey($next=true)
    {
        $current=$this->key();
        $this->move($next);

        return $current;
    }

    /**
     * Zjisti zda je to posledni hodnota
     * @return bool
     */
    public function isLast()
    {
        return $this->pointer === $this->last();
    }

    /**
     * zjisti zda je to prvni hodnota
     * @return bool
     */
    public function isFirst()
    {
        return $this->pointer === self::POINTER;
    }

    /**
     * Zjisti zda se jedna o sudou hodnotu
     * @return bool
     */
    public function isEven()
    {
        return $this->pointer % 2 === 1;
    }

    /**
     * Zjisti zda se jedna o lichou hodnotu
     * @return bool
     */
    public function isOdd()
    {
        return $this->pointer % 2 === 0;
    }

    /**
     * nastavi ukazatel na konec pole
     * @return mixed
     */
    public function end()
    {
        $last   =$this->last();
        $this->seek($last);
        $this->pointer  =$last;
    }


//---------------prace s pointrem-----------------------------------------------

    /**
     * vrati posledni hodnotu ukazatele (pointer)
     * @return int -pointer
     */
    protected function last()
    {
        $count  =parent::count();
        return $count > 0? --$count: 0;
    }

    /**
     * vrati vnitrni ukazatel
     * @return int
     */
    public function getPointer()
    {
        return $this->pointer;
    }

    /**
     * nastavi vnitrni ukazatel
     * @param int unsigned $pointer
     * @return int
     */
    public function setPointer($pointer, $interval=true)
    {
        $pointer    =(int)$pointer;
        if($interval)
            $pointer    =(int)Math::interval($pointer, self::POINTER, $this->count() - 1);

        $this->seek( $pointer );//vrati OutOfBoundsException pokud se prekroci rozsah

        $this->pointer  =$pointer;
        return $pointer;
    }

    /**
     * vrati hodnotu na dannem pointeru
     * @param int $pointer
     * @return mixed
     */
    public function getValueByPointer($pointer)
    {
        $point  =$this->pointer;
        $this->setPointer($pointer);
        $val    =$this->current();
        $this->seek($point);
        return $val;
    }

    /**
     * vrati klic na dannem pointru
     * @param int $pointer
     * @return mixed
     */
    public function getKeyByPointer($pointer)
    {
        $point  =$this->pointer;
        $this->setPointer($pointer);
        $val    =$this->key();
        $this->seek($point);
        return $val;
    }

//------------------------------------------------------------------------------
    static public function sortForUl(array $array, $column=4)
    {
        $column     =(int)$column;
        $row        =ceil( count($array) / $column );
        $newArray   =array();

        $i=0;
        $j=0;
        foreach($array as $value)
        {
            $k  =$i % $row;
            if($k == 0 && $i != 0)
            {
                $j++;
            }

            $newArray[$j + ($k * $column)]  =$value;

            $i++;
        }
        ksort($newArray, SORT_NUMERIC);
        reset($newArray);

        return $newArray;
    }

    static public function sortForUlAssoc(array $array, $column=4)
    {
        $cloneArray =$array;
        $newArray   =array();
        $array  =self::sortForUl($array, $column);

        foreach($array as $value)
        {
            $key    =array_search($value, $cloneArray);
            unset($cloneArray[$key]);
            $newArray[$key] =$value;
        }

        return $newArray;
    }
}
