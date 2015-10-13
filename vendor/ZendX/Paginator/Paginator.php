<?php
/**
 * @author 		VanCK
 * @category   	ZendX library
 * @copyright  	http://nhanh.vn
 * @license    	http://nhanh.vn/license
 */

namespace ZendX\Paginator;
use Zend\Paginator\Paginator as ZendPaginator;

class Paginator extends ZendPaginator
{
	/**
	 * @var array
	 */
	protected $currentModels;

	/**
	 * @author VanCK
	 * @return array
	 */
	public function getCurrentModels()
	{
		if(!$this->currentModels) {
			foreach($this as $item) {
				$this->currentModels[] = $item;
			}
		}
		return $this->currentModels;
	}

	/**
	 * @param array $currentModels
	 * @return \ZendX\Paginator\Paginator
	 */
	public function setCurrentModels($currentModels)
	{
		$this->currentModels = $currentModels;
		return $this;
	}
}