<?php

$_SERVER['SERVER_ADMIN'] = 'hakuna@vodafonemail.cz';

use Nette\Diagnostics\Debugger;

function e() {
	if (!Debugger::$productionMode)
		throw new Exception('debug');
}

function show($arg) {
	if (!Debugger::$productionMode) {
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
}

function p() {
	$arg = func_get_args();
	show($arg);
}

function pd() {
	$arg = func_get_args();
	show($arg);
	if (!Debugger::$productionMode)
		die();
}
