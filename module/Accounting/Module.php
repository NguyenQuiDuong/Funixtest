<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Accounting;

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
                'Accounting\Model\AccountMapper' => 'Accounting\Model\AccountMapper',
                'Accounting\Model\ExpenseCategoryMapper' => 'Accounting\Model\ExpenseCategoryMapper',
                'Accounting\Model\TransactionMapper' => 'Accounting\Model\TransactionMapper',
                'Accounting\Model\Transaction\ItemMapper' => 'Accounting\Model\Transaction\ItemMapper'
            ),
            'factories' => array (
                'AccountingNavigation' => 'Accounting\Navigation\Service\AccountingFactory',
            )
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
//                 'city' => 'Address\View\Helper\CityFactory',
            ),
        );
    }
}