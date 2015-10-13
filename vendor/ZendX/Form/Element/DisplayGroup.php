<?php

/**
 * @author 		VanCK
 * @category   	ZendX library
 * @copyright  	http://nhanh.vn
 * @license    	http://nhanh.vn/license
 */
namespace ZendX\Form\Element;

use Zend\Form\Element;

class DisplayGroup extends Element
{
	/**
	 * @var array
	 */
	protected $elements;

	/**
	 * @param string $name
	 */
	public function __construct($name = null) {
		parent::__construct($name);
	}

	/**
	 * @return array $elements
	 */
	public function getElements() {
		return $this->elements;
	}

	/**
	 * @param array $elements
	 */
	public function setElements($elements) {
		$this->elements = $elements;
	}

	/**
	 * @param Element $element
	 */
	public function addElement($element)
	{
		$options = $element->getOptions();
		$options['displayGroup'] = $this->getName();
		$element->setOptions($options);

		$this->elements[$element->getName()] = $element;
		return $this;
	}
}