<?php

namespace Utility;

use Nette;

/*
 * @example
$p = new \Utility\Period(new \Utility\DateTime('monday previous week'), new \Utility\DateTime('monday +3 week'));
$p->setFrom(new \DateTime('2012-04-02'), 2);
foreach ($p as $k => $v) {
	p($k, $v, $p->currentWeek());
}
 */


/**
 *
 *
 * @property-read sql
 */
class Period extends Nette\Object implements \Iterator
{
	/** @var DateTime */
	private $start;

	/** @var DateTime */
	private $end;

	/** @var DateTime */
	private $actual;

	/** @var DateInterval */
	private $interval;
//-----------------Iterator
	private $key;

	/** @var DateTime */
	private $pointer;

	/** @var DateTime */
	private $from;

	public function __construct(\DateTime $start, \DateTime $end, $actual = NULL, $interval = '+1 week')
	{
		if (is_string($interval)) {
			$interval = \DateInterval::createFromDateString($interval);
		}
		$this->end = $end;
		$this->start = $start;
		$this->interval = $interval;
		$this->setActual($actual);
	}

	public function getSql($format = 'Y-m-d H:i:s')
	{
		$end = clone $this->end;
		$end->modify('-1 second');
		return "BETWEEN '{$this->start->format($format)}' AND '{$end->format($format)}'";
	}

	public function setActual($date)
	{
		if ($date instanceof \DateTime) {
			$this->actual = $date;
		} else {
			$this->actual = new \DateTime($date);
		}
		return $this->actual;
	}

	public function getEnd($format = NULL)
	{
		if ($format) {
			return $this->end->format($format);
		}
		return $this->end;
	}

	public function getStart($format = NULL)
	{
		if ($format) {
			return $this->start->format($format);
		}
		return $this->start;
	}

//-----------------Iterator
	public function currentWeek()
	{
		return $this->isCurrent('Y-W');
	}

	public function currentDay()
	{
		return $this->isCurrent('Y-m-d');
	}

	public function currentMonth()
	{
		return $this->isCurrent('Y-m');
	}

	public function isCurrent($format)
	{
		return $this->current()->format($format) == $this->actual->format($format);
	}

	public function setFrom(\DateTime $date = NULL)
	{
		$this->from = $date;
	}

	protected function getFrom()
	{
		if (!$this->from) {
			$this->from = clone $this->start;
		}

		return clone $this->from;
	}

	public function current()
	{
		return $this->pointer;
	}

	public function next()
	{
		++$this->key;
		$this->pointer->add($this->interval);
	}

	public function rewind()
	{
		$this->key = 0;
		$this->pointer = $this->getFrom();
	}

	public function key()
	{
		return $this->key;
	}

	public function valid()
	{
		return $this->pointer < $this->end;
	}

}