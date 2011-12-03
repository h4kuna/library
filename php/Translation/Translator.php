<?php

namespace Translation;

use Nette\Environment, Nette\Application\UI\Control, Nette\Localization\ITranslator,
        Nette\ComponentModel\IComponent, Nette\Diagnostics\Debugger;
use Utility\Math;
/**
 * @author Milan Matějček
 */

require_once __DIR__ .'/Dictionary.php';
require_once __DIR__ .'/Declension.php';
require_once __DIR__ .'/TranslationPanel.php';

abstract class Translator extends Control implements ITranslator
{
    /**
     * abstract method from class Dictionary
     * @var string
     */
    const DEF_GROUP = 'globalWords';

    /**
     * faster question by method isProduction()
     * @var boolean
     */
    private static $isProduction = NULL;

    /**
     * allowed languages
     * @var array
     */
    public static $languages = array('cs', 'en', 'de');

    /**
     * default language LOWERCASE
     * @var string
     */
    protected $defLang = 'cs';

    /**
     * white space is nonvalid path
     * @var sting path
     */
    protected $sourcePath = ' ';

    /**
     * doba expirace session
     * @var int
     */
    protected $expiration = 1209600;

    /**
     * param of Route from nette
     * @var string
     */
    protected $param = 'lang';

    /**
     * actual web lang
     * @var Dictionary
     * @return string name of instance
     */
    protected $webLang = NULL;


//-----------------for debug mode--------------------------
    /**
     * hide translate text
     * @var bool
     */
    static private $hide = FALSE;

    /**
     *
     * @param $sourcePath
     * @param $lang
     * @return void
     */
    public function __construct(IComponent $parent = NULL, $name = NULL)
    {
        if($parent === NULL)
        {
            $parent = Environment::getApplication ()->getPresenter();
            $name = 'Translator';
        }
        parent::__construct($parent, $name);
        $this->checkOnStartUp();
        $this->setLanguage($this->parent->getParam($this->param));
        $groups = explode(':', $this->parent->name);
        foreach($groups as $group)
            $this->addGroup( $group );
        Debugger::addPanel(new TranslationPanel($this->webLang));
    }


//-----------------debug method----------------------------
    /**
     * hide all words in template whose will translate
     */
    public static function hide()
    {
        if(!self::isProduction())
            self::$hide = TRUE;
    }

//-----------------normaly methods-------------------------
    /**
     * name of actual language, whose choose on web
     * @return string
     */
    public function getWebLang()
    {
        return $this->webLang === NULL? $this->defLang: $this->webLang->getName();
    }

    /**
     * is setup actual lang
     * @return bool
     */
    public function isActual()
    {
        return $this->getWebLang() == $this->defLang;
    }

    /**
     * implements method from interface
     * @param string $message
     * @param float $count -do klice v databazi dejte na konec cislo, napr: dog1 => pes, dog2 => psi, dog => psů
     * @return mix
     */
    public function translate($message, $count=NULL)
    {
        $key = 0;
        if($count !== NULL) {
            $key = $this->declension( abs( (double)Math::stroke2point($count) ) );
        }

        //webLang je objekt tudíž má svůj překladový soubor
        if($this->webLang !== NULL)
        {
            if(is_array($message))
                $message = $message[0];

            try {
                $trans = $this->webLang->getWord($message);
            } catch(\OutOfRangeException $e) {
                TranslationPanel::add($e);
                $trans = $message;
            }
        }
        else
            $trans = $message;


        //debugovací mod
        TranslationPanel::counter($message);

        //překlad pluralu
        if(is_array($trans))
        {
            if(!isset($trans[$key]))
            {
                $e = new \OutOfRangeException ($this->getWebLang().': Add value for '. (is_array ($message)? $message[0]: $message) .'['. $key .'], but now is small array');
                $trans = current($trans);
                TranslationPanel::add($e);
            }
            else
                $trans = $trans[$key];

            $trans = vsprintf($trans, $count);
        }
        elseif($count != 0)
        {
            $e = new \OutOfRangeException ($this->getWebLang().': Must be an plural written as array(), singular given as string, "'. $message .'" rewrite to: \''. $message .'\' => array(\''. $trans .'\')');
            TranslationPanel::add($e);
        }

        if (!self::isProduction() && self::$hide) {
            return '&nbsp;';
        }
        return $trans;
    }



    /**
     * group is name method in class whose extends class Dictionary
     * load group to memory
     * @param group $group
     * @return void
     */
    public function addGroup($group)
    {
        try{
            $this->webLang && $this->webLang->addGroup($group);
        }
        catch (\RuntimeException $e){
            TranslationPanel::add($e);
        }
    }

    /**
     * setUp session and local property
     * @param string $newLang
     * @return void
     */
    public function setLanguage($newLang=NULL)
    {
        if(!in_array($newLang, self::$languages))
            $this->redirectDefLang();

        $this->webLang = $this->loadFile($newLang);

        $this->checkSession();
    }

//-----------------protected----------------------------------------------------
    /**
     *
     * @param float $number
     * @return int
     */
    protected function declension($number)
    {
        return call_user_func($this->getDeclension(), $number);
    }

