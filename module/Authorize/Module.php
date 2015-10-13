<?php

namespace Authorize;
use Zend\Mvc\MvcEvent;

class Module
{

	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}

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

    public function getServiceConfig()
    {
        return array(
			'invokables' => array(
				'Authorize\Permission\Acl' => 'Authorize\Permission\Acl',
			),
        	'factories' => array(
        		'Authorize\Service\Authorize' => 'Authorize\Service\AuthorizeFactory',
        		'Authorize\Guard\Controller' => 'Authorize\Guard\ControllerFactory',
        		'Authorize\View\UnauthorizedStrategy' => 'Authorize\View\Strategy\UnauthorizedStrategyFactory'
        	)
        );
    }

	public function onBootstrap(MvcEvent $e)
	{
		$sm = $e->getApplication()->getServiceManager();

		/* @var $eventManager \Zend\EventManager\EventManager */
		$eventManager = $e->getApplication()->getEventManager();
		$eventManager->attach($sm->get('Authorize\Guard\Controller'));
		$eventManager->attach($sm->get('Authorize\View\UnauthorizedStrategy'));

		/* @var $acl \Authorize\Permission\Acl */
		$acl = $sm->get('Authorize\Permission\Acl');
		/* @var $serviceUser \User\Service\User */
		$serviceUser = $sm->get('User\Service\User');

 		\Zend\View\Helper\Navigation::setDefaultAcl($acl);
 		\Zend\Navigation\Page\Mvc::setDefaultRouter($sm->get('router'));
	}
}