<?php declare(strict_types=1);

namespace h4kuna\Library\Tests;

use Tracy;
use Tester;

require __DIR__ . '/../../vendor/autoload.php';

if (defined('__PHPSTAN_RUNNING__')) {
	return;
}

date_default_timezone_set('Europe/Prague');

Tester\Environment::setup();

Tracy\Debugger::enable(false, __DIR__ . '/../temp');
