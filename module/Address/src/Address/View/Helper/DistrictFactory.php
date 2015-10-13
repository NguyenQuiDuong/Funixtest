<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Address\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DistrictFactory implements FactoryInterface
{
    /**
     * @author VanCK
     * @param ServiceLocatorInterface $hpm
     * @return \Address\View\Helper\District
     */
    public function createService(ServiceLocatorInterface $hpm)
    {
        /* @var $hpm \Zend\View\HelperPluginManager */
        $helper = new District($hpm->getServiceLocator());
        return $helper;
    }
}