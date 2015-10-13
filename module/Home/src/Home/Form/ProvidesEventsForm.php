<?php

namespace Home\Form;

use Zend\Form\Form;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ProvidesEventsForm extends Form implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var EventManagerInterface
     */
    protected $events;

    /**
     * Set the event manager instance used by this context
     *
     * @param  EventManagerInterface $events
     * @return mixed
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $this->events = $events;
        return $this;
    }

    /**
     * Retrieve the event manager
     *
     * Lazy-loads an EventManager instance if none registered.
     *
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (!$this->events instanceof EventManagerInterface) {
            $identifiers = array(__CLASS__, get_called_class());
            if (isset($this->eventIdentifier)) {
                if ((is_string($this->eventIdentifier))
                    || (is_array($this->eventIdentifier))
                    || ($this->eventIdentifier instanceof Traversable)
                ) {
                    $identifiers = array_unique($identifiers + (array) $this->eventIdentifier);
                } elseif (is_object($this->eventIdentifier)) {
                    $identifiers[] = $this->eventIdentifier;
                }
                // silently ignore invalid eventIdentifier types
            }
            $this->setEventManager(new EventManager($identifiers));
        }
        return $this->events;
    }

    /**
     * load cities for select box
     * @param $element \Zend\Form\Element|string instance of Zend\Form\Element
     * 				or element name
     * @param $options array
     */
    public function loadCities($element, $options = null)
    {
		/* @var $cityMapper \Address\Model\CityMapper */
		$cityMapper = $this->getServiceLocator()->get('Address\Model\CityMapper');
		$cities = $cityMapper->fetchAll();

    	$arr = ['' => '- Thành phố -'];
    	if(is_array($cities)) {
    		foreach ($cities as $city) {
    			/* @var $city \Address\Model\City */
    			$arr[$city->getId()] = $city->getName();
    		}
    	}
    	if($element instanceof \Zend\Form\Element) {
    		$element->setValueOptions($arr);
    	} else {
    		$this->get($element)->setValueOptions($arr);
    	}
    	return $this;
    }

    /**
     * load districts for select box
     * @param $element \Zend\Form\Element|string instance of Zend\Form\Element
     * 				or element name
     * @param $options array
     */
    public function loadDistricts($element, $options = null)
    {
    	if(!($cityId = isset($options['cityId']) ? $options['cityId'] : null)) {
    		return;
    	}

		/* @var $districtMapper \Address\Model\DistrictMapper */
		$districtMapper = $this->getServiceLocator()->get('Address\Model\DistrictMapper');
		$districts = $districtMapper->fetchAll();

    	$arr = ['' => '- Quận huyện -'];
    	if(is_array($districts)) {
    		foreach ($districts as $district) {
    			/* @var $district \Address\Model\District */
    			$arr[$district->getId()] = $district->getName();
    		}
    	}
    	if($element instanceof \Zend\Form\Element) {
    		$element->setValueOptions($arr);
    	} else {
    		$this->get($element)->setValueOptions($arr);
    	}
    	return $this;
    }

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
}
