<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Model;

use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class Base implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     *
     * @param array $options
     */
    public function __construct($options = null){
    	if($options && is_array($options)){
    		$this->exchangeArray($options);
    	}
    }

    /**
     * @param mixed $key
     * @param mixed $value
     * @return mixed
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
        return $this;
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function addOptions($options)
    {
    	if(is_array($options)) {
    		foreach($options as $key => $value) {
        		$this->options[$key] = $value;
    		}
    	}
    	return $this;
    }

    /**
     * @param string $key
     * @param null $defaultValue
     * @return null
     */
    public function getOption($key, $defaultValue = null)
    {
        return isset($this->options[$key]) ? $this->options[$key] : $defaultValue;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Overloading: allow property access
     *
     * @param string $name
     * @param mixed $value
     * @return mixed|void
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $method = 'set' . ucfirst($name);
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        $this->$method($value);
    }

    /**
     * Overloading: allow property access
     *
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        $method = 'get' . ucfirst($name);
        if ('mapper' == $name || !method_exists($this, $method)) {
            throw new Exception('Invalid property specified');
        }
        return $this->$method();
    }

    /**
     * extract object to array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * populate properties from array
     * @param array $data
     * @return $this
     */
    public function exchangeArray($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $method = 'set' . ucfirst($key);
                if (in_array($method, get_class_methods($this))) {
                    $this->$method($value);
                }
            }
        }
        return $this;
    }

    /**
     * @param array $items
     * @return array (id => name)
     */
    public function toSelectBoxArray($items)
    {
        if (is_array($items) && count($items)) {
            $result = array();
            /* @var Object $item */
            foreach ($items as $item) {
                $result[$item->getId()] = $item->getName();
            }
            return $result;
        }
        return array();
    }

    /**
     * @author KienNN
     * @param Array $items
     * @return array (id )
     */
    public function toIds($items){
    	if($items && is_array($items) && count($items)){
    		$result = [];
    		foreach ($items as $item){
    			/* @var Object $item */
    			$result[] = $item->getId();
    		}
    		return $result;
    	}
    	return null;
    }

    /**
     * @author AnhNV
     * @param Array $items
     * @return array option
     */
    public function selectBoxFromOptions($items, $option = null){
    	if($items && is_array($items) && count($items)){
    		$result = [];
    		foreach ($items as $item){
    			/* @var Object $item */
    			$result[$item->getUserId()] = $item->getOption($option);
    		}
    		return $result;
    	}
    	return null;
    }
}