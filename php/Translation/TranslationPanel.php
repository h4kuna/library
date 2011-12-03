<?php

namespace Translation;

use Nette\Diagnostics\Debugger, Nette\Environment, Nette\Object, Nette\Diagnostics\IBarPanel;

/**
 * Description of TranslationPanel
 *
 * @author Milan Matějček
 */
class TranslationPanel extends Object implements IBarPanel
{
    private static $words = array();
    private static $counter = array();
    private $notUsed = NULL;

    /** @var Dictionary */
    private $lang;

    private static $isProduction = NULL;

    public function __construct(Dictionary $lang = NULL)
    {
        $this->lang = $lang;
    }

    public static function add(\Exception $exception)
    {
        if(!self::isProduction())
            self::$words[] = $exception->getMessage();
        else
            Debugger::log($exception);
    }

    public static function counter($word)
    {
        if(self::isProduction())
            return;

        if(is_array($word))
            $word = $word[0];

        if(!isset(self::$counter[$word]))
            self::$counter[$word] = 0;
        self::$counter[$word]++;
    }

    /**
     * production mode
     * @return bool
     */
    protected static function isProduction()
    {
        if(self::$isProduction === NULL)
            self::$isProduction = Environment::isProduction();
        return self::$isProduction;
    }

    /**
	 * Renders HTML code for custom tab.
	 * @return void
	 */
	public function getTab()
    {
        $count = count(self::$words);

        if($this->lang !== NULL)
        {
            $dic = $this->lang->getDictionary();
            $res = array_diff_key($dic, self::$counter);
            if(!empty($res))
            {
                $count += count($res);
                $this->notUsed = '<h1>Conter of words</h1>';
                foreach (self::$counter as $val => $num)
                {
                    $this->notUsed .= $num .'x - '. $val .'<br>';
                }

                $this->notUsed .= '<h1>Not used</h1>';
                $this->notUsed .= implode('<br />', \array_keys($res));
            }
        }


        if($count == 0)
            return NULL;
        return '<img src="'. $this->getImage() .'"/> '.$this->lang.' <span class="nette-warning">'. $count .' errors</span>';
    }

	/**
	 * Renders HTML code for custom panel.
	 * @return void
	 */
	public function getPanel()
    {
        return '<h1>Forgot words</h1><span>'. implode('<br />', self::$words) .'</span>'. $this->notUsed;
    }

	/**
	 * Returns panel ID.
	 * @return string
	 */
	public function getId()
    {
        return __CLASS__;
    }

    protected function getImage()
    {
        return 'data:image/jpg;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAAXNSR0IArs4c6QAAAAlwSFlzAAALEwAACxMBAJqcGAAAAAd0SU1FB9sBDAc4A0hCK4AAAAAdaVRYdENvbW1lbnQAAAAAACAgICAgICAgICAgICAgICAgZC5lBwAAAcpJREFUOMudkr1uU1EQhL/dc861jQusNJQICSIahISQaCDp0sELQEth0SHRIZ4CKS9AQU1DgYSUmifABQgiiHGwiYP/rq/vLoUJkbEdJLY5vzuzMxp58/qtT0ZjYsz4vN8mZBFVxR0cx8URBAFMHERQEVKWKN2RJ1v33do/qVSrZNUKUk2klEgxodVEqCSymAgpzs9ZIknkwtWLfJAhMUyMfrvHQIWT8vUbcDAzLm/doL5znRgMEEFU//w5hVpdUgp5BWoCWnjJ/5Tq3JlYeIn8HvKEebe3B8BYAu9jg5f1K/S1siAxFQLuaHTFBcQXGZob2zw9f4sfWuHBsLUsA8HNUbE5pK8QPtLEq9olNoujv5ohzxxTQWfBkLOMW/MQgiKiRJP1ntdsxt3xR1qpsXDvQDZVMIg1AqMVRLu9PcYEWqnBi/rmSgJ3J6LLEzQ3ts/OATCNjrqhRfx3cFayi1OaEaMGzAwVATkBO02GyzKBA1kOVhTEmzu3eVeUTPpD+sd9yu4QE4iiBJk7rSqIKBLmK+5k5zKajx8i00nu+XiClUZZlszGOYftDt8POvS+fGOwf8jXTpujgy6DTx1G3WNmZly7d4dHz5/xCzGQtnO+2eP5AAAAAElFTkSuQmCC';
    }
}
