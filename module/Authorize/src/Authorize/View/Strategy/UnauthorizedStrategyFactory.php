<?php
/**
 * @author 		KIenNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Authorize\View\Strategy;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UnauthorizedStrategyFactory implements FactoryInterface{
	/**
	 * @param ServiceLocatorInterface $sl
	 * @return \Authorize\Service\Authorize
	 */
	public function createService(ServiceLocatorInterface $sl)
	{
		return new \Authorize\View\Strategy\UnauthorizedStrategy();
	}
}