<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Address\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BookFactory implements FactoryInterface
{
    /**
     * @author VanCK
     * @param ServiceLocatorInterface $hpm
     * @return \Address\View\Helper\Book
     */
    public function createService(ServiceLocatorInterface $hpm)
    {
        /* @var $hpm \Zend\View\HelperPluginManager */
        $helper = new Book($hpm->getServiceLocator());
        return $helper;
    }
}