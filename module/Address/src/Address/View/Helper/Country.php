<?php
/**
 * @Address   	Shop99 library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class Country
 * @package Address\View\Helper
 */
class Country extends AbstractHelper {

	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
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
     * @param $serviceLocator
     */
    function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

	public function __invoke() {
		return $this;
	}
	public function get($id){
		$mapper = $this->getServiceLocator()->get('Address\Model\CountryMapper');
		return $mapper->get($id);
	}
	
}










