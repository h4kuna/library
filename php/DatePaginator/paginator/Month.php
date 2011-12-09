<?php

namespace Paginator;

class Month extends DatePaginator
{
	protected $unit = 'Y-m';

	protected $interval = 'month';

	protected function setMinSql() {
		$this->minSql->modify('first day');
		if($this->unitValue > 1) {
			$this->minSql->modify('-'. ($this->unitValue - 1) .' '. $this->interval);
		}
		$this->minSql->setTime(0, 0, 0);
		parent::setMinSql();
	}
}
