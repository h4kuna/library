<?php

use Utility\Stopwatch;

function start($name=NULL)
{
    Stopwatch::start($name);
}

function stop($name=NULL)
{
    Stopwatch::stop($name);
}
