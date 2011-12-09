<?php

namespace Paginator;

/**
 * umi strankovat vzestupne
 *
 * @property-read $pages
 * @property-read $maxRange
 * @property-read $minRange
 */
abstract class DatePaginator extends \Nette\Object implements \Iterator {

	const PG_FORMAT = '\Paginator\DateTime';

	/** @var DateTime */
	protected $maxRange;

	/** @var DateTime */
	protected $minRange;

	/** @var DateTime */
	protected $maxSql;

	/** @var DateTime */
	protected $minSql;

	/** @var DateTime */
	private $iterator;

	private $counter;

	/** @var PgDateTime */
	private $format = self::PG_FORMAT;

	private $maxUnit;
	private $minUnit;

//-----------------custom settings
	protected $unit;

	protected $interval;

	protected $unitValue = 1;

	public function __construct(\DateTime $minimum, \DateTime $actualDate = NULL, \DateTime $maximum = NULL) {
		$now = new \DateTime;

		$this->minRange = $minimum;

		if(!$maximum) {
			$maximum = clone $now;
		}
		$this->maxRange = $maximum;

		if(!$actualDate) {
			$actualDate = clone $now;
		}

		$this->setMaxSql($actualDate);
		$this->minSql = clone $this->maxSql;
		$this->setMinSql();
	}

	// <editor-fold defaultstate="collapsed" desc="setter">
	/**
	 * set formating class, default is self::PG_FORMAT
	 * @param type $class
	 * @return DatePaginator
	 */
	public function setFormating($class) {
		if(is_string($class)) {
			$this->format = $class;
		}
		return $this;
	}

	/**
	 * init formating class
	 * @param type $format
	 * @return DatePaginator
	 */
	public function setFormat($format = NULL) {
		if(is_string($this->format)) {
			$class = $this->format;
			$this->format = new $class($this->minSql, $this->unit, $format);
			if( !($this->format instanceof IDate) ) {
				throw new \RuntimeException('Format object must be instance of IDate');
			}
		}
		return $this;
	}

	protected function setMaxSql(\DateTime $date) {
		$this->maxSql = $date;
		if(!$this->isActual()) {
			$date->modify('last day');
			$date->setTime(23, 59, 59);
		}
		else {
			$this->maxSql = clone $this->maxRange;
		}
	}

	protected function setMinSql() {
		if($this->minSql < $this->minRange) {
			$this->minSql = clone $this->minRange;
		}
	}
	// </editor-fold>


	// <editor-fold defaultstate="collapsed" desc="getter">
	public function getSql() {
		$f = 'Y-m-d H:i:s';
		$max = $this->maxSql->format($f);
		$min = $this->minSql->format($f);
		return "BETWEEN '$min' AND '$max'";
	}

	public function getFormat() {
		return $this->format;
	}

	public function getMinRange() {
		return $this->minRange;
	}

	public function getMaxRange() {
		return $this->maxRange;
	}

	public function getUnit() {
		return $this->unit;
	}

	public function getMinSql() {
		return $this->minSql;
	}

	public function getMaxUnit() {
		if(!$this->maxUnit) {
			$this->maxUnit = $this->maxRange->format($this->unit);
		}
		return $this->maxUnit;
	}

	public function getMinUnit() {
		if(!$this->minUnit) {
			$this->minUnit = $this->minRange->format($this->unit);
		}
		return $this->minUnit;
	}

	public function getModify($mark = '+') {
		return $mark . $this->unitValue .' '. $this->interval;
	}

	public function getYear() {
		$this->maxSql->format('Y');
	}
	// </editor-fold>

	public function isActual() {
		return date($this->unit) == $this->maxRange->format($this->unit);
	}

//-----------------methods for iterator
	public function current() {
		return $this->format->setDate(clone $this->iterator);
	}

	public function next() {
		++$this->counter;
		return $this->iterator->modify($this->getModify('+'));
	}

	public function key() {
		return $this->counter;
	}

	public function valid() {
		return $this->iterator->format($this->unit) < $this->getMaxUnit();
	}

	public function rewind() {
		$this->counter = 0;
		$this->setFormat();
		return $this->iterator = clone $this->minRange;
	}
}