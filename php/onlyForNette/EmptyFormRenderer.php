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
class EmptyFormRenderer extends \Nette\Forms\DefaultFormRenderer{

    public function __construct()
    {
        $this->wrappers['controls']['container'] =
        $this->wrappers['pair']['container'] =
        $this->wrappers['control']['container'] =
        $this->wrappers['label']['container'] = '';
    }
}
