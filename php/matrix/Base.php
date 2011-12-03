<?php

/**
 * spravuje matici a pohyb v ni
 * osa X = horizontalne
 * osa Y = vertikalne
 *
 * znaminka pri posunech horizontalne, vertikalne nebo po diagonalach, stejne pri praci s obrazky
 * v matici se vytvori vlastni souradnice zacinaji od 0
 *
 * 0 1 2 3 4 5 6 7 8 9 10 11 12 ...
 * 1
 * 2         up
 * 3          ^
 * 4        - |
 * 5 left <---|---> right
 * 6          | +
 * 7         down
 * 8
 * ...
 *
 */
class MatrixBase extends ArrayStep
{
    /**
     * hodnota osy x
     * @var int
     */
    private $x =ArrayStep::POINTER;

    /**
     * sestavi matic, co radek to objekt + vertikalni seznam radku je taky objekt
     * @param string|array $matrix
     */
    public function __construct(array $matrix=array(array()), $matrixLoop=true, $rowLoop=true)
    {
        parent::setLoop($matrixLoop);

        $row    =false;
        $count  =false;
        foreach($matrix as $row => $pole)
        {
            if($count === false)
                $count  =count($pole);

            $this->offsetSet($row, new ArrayStep($pole, $rowLoop));

            if($this->offsetGet($row)->count() != $count)
            {
                throw new InvalidStateException('This matrix is wrong, because hasn\'t same column  "'. $row .'".');
            }
        }

        if($row != false)
            $this->seek(0);
    }

    /**
     * Osa X 0-n
     * @return int
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * osa Y 0-n
     * @see ArrayStep#getPointer()
     * @return int
     */
    public function getY()
    {
        return $this->getPointer();
    }

    /**
     * hodnota osy X 0-n
     * @param int $int -pointer 0-n
     * @return void
     */
    public function setX($int)
    {
        $this->current()->setPointer($int);
        $this->x   =$int;
    }

    /**
     * hodnota osy Y 0-n
     * @param int $int -pointer 0-n
     * @see ArrayStep#setPointer()
     * @return void
     */
    public function setY($int)
    {
        $this->setPointer($int);
    }

    /**
     * vrati radek matice
     * @return ArrayStep
     */
    public function current()
    {//je tu schvalne ohledne php-Doc
        return parent::current();
    }

    /**
     * vrati radek matice
     * @return ArrayStep
     */
    public function getValueByPointer($pointer)
    {//je tu schvalne ohledne php-Doc
        return parent::getValueByPointer($pointer);
    }

    /**
     * vrati radek matice
     * @return ArrayStep
     */
    public function getKeyByPointer($pointer)
    {//je tu schvalne ohledne php-Doc
        return parent::getKeyByPointer($pointer);
    }

    /**
     * (non-PHPdoc)
     * @see ArrayStep#setLoop($flag)
     */
    public function setLoop($matrix, $row=true)
    {
        parent::setLoop($matrix);
        if($this->current()->getLoop() !== $row)
        {
            do
            {
                $rowObj =$this->current();
                $rowObj->setLoop($row);
                $last   =$rowObj->isLast();
                $this->next();
            }
            while(!$last);
            $this->rewindMatrix();
        }
    }

    /**
     * zatim neimplementovano, uvidi se dal co s tim
     */
    public function setFlags($matrix, $row=true)
    {//moznost pouzit jen pro debug
        parent::setFlags($matrix);
        if($this->current()->getFlags() !== $row)
        {
            do
            {
                $rowObj =$this->current();
                $rowObj->setFlags($row);
                $last   =$rowObj->isLast();
                $this->next();
            }
            while(!$last);
            $this->rewindMatrix();
        }
    }

    /**
     * vrati nasledujici prvek
     * @return mixed
     */
    public function next()
    {
        parent::next();
        /*
        $row    =$this->current();
        if($row->isLast())
        {
            $last  =$this->isLastMatrix();
            if($last && $this->loop)
            {
                $this->rewindMatrix();
            }
            elseif(!$last)
            {
                parent::next();
                $this->current()->rewind();
            }
        }
        else
        {
            $row->next();
        }*/
    }

