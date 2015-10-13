<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Home\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DbAdapterFactory implements FactoryInterface
{
	/**
	 * @author VanCK
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return \Home\Service\Store
	 */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
		$adapterFactory = new \Zend\Db\Adapter\AdapterServiceFactory();
		$adapter = $adapterFactory->createService($serviceLocator);

		$config = $serviceLocator->get('Config');
		if(isset($config['db']['profilerEnabled']) && $config['db']['profilerEnabled']) {
		    $adapter->setProfiler(new \Zend\Db\Adapter\Profiler\Profiler());
		}

		\Zend\Db\TableGateway\Feature\GlobalAdapterFeature::setStaticAdapter($adapter);
		return $adapter;
    }
}
