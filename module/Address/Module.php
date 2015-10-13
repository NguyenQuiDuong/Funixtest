<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address;

class Module
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'Address\Model\Address' => 'Address\Model\Address',
                'Address\Model\AddressMapper' => 'Address\Model\AddressMapper',
                'Address\Model\City' => 'Address\Model\City',
                'Address\Model\CityMapper' => 'Address\Model\CityMapper',
                'Address\Model\District' => 'Address\Model\District',
                'Address\Model\DistrictMapper' => 'Address\Model\DistrictMapper',
                'Address\Model\BookMapper' => 'Address\Model\BookMapper',
                'Address\Model\Book' => 'Address\Model\Book',
                'Address\View\Helper\CityFactory' => 'Address\View\Helper\CityFactory',
                'Address\View\Helper\DistrictFactory' => 'Address\View\Helper\DistrictFactory',
                'Address\View\Helper\BookFactory' => 'Address\View\Helper\BookFactory',
                'Address\Form\Book' => 'Address\Form\Book',
                'Address\Form\BookFilter' => 'Address\Form\BookFilter',
            	'Address\Model\Country' => 'Address\Model\Country',
            	'Address\Model\CountryMapper' => 'Address\Model\CountryMapper',
            )
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'city' => 'Address\View\Helper\CityFactory',
                'district' => 'Address\View\Helper\DistrictFactory',
                'book' => 'Address\View\Helper\BookFactory',
            	'country' => 'Address\View\Helper\CountryFactory'
            ),
        );
    }
}