    /**
     * vrati predchozi prvek
     * @return mixed
     */
    public function prev()
    {
        $row    =$this->current();
        if($row->isFirst())
        {
            $first  =$this->isFirstMatrix();
            if($first && $this->loop)
            {
                $this->end();
                $this->current()->end();
            }
            elseif(!$first)
            {
                parent::prev();
                $this->current()->end();
            }
        }
        else
        {
            $row->prev();
        }
    }

    /**
     * @see ArrayStep#getArrayCopy()
     * @return array(array())
     */
    public function getCopyMatrix()
    {
        return $this->getArrayCopy();
    }

    /**
     * vrati velikost matice osy X
     * @return int
     */
    public function getSizeX()
    {
        return $this->current()->count();
    }

    /**
     * vrati velikost matice osy Y
     * @see ArrayStep#count()
     * @return int
     */
    public function getSizeY()
    {
        return $this->count();
    }





//-----------metody ktere by se nemeli prepsat a jsou v  ramci matice-----------

    /**
     * zjisti zda ukazatel je spravne v ramci matice
     * @return bool
     */
    public function validMatrix()
    {
        return ($this->valid() === $this->current()->valid()) === true;
    }

    /**
     * vrati klic v ramci matice
     * @return mixed
     */
    public function keyMatrix()
    {
        $row    =$this->current();
        $row->seek( $this->x );
        return $row->key();
    }

    /**
     * resetuje matici a nastavi ukazatele na zacatek
     * @return void
     */
    public function rewindMatrix()
    {
        $this->rewind();
        $this->current()->rewind();
        $this->x   =ArrayStep::POINTER;
    }

    /**
     * zjisti pocet prvku v matici
     * @return int
     */
    public function countMatrix()
    {
        return $this->count() * $this->current()->count();
    }

    /**
     * zda je prvek uplne posledni
     * @return bool
     */
    public function isLastMatrix()
    {
        return ($this->isLast() === $this->current()->isLast()) === true;
    }

    /**
     * zda je prvek uplne prvni tzn [0, 0]
     * @return bool
     */
    public function isFirstMatrix()
    {
        return ($this->isFirst() === $this->current()->isFirst()) === true;
    }

    /**
     * vrati aktualni hodnotu v matici
     * @return mixed
     */
    public function currentMatrix()
    {
        return $this->current()->current();
    }





//-----------------prece se souradnicemi----------------------------------------

    /**
     * aktualni souradnice ukazatele v matici
     * 0=osa X, 1=osa Y
     * @return array
     */
    public function getCoordinate()
    {
        $vertical   =$this->getPointer();
        return array($this->x, $vertical, 'all'=>$this->x .';'. $vertical);
    }

    /**
     * nastavi vnitrni ukazatel v matici na souradnice
     * @param int $x
     * @param int $y
     * @return mixed  -vrati prvek na dannych souradnicich
     */
    public function setCoordinate($x=false, $y=false)
    {
        if(is_numeric($y))
            $this->setPointer($y);

        if(is_numeric($x))
        {
            $x  =$this->current()->setPointer($x);
            $this->x   =$x;
        }
        return $this->currentMatrix();
    }

    /**
     * vrati pozadovany radek, pokud neni vyplnena souradnice tak vrati aktualni
     * @param $y
     * @return array
     */
    public function getRow($y=false)
    {
        if($y === false)
            return $this->current();

        return $this->getValueByPointer($y);
    }

    /**
     * vrati pozadovany sloupec
     * @param $x
     * @return array
     */
    public function getColumn($x=false)
    {
        if($x === false)
            $x  =$this->x;

        $saveY  =$this->getY();
        $saveX  =$this->getX();
        $this->rewind();
        do
        {
            $row    =$this->current();
            $row->seek($x);
            $column[$this->key()]   =$row->current();
            parent::next();
        }
        while($this->isLast());

        $this->setX($saveX);
        $this->setY($saveY);
        return $column;
    }

    /**
     * vrati hodnotu danne souradni aniz by posunul vnitrni ukazatele
     *
     * @param int $x
     * @param int $y
     * @return mix
     */
    public function getValue($x=false, $y=false)
    {
        if($x === false && $y === false)
        {
            return $this->current();
        }
        else if($x === false)
        {
            $x  =$this->x;
        }
        else if($y === false)
        {
            $y  =$this->getY();
        }

        return $this->getValueByPointer($y)->getValueByPointer($x);
    }

