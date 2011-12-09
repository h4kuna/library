<?php

namespace Paginator;

/**
 * @property-read $dp
 */
class VisualDatePaginator extends \Nette\Application\UI\Control {
	/** @var DatePaginator */
	private $dp;

	protected $format = 'm/Y';

	const MONTH = 'month';
	const YEAR = 'year';
	const WEEK = 'week';
	/**
	 * LOWER CASE. You can choose from only a constant amount.
	 * @var string
	 */
	protected $unit = self::MONTH;

	/** @persistent */
	public $date;

	public function __construct($minDate, \Nette\ComponentModel\IContainer $parent = NULL, $name = NULL, $formating = NULL) {
		parent::__construct($parent, $name);

		$date = $this->date;
		if($this->date) {
			$date = new \DateTime($this->date);
		}

		if(!($minDate instanceof \DateTime)) {
			$minDate = new \DateTime($minDate);
		}

		$class = '\\'. __NAMESPACE__ . '\\'.ucfirst($this->unit);

		$this->dp = new $class($minDate, $date);
		if($formating) {
			$this->dp->setFormating($formating);
		}
		$this->dp->setFormat($this->format);
	}

	public function render() {
		$tpl = $this->template;
		$tpl->dp = $this->dp;
		$tpl->setFile(dirname(__FILE__) . '/templates/'. $this->unit .'.latte');
		$tpl->render();
	}

	public function getDp() {
		return $this->dp;
	}
}