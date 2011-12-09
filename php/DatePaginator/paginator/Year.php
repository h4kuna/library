<?php

namespace Paginator;

class Year extends DatePaginator {
	protected $unit = 'Y-m';

	protected $interval = 'year';

	protected function setMinSql() {
		$this->minSql->modify('-'. $this->unitValue .' '. $this->interval);
		$this->minSql->modify('first day');
		$this->minSql->modify('+1 month');
		$this->minSql->setTime(0, 0, 0);
		parent::setMinSql();
	}
}