    /**
     * pokud návštěvník příjde na stránky, tak ho přesměruje na jeho poslední
     * zvolený jazyk
     * @return void
     */
    protected function checkSession()
    {
        $session = Environment::getSession(__CLASS__);
        $session->setExpiration($this->expiration);

        $check = Environment::getSession('check');

        if( !isset($check->check) )
        {
            $check->check = 1;
            if(isset($session->lang))
            {//jazyk ulozeny v session
                $this->defLang = $session->lang;
                $this->redirectDefLang(FALSE);
                exit;
            }

            $lang = substr(Environment::getHttpRequest()->headers['accept-language'], 0, 2);
            //$lang = \in_array($lang, self::$languages)? $lang: self::$languages[1];
            if($this->getWebLang() != $lang)
            {//vyber a presmerovani na jazyk podle prohlizece
                $this->defLang = $session->lang = $lang;
                $this->redirectDefLang(FALSE);
                exit;
            }

            $session->lang = $this->getWebLang();
        }
        elseif($this->getWebLang() != $session->lang)
            $session->lang = $this->getWebLang();
    }

    /**
     * redirect to default language
     * @return void
     */
    protected function redirectDefLang($message=TRUE)
    {
        if($message)
            $this->parent->flashMessage('Sorry this language, does\'t exists.', 'warning');
        $this->parent->redirect('this', array($this->param => $this->defLang));
    }

    /**
     * první kontrola před spuštěním skriptu
     */
    protected function checkOnStartUp()
    {
        if(!in_array($this->defLang, self::$languages))
            throw new \RuntimeException('Default language is not defined in base self::$languages');

        if(!\realpath($this->sourcePath))
            throw new \FileNotFoundException ('Source path does not exists.');

        if(!isset($this->getParent()->params[$this->param]))
            throw new \RuntimeException ('Let \'s define self::$param same name as in Route.');

        throw new \RuntimeException('Let \'s add method: "protected function checkStartOnUp(){}" to your class.');
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


//-----------------path for source or temp file---------------------------------
    protected function getDeclension()
    {
        return ($this->webLang === NULL)? '\Translation\Declension::czechDeclension4': $this->webLang->getDeclension();
    }

    /**
     * setup temp
     * @return path to temp
     */
    protected function getTemp()
    {
        return Environment::getVariable('tempDir') . \DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $lang
     * @return string path to cache file
     */
    protected function getCache($lang)
    {
        return $this->getTemp() . $lang .'.php';
    }

    /**
     * @param string $lang
     * @return string name of language (class)
     */
    protected function loadFile($lang)
    {
        $file = $this->sourcePath . \DIRECTORY_SEPARATOR . $lang .'.php';

        if(file_exists($file))
        {
            require_once $file;
            return new $lang;
        }
        elseif($lang != $this->defLang)
        {
            $e = new \RuntimeException ('The source file for language does not exists.');
            if(self::isProduction())
            {
                Debuger::log($e);
                $this->redirectDefLang();
            }
            $this->createExampleFile($lang);
            throw $e;
        }
        return NULL;
    }

    /**
     * controling if cache exists
     * @param string $newLang
     * @return void
     */
    protected function checkCache($newLang)
    {
        $cache  =$this->getCache($newLang);
        $source =$this->loadFile($newLang);
        if( !file_exists($cache) )
        {
            if(file_exists($source))
            {
                copy($source, $cache);
            }
            elseif(!self::isProduction())
            {
                $this->createExampleFile($newLang, $this->getDeclension());
                throw new \RuntimeException('Doesn\'t exists source file on this path: "'. $source .'."
                How look like it, you can see to: '. $cache .'
                Remeber source file can\'t be writeable!!! It can\'t locate in temp dir!!!');
            }
            elseif($newLang !== $this->defLang)
            {
                $newLang    =$this->defLang;
            }
        }
        elseif(!self::isProduction())
        {
            if(!file_exists($source))
                throw new \RuntimeException('Let\'s copy source file from cache '. $cache .', to '.$source);

            if(filemtime($source) > filemtime($cache))
                copy($source, $cache);
        }
    }


//-----------------creating example source file---------------------------------
    /**
     *
     * @param string $path
     * @param array $dictionary
     * @param string $lang
     * @param string $declension
     * @return bool
     */
    public function createSourceFile($path, array $dictionary, $lang=NULL, $declension=NULL)
    {
        if($lang === NULL)
            $lang = $this->getWebLang();
        return file_put_contents($path, $this->makeClass($lang, $dictionary, $declension));
    }

    /**
     * ze zadanych udaju sestavi tridu slovniku a ulozi ji
     * @param array $className
     * @param $body
     * @param $declension sklonovani ktere bude nactene z databaze
     * @return void
     */
    protected function makeClass($className, array &$body, $declension)
    {

        $class  ='<?php final class '. $className .' extends '. __NAMESPACE__ .'\Dictionary{ protected $declension =\''. $declension .'\';';
        foreach($body as $method => $array)
        {
            $class  .='final protected function '. $method .'(){return '. var_export($array, TRUE) .';}';
        }
        return $class . '}';
    }

    /**
     * example file how look like it
     * @return void
     */
    public function createExampleFile($lang, $declension=NULL)
    {
        $example = $this->getCache($lang);
        if(file_exists($example))
            rename($example, $example .'_save');
        $this->createSourceFile($example, array(self::DEF_GROUP => array('%s pes'=>array('%s pes', '%s psi', '%s psů', '%s psa'))), $lang, $declension);
    }
}
