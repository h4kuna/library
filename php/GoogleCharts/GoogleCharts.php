<?php

namespace Charts;

/**
 * Description of GoogleCharts
 *
 * @author h4kuna
 */
abstract class GoogleCharts extends \Nette\Object
{
    const URL = 'https://chart.googleapis.com/chart';

    private static $count = 0;

    protected $url;

    protected $image;

    protected $name;

    protected $chart = array('cht'=>0);

    public function __construct($name = NULL)
    {
        $this->name = ($name === NULL)? __CLASS__: $name;
        self::$count++;
    }

    public function getImage()
    {
        if($this->image === NULL)
        {
            $this->image = \Nette\Web\Html::el('img');
            $this->image->src = $this->url;
            $this->image->alt = $this->name;
        }
        return $this->image;
    }

    public function render()
    {
        echo $this->getImage()->render();
    }

    abstract protected function setType();

}
