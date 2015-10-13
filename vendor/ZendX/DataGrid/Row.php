<?php
/**
 * @author 		VanCK
 * @category   	ZendX library
 * @copyright  	http://nhanh.vn
 * @license    	http://nhanh.vn/license
 */
namespace ZendX\DataGrid;

class Row
{
	const TYPE_HEADER = 1;
	const TYPE_BODY = 2;

	/**
	 * @var DataGrid
	 */
	protected $dataGrid;

	/**
	 * @var array
	 */
	protected $attributes;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var array
	 */
	protected $columns;

	/**
	 * @var int
	 */
	protected $type;

	/**
	 * @return DataGrid
	 */
	public function getDataGrid() {
		return $this->dataGrid;
	}

	/**
	 * @param DataGrid $dataGrid
	 */
	public function setDataGrid($dataGrid) {
		$this->dataGrid = $dataGrid;
	}

	/**
	 * @return the $attributes
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * @param multitype: $attributes
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}

	/**
	 * @return the $options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @return the $options
	 */
	public function getOption($key) {
		return isset($this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * @param multitype: $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * @return the $columns
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * @param multitype: $columns
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @param number $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @param null|array $row
	 * @throws Exception
	 */
	public function __construct($row = null)
	{
		if(isset($row['attributes'])) {
			$this->setAttributes($row['attributes']);
		}
		if(isset($row['options'])) {
			$this->setOptions($row['options']);
		}
		if(isset($row['columns'])) {
			$this->addColumns($row['columns']);
		}
	}

	/**
	 * @param string $key
	 * @param string $val
	 */
	public function addAttribute($key, $val) {
		$this->attributes[$key] = $val;
	}

	/**
	 * @param array $columns
	 * @throws Exception
	 */
	public function addColumns($columns)
	{
		if(!is_array($columns)) {
			throw new \Exception("columns must be an array");
		} else {
			foreach($columns as $column) {
				$this->addColumn($column);
			}
		}
	}

	/**
	 * @param Column|array $column
	 */
	public function addColumn($column)
	{
		if($column instanceof Column) {
			$column->setRow($this);
			$this->columns[$column->getName()] = $column;
			return $column;
		} else if(is_array($column)) {
			$c = new Column($column);
			$c->setRow($this);
			$this->columns[$c->getName()] = $c;
			return $c;
		} else {
			throw new \Exception('column must be instance of ZendX\DataGrid\Column or an array');
		}
	}

	/**
	 * prepare sort icon for columns
	 */
	public function sortColumns()
	{
		if(is_array($this->getColumns())) {
			$request = Zend_Controller_Front::getInstance()->getRequest();

			$sorts = $this->getOption('sortColumns');
			$sortingColumn = $request->getParam('sort', isset($sorts['defaultColumn']) ? $sorts['defaultColumn'] : '');
			$sortingDir = $request->getParam('dir', isset($sorts['defaultDir']) ? $sorts['defaultDir'] : 'desc');

			foreach ($this->getColumns() as $column) {
				/* @var $column ZendX_DataGrid_Column */
				if($column->getOption('sortable')) {
					if($sortingColumn == $column->getName()) {
						$column->addAttributeValue('class', 'sortable sorting');
						if($sortingDir == 'asc') {
							$href = Logistics_Uri::build($_SERVER['REQUEST_URI'], array(
								'sort' => $column->getName(),
								'dir' => 'desc',
							));
							$column->prependContent("<a href='$href'><i class='fa fa-sort-up'></i>");
							$column->appendContent('</a>');
						} else {
							$href = Logistics_Uri::build($_SERVER['REQUEST_URI'], array(
								'sort' => $column->getName(),
								'dir' => 'asc'
							));
							$column->prependContent("<a href='$href'><i class='fa fa-sort-down'></i>");
							$column->appendContent('</a>');
						}
					} else {
						$column->addAttributeValue('class', 'sortable');
						$href = Logistics_Uri::build($_SERVER['REQUEST_URI'], array(
							'sort' => $column->getName(),
							'dir' => 'desc'
						));
						$column->prependContent("<a href='$href'><i class='fa fa-sort'></i>");
						$column->appendContent('</a>');
					}
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function renderAttributes() {
		$attrs = '';
		if(is_array($this->getAttributes())) {
			foreach ($this->getAttributes() as $key => $value) {
				$attrs .= " $key='$value'";
			}
		}
		return $attrs;
	}

	/**
	 * @return string
	 */
	public function render()
	{
		$resource = $this->getOption('resource');
		$privilege = $this->getOption('privilege');
		$acl = $this->getDataGrid()->getAcl();
		$role = $this->getDataGrid()->getRole();
		if($acl && $role && $resource && $privilege) {
			if(!$acl->isAllowed($role, $resource, $privilege)) {
				$this->addAttribute('class', 'hide');
				$attrs = $this->renderAttributes();
				return "<tr$attrs></tr>";
			}
		}

		$attrs = $this->renderAttributes();
		$content = "<tr$attrs>";
		if(is_array($this->getColumns())) {
			foreach($this->getColumns() as $column) {
				/* @var $column Column */
				$content .= $column->render();
			}
		}
		$content .= '</tr>';
		return $content;
	}
}