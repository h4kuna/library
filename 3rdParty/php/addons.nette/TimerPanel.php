<?php


class TimerPanel extends Object implements IDebugPanel
{
    const PRODLEVA = 1800;

    const PROTOKOL = 'file'; // safe
    protected $logFile = null;

    public $data = array('celkem'=>0, 'start'=>0, 'naposledy'=>0);

    function __construct()
    {
        $time = time();
        if( !is_file($this->getLogFile()) ){ // výchozí hodnoty
            file_put_contents($this->getLogFile(), json_encode(array(
                                "celkem" => 0,
                                "start" => $time,
                                "naposledy" => $time,
            )));
        }

        if( is_file($this->getLogFile()) ){ // načtu data
            $json = json_decode( file_get_contents($this->getLogFile()) );
        }

        if( !isset($json->celkem) OR !isset($json->naposledy) OR !isset($json->start) ){ // kontrola formátu
            unlink($this->getLogFile());
            return $this->__construct();
        }

        $data = array( // výchozí pro zápis
                        "celkem" => $json->celkem,
                        "start" => $json->start,
                        "naposledy" => $time,
        );

        if( ($time - self::PRODLEVA) < $json->naposledy ){
            $data['celkem'] += $time - $json->naposledy;
        }

        file_put_contents($this->getLogFile(), json_encode($data) );
        $this->data = $data;
    }


    function getLogFile()
    {
        if($this->logFile === null)
            $this->logFile = self::PROTOKOL . '://' . Environment::getVariable('logDir') . '/'. __CLASS__ .'.php';
        return $this->logFile;
    }

    function getTab()
    {
        $hod = floor($this->data['celkem'] / Tools::HOUR);
        $min = sprintf('%02s', floor(($this->data['celkem'] % Tools::HOUR) / 60));

        return '<img title="Čas zahájení: '. date('d.m.Y H:i', $this->data['start'])
            .'"src="'. $this->getPicture() .'" />'. $hod .':'. $min;
    }

    public function getPanel()
    {
        return NULL;
    }

    public function getId()
    {
        return __CLASS__;
    }

    protected function getPicture()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAIESURBVDjLlVJtaxpBEH7uvNSL50skFBqCiDVYpCWiIAjtx4Ih4I/zs78jkD9QioVAUBGNWigqRfpBxSO+3LnbmY13mNQWOvAwuzszz7zsQEoJBomWzWY/V6vVb5lM5oruBr/tYBQKhU+1Wu0r+/CbF6cOA02Tv9jr5gbn+TyGd3cQlQpe40nYFry9xZvLS/y8v8fm+lrZ0lJqukbCTlYwCCsWw3a7RTgex3EggLiuK5jkYkYiynYcjcLcEXOsvjvDNAx0BgPl1O31IIjEPjmBHQ5ja5rodLvK1nl48Ang9dgHRIyyN87O0LNtXFD2FLWmU4B0HKxdF99JDwhvhUCB9CPZLwDd2K/gw+kp3lsW5GYDl5wEg8heEdG7oyNkSGuE4GKBRyL1q6jX69J13b/CcRy5XC4VWPiNYzjWwAFZr9dot9tIp9Po9/uq9/l8jnK57H25L/ohAg4ejUaI0ORzuRxSqRRCoRAosw+P6BmB95inXfAWhdFqtVQ1Dg+UqqNW/Jg/WnhZ4mw2g6DJc/BkMlFnhud3cAb7ZNwOrbaaQzKZ5OXBcDiEQb/GA9XljoqU2A+u0CqzqVgswqKv5awcPB6PfSJ/Bgv6V5uEjoIN+wjQHrDmCjhzIpHAarVSLfktdGlNyTHKZf1LvAqYrNlsolQqPRFMp9MvjUbjI/5D6Dd+sP4NLTpNB1cxufkAAAAASUVORK5CYII=';
    }
}
