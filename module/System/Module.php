<?php
/**
 * @category       Nhanh.vn library
 * @copyright      http://nhanh.vn
 * @license        http://nhanh.vn/license
 */

namespace System;

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
    			'System\Model\Module'                    => 'System\Model\Module',
    			'System\Model\ModuleMapper'              => 'System\Model\ModuleMapper',
    			'System\Model\Controller'                => 'System\Model\Controller',
    			'System\Model\ControllerMapper'          => 'System\Model\ControllerMapper',
    			'System\Model\Action'                    => 'System\Model\Action',
    			'System\Model\ActionMapper'              => 'System\Model\ActionMapper',
    			'System\Model\Action\Dependency'		 => 'System\Model\Action\Dependency',
    			'System\Model\Action\DependencyMapper'	 => 'System\Model\Action\DependencyMapper',
    			'System\Model\RoleMapper'             	 => 'System\Model\RoleMapper',
    			'System\Model\Role\FeatureMapper'        => 'System\Model\Role\FeatureMapper',
    		),
    		'factories'  => array(
				'systemNavigation' => 'System\Navigation\Service\SystemFactory'
    		),
    	);
    }
}