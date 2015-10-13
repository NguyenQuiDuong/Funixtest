<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Address\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CountryFactory implements FactoryInterface
{
    /**
     * @author Hoangth
     * @param ServiceLocatorInterface $hpm
     * @return \Address\View\Helper\City
     */
    public function createService(ServiceLocatorInterface $hpm)
    {
        /* @var $hpm \Zend\View\HelperPluginManager */
        $helper = new Country($hpm->getServiceLocator());
        return $helper;
    }
}