<?php
namespace Paginator;

class Week extends DatePaginator
{
	protected $unit = 'Y-m-d';

	protected $interval = 'week';

	protected function setMaxSql(\DateTime $date) {
		$this->maxSql = $date;
		$date->modify('next monday');
		$date->setTime(0, 0, 0);
		$date->modify('-1 second');
	}

	protected function setMinSql() {
		$this->minSql->modify($this->getModify('-'));
		$this->minSql->modify('+1 second');
	}
}

