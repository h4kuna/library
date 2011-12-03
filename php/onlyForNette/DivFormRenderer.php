<?php
/**
 * potřebné css je v libs v danné složce
 */
namespace NettePlugin;
/**
 * Description of DivFormRenderer
 *
 * @author Milan Matějček
 */
class DivFormRenderer extends \Nette\Forms\Rendering\DefaultFormRenderer{

    public function __construct()
    {
        $this->wrappers['controls']['container'] =  'div class=divForm';
        $this->wrappers['pair']['container'] = '';
        $this->wrappers['control']['container'] = 'div class=input';
        $this->wrappers['label']['container'] = 'div class=label';
        $this->wrappers['control']['requiredsuffix'] = '<span>*</span>';
        $this->wrappers['label']['suffix'] = ':';
    }
}
