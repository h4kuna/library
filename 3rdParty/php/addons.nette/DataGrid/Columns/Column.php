<?php

namespace DataGrid\Columns;
use Nette, Nette\Web\Html, Datagrid, DataGrid\Filters;

/**
 * Base class that implements the basic common functionality to data grid columns.
 *
 * @author     Roman Sklenář
 * @copyright  Copyright (c) 2009 Roman Sklenář (http://romansklenar.cz)
 * @license    New BSD License
 * @example    http://addons.nette.org/datagrid
 * @package    Nette\Extras\DataGrid
 */
abstract class Column extends Nette\ComponentContainer implements IColumn
{
	/** @var Nette\Web\Html  table header element template */
	protected $header;

	/** @var Nette\Web\Html  table cell element template */
	protected $cell;

	/** @var string */
	protected $caption;

	/** @var int */
	protected $maxLength = 100;

	/** @var array  of arrays('pattern' => 'replacement') */
	public $replacement;

	/** @var array  of callback functions */
	public $formatCallback = array();

	/** @var bool */
	public $orderable = TRUE;

	/** @var string */
	public static $ajaxClass = 'datagrid-ajax';


	/**
	 * Data grid column constructor.
	 * @param  string  textual caption of column
	 * @param  int     maximum number of dislayed characters
	 */
	public function __construct($caption = NULL, $maxLength = NULL)
	{
		parent::__construct();
		$this->addComponent(new Nette\ComponentContainer, 'filters');
		$this->header = Html::el();
		$this->cell = Html::el();
		$this->caption = $caption;
		if ($maxLength !== NULL) $this->maxLength = $maxLength;
		$this->monitor('DataGrid\DataGrid');
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\IComponent
	 * @return void
	 */
	protected function attached($component)
	{
		if ($component instanceof DataGrid\DataGrid) {
			$this->setParent($component);

			if ($this->caption === NULL) {
				$this->caption = $this->getName();
			}
		}
	}


	/**
	 * Returns DataGrid.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return DataGrid\DataGrid
	 */
	public function getDataGrid($need = TRUE)
	{
		return $this->lookup('DataGrid\DataGrid', $need);
	}



	/********************* Html objects getters *********************/



	/**
	 * Returns headers's HTML element template.
	 * @return Nette\Web\Html
	 */
	public function getHeaderPrototype()
	{
		return $this->header;
	}


	/**
	 * Returns table's cell HTML element template.
	 * @return Nette\Web\Html
	 */
	public function getCellPrototype()
	{
		return $this->cell;
	}


	/**
	 * Setter / property method.
	 * @return string
	 */
	public function getCaption()
	{
		if ($this->caption instanceof Html && $this->caption->title) {
			return $this->caption->title($this->getDataGrid()->translate($this->caption->title));
		} else {
			return $this->getDataGrid()->translate($this->caption);
		}
	}



	/********************* interface DataGrid\Columns\IColumn *********************/



	/**
	 * Is column orderable?
	 * @return bool
	 */
	public function isOrderable()
	{
		return $this->orderable;
	}


	/**
	 * Gets header link (order signal)
	 * @param  string  direction of sorting (a|d|NULL)
	 * @return string
	 */
	public function getOrderLink($dir = NULL)
	{
		return $this->getDataGrid()->link('order', array('by' => $this->getName(), 'dir' => $dir));
	}


	/**
	 * Has column filter box?
	 * @return bool
	 */
	public function hasFilter()
	{
		return $this->getFilter(FALSE) instanceof DataGrid\Filters\IColumnFilter;
	}


	/**
	 * Returns column's filter.
	 * @param  bool   throw exception if component doesn't exist?
	 * @return DataGrid\Filters\IColumnFilter|NULL
	 */
	public function getFilter($need = TRUE)
	{
		return $this->getComponent('filters')->getComponent($this->getName(), $need);
	}


	/**
	 * Formats cell's content. Descendant can override this method to customize formating.
	 * @param  mixed
	 * @param  DibiRow|array
	 * @return string
	 */
	public function formatContent($value, $data = NULL)
	{
		return (string) $value;
	}


	/**
	 * Filters data source. Descendant can override this method to customize filtering.
	 * @param  mixed
	 * @return void
	 */
	public function applyFilter($value)
	{
		return;
	}



	/********************* Default sorting and filtering *********************/



	/**
	 * Adds default sorting to data grid.
	 * @param string
	 * @return DataGrid\Columns\Column  provides a fluent interface
	 */
	public function addDefaultSorting($order = 'ASC')
	{
		$orders = array('ASC', 'DESC', 'asc', 'desc', 'A', 'D', 'a', 'd');
		if (!in_array($order, $orders)) {
			throw new \InvalidArgumentException("Order must be in '" . implode(', ', $orders) . "', '$order' given.");
		}

		parse_str($this->getDataGrid()->defaultOrder, $list);
		$list[$this->getName()] = strtolower($order[0]);
		$this->getDataGrid()->defaultOrder = http_build_query($list, '', '&');

		return $this;
	}


	/**
	 * Adds default filtering to data grid.
	 * @param string
	 * @return DataGrid\Columns\Column  provides a fluent interface
	 */
	public function addDefaultFiltering($value)
	{
		parse_str($this->getDataGrid()->defaultFilters, $list);
		$list[$this->getName()] = $value;
		$this->getDataGrid()->defaultFilters = http_build_query($list, '', '&');

		return $this;
	}


	/**
	 * Removes data grid's default sorting.
	 * @return DataGrid\Columns\Column  provides a fluent interface
	 */
	public function removeDefaultSorting()
	{
		parse_str($this->getDataGrid()->defaultOrder, $list);
		if (isset($list[$this->getName()])) unset($list[$this->getName()]);
		$this->getDataGrid()->defaultOrder = http_build_query($list, '', '&');

		return $this;
	}


	/**
	 * Removes data grid's default filtering.
	 * @return DataGrid\Columns\Column  provides a fluent interface
	 */
	public function removeDefaultFiltering()
	{
		parse_str($this->getDataGrid()->defaultFilters, $list);
		if (isset($list[$this->getName()])) unset($list[$this->getName()]);
		$this->getDataGrid()->defaultFilters = http_build_query($list, '', '&');

		return $this;
	}




	/********************* filter factories *********************/



	/**
	 * Alias for method addTextFilter().
	 * @return DataGrid\Filters\IColumnFilter
	 */
	public function addFilter()
	{
		return $this->addTextFilter();
	}


	/**
	 * Adds single-line text filter input to data grid.
	 * @return DataGrid\Filters\IColumnFilter
	 * @throws \InvalidArgumentException
	 */
	public function addTextFilter()
	{
		$this->_addFilter(new Filters\TextFilter);
		return $this->getFilter();
	}


	/**
	 * Adds single-line text date filter input to data grid.
	 * Optional dependency on DatePicker class (@link http://nettephp.com/extras/datepicker)
	 * @return DataGrid\Filters\IColumnFilter
	 * @throws \InvalidArgumentException
	 */
	public function addDateFilter()
	{
		$this->_addFilter(new Filters\DateFilter);
		return $this->getFilter();
	}


	/**
	 * Adds check box filter input to data grid.
	 * @return DataGrid\Filters\IColumnFilter
	 * @throws \InvalidArgumentException
	 */
	public function addCheckboxFilter()
	{
		$this->_addFilter(new Filters\CheckboxFilter);
		return $this->getFilter();
	}


	/**
	 * Adds select box filter input to data grid.
	 * @param  array   items from which to choose
	 * @param  bool    add empty first item to selectbox?
	 * @param  bool    translate all items in selectbox?
	 * @return DataGrid\Filters\IColumnFilter
	 * @throws \InvalidArgumentException
	 */
	public function addSelectboxFilter($items = NULL, $firstEmpty = TRUE, $translateItems = TRUE)
	{
		$this->_addFilter(new Filters\SelectboxFilter($items, $firstEmpty));
		return $this->getFilter()->translateItems($translateItems);
	}


	/**
	 * Internal filter adding routine.
	 * @param  DataGrid\Filters\IColumnFilter $filter
	 * @return void
	 */
	private function _addFilter(DataGrid\Filters\IColumnFilter $filter)
	{
		if ($this->hasFilter()) {
			$this->getComponent('filters')->removeComponent($this->getFilter());
		}
		$this->getComponent('filters')->addComponent($filter, $this->getName());
	}
}
