<?php
/**
 * Home\Filter\Mobile
 *
 * @author      KienNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Filter;

use Zend\Filter\AbstractFilter;

class Mobile extends AbstractFilter{

	public function filter($value)
	{
		$digitFilter = new \Zend\Filter\Digits();
		$valueFiltered = $digitFilter->filter($value);
		if($valueFiltered){
			$regexValidator = new \Zend\Validator\Regex('/^(084)+/');
			if($regexValidator->isValid($valueFiltered)){
				$valueFiltered = substr_replace($valueFiltered, '0', 0, 3);
			} else {
				$regexValidator->setPattern('/^(84)+/');
				if($regexValidator->isValid($valueFiltered)){
					$valueFiltered = substr_replace($valueFiltered, '0', 0, 2);
				}
			}

			$regexValidator->setPattern('/^[0]/');
			if(!$regexValidator->isValid($valueFiltered)){
				$valueFiltered = '0'. $valueFiltered;

			}
		}

		return $valueFiltered;
	}
}