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

class DomainName extends AbstractFilter{
	public function filter($value){
		// Do not filter non-string values
		if (!is_string($value)) {
			return $value;
		}
		$filteredValue = $value;
		if(!preg_match('/^((http:\/\/)|(https:\/\/))/', $filteredValue)){
			$filteredValue = 'http://'.$filteredValue;
		}
		$filteredValue = parse_url($filteredValue,PHP_URL_HOST);
		if(preg_match('/^(www\.)/', $filteredValue)){
			$filteredValue = str_replace('www.', '', $filteredValue);
		}
		return $filteredValue?:null;

	}
}