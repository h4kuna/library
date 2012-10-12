<?php

$_SERVER['SERVER_ADMIN'] = 'hakuna@vodafonemail.cz';

use Nette\Diagnostics\Debugger;

function e() {
    if (!Debugger::$productionMode) {
        throw new Exception('debug');
    }
}

function show($arg) {
    if (Debugger::$productionMode) {
        return NULL;
    }
    $e = new RuntimeException;
    $t = $e->getTrace();
    $t = $t[1];
    echo "<hr />";
    echo $t['line'] . '# ' . $t['file'] . '<br/>';
    foreach ($arg as $val) {
        Debugger::dump($val);
        echo "<br />";
    }
    echo "<hr />";
}

function p() {
    show(func_get_args());
}

function pd() {
    show(func_get_args());
    if (!Debugger::$productionMode) {
        exit;
    }
}
