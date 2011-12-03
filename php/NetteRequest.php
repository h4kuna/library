<?php

namespace Utility;
use Nette\Object, Nette\Environment;

class NetteRequest extends \Nette\Application\UI\Control
{
    private $module = TRUE;

    private $link;

    private $delete = FALSE;

    public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL)
    {
        if($parent === NULL)
        {
            $parent = Environment::getApplication()->getPresenter();
            $name = 'NetteRequest';
        }
        parent::__construct($parent, $name);
    }

    public function setModule($bool=FALSE)
    {
        $this->module = (bool)$bool;
    }

    public function setDelete($bool = TRUE)
    {
        $this->delete = (bool)$bool;
    }


    public function setLink($link)
    {
        $exLink = \explode(':', $link);
        if($this->module && !empty($exLink[0]))
            throw new \RuntimeException ('Let\'s write absolute link');

        end($exLink);
        $key = key($exLink);
        if($exLink[$key] == FALSE)
            $exLink[$key] = 'default';
        $this->link = ':'. trim(\implode(':', $exLink), ':');
    }

    public function render($block='content')
    {
        $app = \realpath(\Nette\Environment::getVariable('appDir')) . \DIRECTORY_SEPARATOR;
        $ds = \DIRECTORY_SEPARATOR;
        list(, $module, $presenter, $action) = \explode(':', $this->link);
        if(!$this->module)
        {
            $action = $presenter;
            $presenter = $module;
            $module = '';
        }
        else
            $module .= 'Module';

        $tplPrt = $presenter;
        $presenter .= 'Presenter';

        $path = $app . $module . $ds;
        $this->mkDir($path);

        $pFile = $path . 'presenters';
        $this->mkDir($pFile);
        $pFile .= $ds . $presenter .'.php';

        $tFile = $path . 'templates'. $ds . $tplPrt;
        $this->mkDir($tFile);
        $tFile .= $ds . $action .'.latte';
        if($this->delete)
        {
            \unlink($tFile);
            \unlink($pFile);
            $tFile = dirname($tFile);
            $pFile = dirname($pFile);
            $p = $t = TRUE;
            do{
                $this->delete($p, $pFile);
                $this->delete($t, $tFile);
            }while($p || $t);
            echo 'smazáno: '. $this->link;
        }
        else
        {
            $this->saveFile($pFile, $this->getPrsntr($module, $presenter, $action));
            $this->saveFile($tFile, $this->getTmpl($block));
            echo '<a href="'. $this->parent->link($this->link) .'">'. $this->link .'</a>';
        }
        exit;
    }

    protected function getPrsntr($module, $presenter, $action)
    {
        $base = '\BasePresenter';
        if($module != '')
        {
            $module = 'namespace '. $module .';';
            $base = \substr($base, 1);
        }

return "<?php

$module

/**
 *
 */
class $presenter extends $base
{
    protected function startup()
    {
        parent::startup();
    }

    protected function beforeRender()
    {
    }


    public function action$action()
    {
    }

    public function render$action()
    {
    }
}
";
    }

    protected function getTmpl($block)
    {
        if($block != FALSE)
            $block = "{block $block}";
        return $block ."\n\n";
    }

    protected function mkDir($dir, $acs=0755)
    {
        if(!\file_exists($dir))
            \mkdir ($dir, $acs);
    }

    protected function saveFile($fileName, $data)
    {
        if(!\file_exists($fileName))
            \file_put_contents($fileName, $data);
    }

    protected function delete(&$make, &$file)
    {
        if($make)
        {
            $make = rmdir($file);
            $file = dirname($file);
        }
    }

    static public function createLink($link, $revert = FALSE)
    {
        $p = new static;
        $p->setLink($link);
        if($revert)
            $p->setDelete();
        $p->render();
    }
}
