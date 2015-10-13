<?php
/**
 * @author 		VanCK
 * @category   	ZendX library
 * @copyright  	http://nhanh.vn
 * @license    	http://nhanh.vn/license
 */

namespace ZendX\DataGrid;

class Column
{
	/**
	 * @var Row
	 */
	protected $row;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $attributes;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $content;

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return Row
	 */
	public function getRow() {
		return $this->row;
	}

	/**
	 * @param Row $row
	 */
	public function setRow($row) {
		$this->row = $row;
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
	 * @param string $key
	 * @param string $val
	 */
	public function addAttribute($key, $val) {
		$this->attributes[$key] = $val;
	}

	/**
	 * @param string $key
	 * @param string $val
	 */
	public function addAttributeValue($key, $val) {
		if(isset($this->attributes[$key])) {
			$this->attributes[$key] = $this->attributes[$key] .' '. $val;
		} else {
			$this->attributes[$key] = $val;
		}
	}

	/**
	 * @return the $options
	 */
	public function getOptions() {
		return $this->options;
	}

	/**
	 * @param multitype: $options
	 */
	public function setOptions($options) {
		$this->options = $options;
	}

	/**
	 * @return the $options
	 */
	public function getOption($key) {
		return isset( $this->options[$key]) ? $this->options[$key] : null;
	}

	/**
	 * @return the $content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @param string $content
	 */
	public function setContent($content) {
		$this->content = $content;
	}

	/**
	 * @param string $content
	 */
	public function appendContent($content) {
		$this->content .= $content;
	}

	/**
	 * @param string $content
	 */
	public function prependContent($content) {
		$this->content = $content . $this->getContent();
	}

	/**
	 * @param null|array $column
	 * @throws Exception
	 */
	public function __construct($column = null)
	{
		if(!isset($column['name'])) {
			throw new \Exception('Column name is required');
		} else {
			$this->setName($column['name']);
		}
		if(isset($column['content'])) {
			$this->setContent($column['content']);
		}
		if(isset($column['attributes'])) {
			$this->setAttributes($column['attributes']);
		}
		if(isset($column['options'])) {
			$this->setOptions($column['options']);
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
		$acl = $this->getRow()->getDataGrid()->getAcl();
		$role = $this->getRow()->getDataGrid()->getRole();
		if($acl && $role && $resource && $privilege) {
			if(!$acl->isAllowed($role, $resource, $privilege)) {
				$this->addAttribute('class', 'hide');
				$this->setContent('');
			}
		}

        $attrs = $this->renderAttributes();
        if($this->getRow()->getType() == Row::TYPE_HEADER) {
        	return "<th$attrs>". $this->getContent() . '</th>';
        } else {
        	return "<td$attrs>". $this->getContent() . '</td>';
        }
	}
}