    /**
     * vraci klic matice na souradnicich
     * @param $x
     * @param $y
     * @return array
     */
    public function getKey($x=false, $y=false)
    {
        if($x === false && $y === false)
        {
            return $this->key();
        }
        else if($x === false)
        {
            $x  =$this->x;
        }
        else if($y === false)
        {
            $y  =$this->getY();
        }

        return $this->getKeyByPointer($y)->getKeyByPointer($x);
    }




//---------------pohyby v matici------------------------------------------------

    /**
     * posun po ose Y smerem dolu
     * @see ArrayStep#next()
     * @return void
     */
    public function down()
    {
        parent::next();
        $this->current()->seek($this->x);
    }

    /**
     * posun po ose X smerem nahoru
     * @see ArrayStep#prev()
     * @return void
     */
    public function up()
    {
        parent::prev();
        $this->current()->seek($this->x);
    }

    /**
     * posune ukazatel doleva, pokud najede na zacatek zacne od konce
     *
     * @param int $step > 0 -o kolik se posunout
     * @return void
     */
    public function left($step=1)
    {
        $x  =$this->getSizeX();

        $this->x   -=(int) ($step % $x);

        if($this->x < ArrayStep::POINTER)
        {
            $this->x   +=$x;
        }
        $this->current()->seek( $this->x );
    }

    /**
     * posune ukazatel doprava, pokud najede na konec zacne od zacatku
     * @param int $step > 0 -o kolik se posunout
     * @return mix  -aktualni prvek na dannych souradnicich
     */
    public function right($step=1)
    {
        $x  =$this->getSizeX();

        $this->x   +=(int)($step % $x);

        if($this->x >= $x)
        {
            $this->x   -=$x;
        }
        $this->current()->seek( $this->x );
    }

    /**
     * $step > 0 =posun vpravo a mensi jak nula posun vlevo
     *
     * @param int $step     -o kolik se posunout
     * @return mix  -aktualni prvek na dannych souradnici
     */
    public function horizontalMove($step=1)
    {
        if($step > 0)
        {
            $this->right($step);
        }
        else if($step < 0)
        {
            $this->left(abs($step));
        }
    }

    /**
     * $step > 0 =posun dolu a mensi jak nula posun nahoru
     * @param int $step     -o kolik se posunout
     * @return mix  -aktualni prvek na dannych souradnici
     */
    public function varticalMove($step=1)
    {
        if($step > 0)
        {
            $this->down($step);
        }
        else if($step < 0)
        {
            $this->up(abs($step));
        }
    }

    /**
     * @param int $step
     * @return void
     */
    public function upRight($step=1)
    {
        $this->up($step);
        $this->right($step);
    }

    /**
     * @param int $step
     * @return void
     */
    public function upLeft($step=1)
    {
        $this->up($step);
        $this->left($step);
    }

    /**
     * @param int $step
     * @return void
     */
    public function downLeft($step=1)
    {
        $this->down($step);
        $this->left($step);
    }

    /**
     * @param int $step
     * @return void
     */
    public function downRight($step=1)
    {
        $this->down($step);
        $this->right($step);
    }

    /**
     * @param int $step
     * @return void
     */
    public function leftDiagonal($step=1)
    {
        if($step > 0)
        {
            $this->downRight($step);
        }
        else if($step < 0)
        {
            $this->upLeft(abs($step));
        }
    }

    /**
     * @param int $step
     * @return void
     */
    public function rightDiagonal($step=1)
    {
        if($step > 0)
        {
            $this->downLeft($step);
        }
        else if($step < 0)
        {
            $this->upRight(abs($step));
        }
    }
/*
    public function changeColumn($fromX, $toX)
    {

    }
*/
    /**
     * zameni radky
     * @param $fromY
     * @param $toY
     * @return false|matrix

    public function changeRow($fromY, $toY)
    {
        if(($this->offsetExists($fromY) === $this->offsetExists($toY)) === true)
        {
            $a  =$this->offsetGet($fromY);
            $this->offsetSet($fromY, $this->offsetGet($toY));
            $this->offsetSet($toY, $a);

            $matrix =$this->getMatrix();

            $this->offsetSet($toY, $this->offsetGet($fromY));
            $this->offsetSet($fromY, $a);

            return $matrix;
        }

        return false;
    }*/
}
