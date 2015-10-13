<?php
/**
 * Home\View\Helper\App
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\View\Helper;

use Zend\View\Helper\AbstractHelper;
use \Zend\ServiceManager\ServiceManager;

class App extends AbstractHelper
{

	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	/**
	 * @var \Zend\Http\Request
	 */
	protected $request;

	/**
	 * @var \Home\Filter\HTMLPurifier
	 */
	protected $htmlPurifier;

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param \Zend\ServiceManager\ServiceManager $serviceLocator
     */
    public function setServiceLocator(ServiceManager $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\Http\Request
     */
    public function getRequest()
    {
    	if(!$this->request) {
    		$this->request = $this->getServiceLocator()->get('Request');
    	}
        return $this->request;
    }

    /**
     * @author VanCK
     * @param string $name
     * @return boolean
     */
    public function slHas($name) {
    	return $this->getServiceLocator()->has($name);
    }

    /**
     * @author VanCK
     * @param string $name
     */
    public function slGet($name) {
    	return $this->getServiceLocator()->get($name);
    }

    /**
     * @param string $name
     * @return string
     */
    public function getMcaName($name = 'module')
    {
    	$routeMatch = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch();
        if ($routeMatch) {
        	if($name == 'module' || $name == 'controller') {
        		$mc = explode("\\", $routeMatch->getParam('controller'));
				if($name == 'module') {
					return strtolower($mc[0]);
        		}
				return strtolower($mc[2]);
        	}
            return strtolower($routeMatch->getParam('action', 'index'));
        }
    }

    /**
     * @return array
     */
    public function getQueryParams() {
    	return $this->getRequest()->getQuery()->toArray();
    }

    /**
     * @param string $key
     * @return string|NULL
     */
    public function getQueryParam($key) {
    	$parmas = $this->getRequest()->getQuery()->toArray();
    	if(isset($parmas[$key])) {
    	    return $parmas[$key];
    	}
    	return null;
    }

    /**
     * @author VanCK
     * @param array $params
     */
    public function appendUriParams($params)
    {
		return $this->buildUri($this->getRequest()->getUri(), $params);
    }

    /**
     * @param array $params
     * @param string|null $uri
     * @return mixed|null|string
     */
    public function buildUri($uri, $params = null)
    {
    	$result = urldecode($uri ?: $this->getRequest()->getUri());
// 		$result = str_replace("%2F", "/", $uri ?: $this->getRequest()->getUri());
// 		$result = str_replace("%3A", ":", $result);
// 		$result = str_replace("L%E1%BB%8Dc", "Lọc", $result);

    	// append params to uri
        if(is_array($params)) {
        	foreach ($params as $param => $value) {
        		$paramStartPos = strpos($result, "$param=");
        		// param is in uri, replace the value
				if($paramStartPos !== false) {
					$paramLength = strlen($param);
					$valuleLength = strlen($this->getRequest()->getQuery($param));
					$result = substr_replace($result, $value, $paramStartPos + $paramLength + 1, $valuleLength);
            	} else { // param is not in uri
            		if(strpos($result, '?')) {
            			$result .= "&$param=$value";
					} else {
						$result .= "?$param=$value";
            		}
            	}
        	}
        }
        return $result;
    }


    /**
     * @param string $value
     * @return string
     */
    public function htmlPurifier($value)
    {
    	if(!$this->htmlPurifier) {
    		$this->htmlPurifier = new \Home\Filter\HTMLPurifier();
    	}
    	return $this->htmlPurifier->filter($value);
    }

    /**
     * @author AnhNV
     * return format time for estimateTime
     */
    public function minutesToHm($minutes)
    {
        $min = $minutes%60;
    	if($minutes>=60)
        {
            $hour = floor($minutes/60);
            echo  $hour . " giờ " . $min . " phút";
        }else
        {
            echo $min . ' phút';
        }
    }

    public function getUsers($options = null){
        $mapper = $this->getServiceLocator()->get('User\Model\UserMapper');
        $user = new \User\Model\User();
        if($options['id']){
            $user->setId($options['id']);
        }
        return $mapper->fetchAll($user);
    }
}