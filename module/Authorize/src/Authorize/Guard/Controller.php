<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Authorize\Guard;

use Authorize\Service\Authorize;

use Zend\Mvc\MvcEvent;
use Zend\Http\Request as Request;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\View\Model\ViewModel;
use Zend\Http\Response as HttpResponse;

class Controller implements ListenerAggregateInterface {

	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	/**
	 * @var Authorize
	 */
	protected $authorizeService;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

	/**
	 * @return the $serviceLocator
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator($serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

	/**
	 * @return \Authorize\Service\Authorize
	 */
	public function getAuthorizeService() {
		return $this->authorizeService;
	}

	/**
	 * @param \Authorize\Service\Authorize $authorizeService
	 */
	public function setAuthorizeService($authorizeService) {
		$this->authorizeService = $authorizeService;
		return $this;
	}

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onDispatch'), -1000);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * @param string $namespace
     * @param string $controller
     * @return string
     */
    public function getResourceName($namespace, $controller)
    {
    	$namespaceArr = explode('\\', $namespace);
    	$moduleName = strtolower(array_shift($namespaceArr));
    	$controllerArr = explode('\\', $controller);
    	$controllerName = strtolower(str_replace("Controller", "", array_pop($controllerArr)));
    	return $moduleName .':'. $controllerName;
    }

	/**
	 * Event callback to be triggered on dispatch, causes application error triggering
	 * in case of failed authorization check
	 *
	 * @author VanCK
	 * @param  MvcEvent $event
	 * @return void
	 */
	public function onDispatch(MvcEvent $event)
	{
		$service    = $this->getAuthorizeService();
		/* @var $match \Zend\Mvc\Router\RouteMatch */
		$match      = $event->getRouteMatch();
		$namespace	= $match->getParam('__NAMESPACE__');
		$controller = $match->getParam('controller');
		$action     = $match->getParam('action');

		/* @var $request \Zend\Stdlib\RequestInterface */
		$request    = $event->getRequest();
		$method     = $request instanceof Request ? strtolower($request->getMethod()) : null;

		if ($service->isAllowed($this->getResourceName($namespace, $controller), $action)) {
			return true;
		} else {
			if(!$service->getUserService()->hasIdentity()) {
				$redirectUri = '/user/signin?redirect=' . $request->getRequestUri();
				/* @var $response \Zend\Stdlib\ResponseInterface */
				$response = $event->getResponse();
				$response->setStatusCode(302);
				$response->getHeaders()->addHeaderLine('Location', $redirectUri);
				return $response;
			} else {
				$event->setError('error-unauthorized-controller');
				$event->setParam('identity', $service->getUserService()->getIdentity());
				$event->setParam('controller', $controller);
				$event->setParam('action', $action);

				/* @var $app \Zend\Mvc\ApplicationInterface */
				$app = $event->getTarget();
				$app->getEventManager()->trigger(MvcEvent::EVENT_DISPATCH_ERROR, $event);
			}
		}
	}
}