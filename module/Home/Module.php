<?php

namespace Home;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Config\SessionConfig;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Session\SaveHandler\DbTableGateway;
use Zend\Session\SaveHandler\DbTableGatewayOptions;

class Module implements AutoloaderProviderInterface
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
                'Home\Service\DbAdapterFactory' => 'Home\Service\DbAdapterFactory',
                'Home\Service\DbSqlFactory'     => 'Home\Service\DbSqlFactory',
                'Home\Service\CacheFactory'     => 'Home\Service\CacheFactory',
                'Home\Service\LogFactory'       => 'Home\Service\LogFactory',
            	'Home\View\Helper\AppFactory' => 'Home\View\Helper\AppFactory',
                'Home\Model\Tree' => 'Home\Model\Tree',
            ),
            'factories'  => array(
                'Zend\Db\Adapter\Adapter' => 'Home\Service\DbAdapterFactory',
                'Zend\Db\Sql\Sql'         => 'Home\Service\DbSqlFactory',
                'cache'                   => 'Home\Service\CacheFactory',
                'log'                     => 'Home\Service\LogFactory',
            ),
            'aliases'    => array(
                'dbAdapter' => 'Zend\Db\Adapter\Adapter',
                'dbSql'     => 'Zend\Db\Sql\Sql',
            )
        );
    }

    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'htmlPurifier' => 'Home\Controller\Plugin\HTMLPurifier'
            ),
        );
    }

    public function getViewHelperConfig()
    {
        \Zend\Paginator\Paginator::setDefaultScrollingStyle('Sliding');
        \Zend\View\Helper\PaginationControl::setDefaultViewPartial("layout/paginatorItem");
        return array(
            'invokables' => array(
            ),
            'factories'  => array(
            	'App'    => 'Home\View\Helper\AppFactory',
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        /* @var $sm \Zend\ServiceManager\ServiceManager */
        $sm = $e->getApplication()->getServiceManager();
        $config = $sm->get('Config');

        // bootstrap session
        $tableGateway = new TableGateway($config['app']['session.tableName'], $sm->get('dbAdapter'));
        $saveHandler = new DbTableGateway($tableGateway, new DbTableGatewayOptions());
        $sessionConfig = new SessionConfig();
        $sessionConfig->setOptions($config['session']);
        $sessionManager = new SessionManager($sessionConfig);
        $sessionManager->setSaveHandler($saveHandler);
        $sessionManager->start();
        Container::setDefaultManager($sessionManager);

        // translate
        $sm->get('translator');
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        // bootstrap locale
//     	$headers = $app->getRequest()->getHeaders();
//     	Locale::setDefault($config['locale']['default']);
//     	if($headers->has('Accept-Language')) {
//     		$locales = $headers->get('Accept-Language')->getPrioritized();
//     		// Loop through all locales, highest priority first
//     		foreach($locales as $locale) {
//     			if(!!($match = Locale::lookup($config['locale']['supported'], $locale->typeString))) {
//     				// The locale is one of our supported list
//     				Locale::setDefault($match);
//     				break;
//     			}
//     		}
//     		if(!$match) {
//     			// Nothing from the supported list is a match
//     			Locale::setDefault($config['locale']['default']);
//     		}
//     	}

		// switch layout
        $e->getApplication()->getEventManager()->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller      = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config          = $e->getApplication()->getServiceManager()->get('config');

            $routeMatch = $e->getRouteMatch();
            $actionName = strtolower($routeMatch->getParam('action', 'not-found')); // get the action name

            if (isset($config['module_layouts'][$moduleNamespace][$actionName])) {
                $controller->layout($config['module_layouts'][$moduleNamespace][$actionName]);
            } elseif(isset($config['module_layouts'][$moduleNamespace]['default'])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]['default']);
            }
        }, 100);
    }
}