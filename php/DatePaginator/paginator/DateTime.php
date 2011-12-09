<?php

namespace Paginator;

class DateTime extends \Nette\Object implements IDate
{
	protected $format;
	private $formatUrl;

	/** @var DateTime */
	protected $date;

	private $current;

	/**
	 *
	 * @param string $current date formated by formatUrl
	 * @param type $format
	 * @param type $formatUrl
	 */
	public function __construct(\DateTime $current, $formatUrl = 'Y-m-d H:i:s', $format = NULL) {
		$this->format = $format? $format: 'd/m/Y H:i:s';
		$this->formatUrl = $formatUrl;
		$this->current = $current->format($formatUrl);
	}

	public function setDate(\DateTime $date) {
		$this->date = $date;
		return $this;
	}

	public function getUrl() {
		return $this->date->format($this->formatUrl);
	}

	public function getView() {
	 return $this->date->format($this->format);
	}

	public function format($format = 'Y-m-d') {
		return $this->date->format($format);
	}

	public function getYear() {
		return $this->format('Y');
	}

	public function getMonth() {
		return $this->format('m');
	}

	public function getDay() {
		return $this->format('d');
	}

	public function isCurrent() {
		return $this->current == $this->format($this->formatUrl);
	}
}
