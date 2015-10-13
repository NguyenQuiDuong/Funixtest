<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Home\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Cache\StorageFactory;

class CacheFactory implements FactoryInterface
{
	/**
	 * @author VanCK
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return \Zend\Cache\StorageFactory
	 */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
	    $cache = StorageFactory::factory(array(
// 	        'adapter' => array(
// 	            'name' => 'memcached',
// 	            'options' => array(
// 	                'servers' => [
// 	            		['host' => '127.0.0.1', 'port' => '11211']
// 	            	]
// 	            ),
// 	        ),
	        'adapter' => array(
	            'name' => 'filesystem',
	            'options' => array(
	                'cache_dir' => BASE_PATH . '/data/cache',
	                'ttl' => 100
	            ),
	        ),
	        'plugins' => array(
	            array(
	                'name' => 'serializer',
	                'options' => []
	            )
	        )
	    ));

	    \Zend\Paginator\Paginator::setCache($cache);
	    return $cache;
    }
}