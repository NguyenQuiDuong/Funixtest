<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Address\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CityFactory implements FactoryInterface
{
    /**
     * @author VanCK
     * @param ServiceLocatorInterface $hpm
     * @return \Address\View\Helper\City
     */
    public function createService(ServiceLocatorInterface $hpm)
    {
        /* @var $hpm \Zend\View\HelperPluginManager */
        $helper = new City($hpm->getServiceLocator());
        return $helper;
    }
}