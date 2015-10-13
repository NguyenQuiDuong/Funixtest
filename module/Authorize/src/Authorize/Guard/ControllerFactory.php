<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Authorize\Guard;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ControllerFactory implements FactoryInterface{
	/**
	 * @param ServiceLocatorInterface $sl
	 * @return \Authorize\Service\Authorize
	 */
	public function createService(ServiceLocatorInterface $sl){
		$guardController = new Controller;
		/* @var $authorizeService \Authorize\Service\Authorize */
		$authorizeService = $sl->get('Authorize\Service\Authorize');
		$guardController->setAuthorizeService($authorizeService);
		return $guardController;
	}
}