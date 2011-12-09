<?php

namespace Paginator;

interface IDate
{
	public function getView();
	public function getUrl();
	public function format();
	public function setDate(\DateTime $date);
	public function isCurrent();
}

