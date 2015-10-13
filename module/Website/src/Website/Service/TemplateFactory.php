<?php
/**
 * @category   	Shop99 library
 * @copyright  	http://shop99.vn
 * @license    	http://shop99.vn/license
 */

namespace Website\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TemplateFactory implements FactoryInterface
{
	/**
	 * @author VanCK
	 * @param ServiceLocatorInterface $sl
	 * @return \Website\Service\Template
	 */
    public function createService(ServiceLocatorInterface $sl)
    {
		$templateService = new Template();
		$templateService->setServiceLocator($sl);

        return $templateService;
    }
}