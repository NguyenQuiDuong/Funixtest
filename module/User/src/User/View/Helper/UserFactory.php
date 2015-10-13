<?php
/**
 * @author VanCK
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace User\View\Helper;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $hpm
     * @return \User\View\Helper\User
     */
    public function createService(ServiceLocatorInterface $hpm)
    {
        /* @var $hpm \Zend\View\HelperPluginManager */
        $viewHelper = new User();
        $viewHelper->setServiceUser($hpm->getServiceLocator()->get('User\Service\User'));
        return $viewHelper;
    }
}