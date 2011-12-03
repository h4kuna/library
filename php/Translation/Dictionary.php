<?php

namespace Translation;

abstract class Dictionary
{
    /**
     * abstract method from class Dictionary
     * @var string
     */
    const DEF_GROUP = 'globalWords';

//    /**
//     * if loaded all dictionary
//     * @var bool
//     */
//    protected $loadedAll = FALSE;

    /**
     * array of dictionary
     * @var array
     */
    private $_data = array();

    /**
     * is for check dictionary
     * @var int
     */
    protected $numDeclension = 0;

    /** @var tring */
    protected $declension = FALSE;

    /** @var bool*/
    private $loadedAll = FALSE;

    public function __construct()
    {
        if($this->declension === FALSE)
            throw new \RuntimeException('Let\'s fill $declension.');

        $this->addGroup(self::DEF_GROUP);
        $this->numDeclension = (int) substr($this->declension, -1);
    }

    public function __unset($name)
    {
        if($name == '_data')
        {
            $this->$name = array();
            $this->addGroup(self::DEF_GROUP);
        }
    }

    /**
     * name of instance
     * @return string
     */
    public function  __toString() {
        return $this->getName();
    }

    /**
     * name of class
     * @return string
     */
    public function getName()
    {
        return get_class($this);
    }

    /**
     * translate must exists
     * @param string $key
     * @return string|array
     * @throw \OutOfRangeException
     */
    public function getWord($key)
    {
        if(!isset($this->_data[$key]))
            throw new \OutOfRangeException ($this->getName() .': '. $key.' => \'\', not exists in array');
        return $this->_data[$key];
    }

    /**
     * where i can load declension
     * @return string
     */
    public function & getDeclension()
    {
        return $this->declension;
    }

    /**
     * add words to dictionary by name of method whose must be final protected
     * @param string
     * @return void
     */
    public function addGroup($group)
    {
        if(!method_exists($this, $group))
            throw new \RuntimeException ('This method '. get_class($this) .'::'. $group .' does not exists.');

        $array = $this->$group();
        if(is_array($array))
            $this->_data += $array;
    }

    /**
     * return loaded dictionary
     * @return array
     */
    public function & getDictionary()
    {
        return $this->_data;
    }

    /**
     * load all dictionary to memory
     * @return void
     */
    public function & loadAllDictionary()
    {
        if(!$this->loadedAll)
        {
            $methods    =$this->getGroups();
            foreach($methods as $val)
            {
                $this->addGroup($val);
            }
            $this->loadedAll    =TRUE;

        }
        return $this->getDictionary();
    }

    /**
     * @return return methods of class whose extends class Dictionary
     */
    public function & getGroups()
    {
        $methods    = array();
        $reflection = new \ReflectionClass($this);
        $method     = $reflection->getMethods(\ReflectionMethod::IS_FINAL);
        $groups     = array();
        foreach($method as $val)
        {
            $groups[] = $val->name;
        }
        return $groups;
    }

//    public function checkDictionary()
//    {
//        $dumped = FALSE;
//        $dic = $this->{self::DEF_GROUP}();
//        $groups = $this->getGroups();
//
//        $plural = array();
//        foreach ($groups as $group)
//        {
//            if($group == self::DEF_GROUP)
//                continue;
//            $gDic = $this->$group();
//
//            foreach ($gDic as $key => $word)
//            {
//                if(is_array($word) && $this->numDeclension != count($word))
//                {
//                    $plural[$group][$key] = 'has bad plural, must '. $this->numDeclension .' words';
//                }
//            }
//
//            $res = array_intersect_key($dic, $gDic);
//            if(!empty($res))
//            {
//                if(!$dumped)
//                {
//                    dump('Word which you can delete, because are used in globaWords.');
//                    $dumped = TRUE;
//                }
//                dump('GROUP: '.$group);
//                dump($res);
//                dump('----------');
//            }
//        }
//        if(!empty ($plural))
//        {
//            dump('Check plural.');
//            dump($plural);
//        }
//        exit;
//
//    }

    abstract protected function globalWords();
}
