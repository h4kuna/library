<?php


class MatrixMath extends MatrixBase
{
    /**
     * zda se hodnota nachazi na diagonale
     * @return bool
     */
    public function isDiagonal()
    {
        return $this->getPointer() === $this->current()->getPointer();
    }

    /**
     * vrati hodnoty na hladni diagonale
     * @return array
     */
    public function getDiagonal()
    {
        $newArray   =array();
        do
        {
            $newArray[$this->key()]   =$this->current();
            $this->downRight();
        }while(!$this->isFirst());

        return $newArray;
    }

    /**
     * zjisti zda se jedna o jednotkovou matici
     * @return bool
     */
    public function isUnitMatrix()
    {
        do
        {
            $val    =$this->current();
            if($this->isDiagonal())
            {
                if($val != 1)
                {
                    return false;
                }
            }
            else
            {
                if($val != 0)
                {
                    return false;
                }
            }

            $this->next();
        }while(!$this->isFirst());

        return true;
    }

    /**
     * zjisti zda se jedna o nulovou matici
     * @return bool
     */
    public function isNullMatrix()
    {
        do
        {
            $val    =$this->current();
            if($val != 0)
            {
                return false;
            }

            $this->next();
        }while(!$this->isFirst());

        return true;
    }

    /**
     * zjisti zda je matice ctvercova
     * @return bool
     */
    public function isSquareMatrix()
    {
        return $this->count() === $this->current()->count();
    }

    /**
     * vrati nejmensi cislo z matice
     * @return float|int
     */
    public function getMin()
    {
        $matrix =$this->getArrayCopy();
        $min    =false;
        foreach($matrix as $array)
        {
            $num    =min($array->getArrayCopy());
            if($min === false || $num < $min)
            {
                $min    =$num;
            }
        }

        return $min;
    }


    /**
     * vrati nejvetsi cislo z matice
     * @return int|float
     */
    public function getMax()
    {
        $matrix =$this->getArrayCopy();
        $max    =false;
        foreach($matrix as $array)
        {
            $num    =max($array->getArrayCopy());
            if($max === false || $num > $max)
            {
                $max    =$num;
            }
        }

        return $max;
    }


    /**
     * transponuje matici
     * @return matrix
     */
    public function getTranspose()
    {
        $matrix =$this->rewind();

        $transponse =array();
        do
        {
            $transponse[ $this->key() ]    =$this->getColumn();
            $this->right();
        }while(!$this->isFirst());

        $this->revert();

        return $transponse;
    }
}
