<?php
/**
 * Address\View\Helper\Book
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Book extends AbstractHelper {

	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

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
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	*/
	public function __construct($serviceLocator) {
		$this->setServiceLocator($serviceLocator);
	}

	public function __invoke() {
		return $this;
	}

	public function getAddress($options){
		$address = new \Address\Model\Book();
		$address->exchangeArray($options);
		/* @var $addressMapper \Address\Model\BookMapper */
		$addressMapper = $this->getServiceLocator()->get('Address\Model\BookMapper');
		return $addressMapper->searchAddress($address);
	}

	public function getAddressById($id){
		/* @var $addressMapper \Address\Model\BookMapper */
		$addressMapper = $this->getServiceLocator()->get('Address\Model\BookMapper');
		return $addressMapper->get($id);
	}

	public function saveAddress($options) {
		$address = new \Address\Model\Book();
		$address->exchangeArray($options);
		/* @var $addressMapper \Address\Model\BookMapper */
		$addressMapper = $this->getServiceLocator()->get('Address\Model\BookMapper');
		return $addressMapper->saveAddress($address);
	}
}