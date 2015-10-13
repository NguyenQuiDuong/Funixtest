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
 * Class City
 * @package Address\View\Helper
 */
class City extends AbstractHelper {

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

    /**
     * @param null $options
     * @return array|null
     */
    public function loadCity($options = null) {
		/* @var $cityMapper \Address\Model\CityMapper */
		$cityMapper = $this->getServiceLocator()->get('Address\Model\CityMapper');
		return $cityMapper->fetchAll();
	}

    /**
     * @param array $citties
     * @param string|null $selectedId
     * @return string
     */
    public function toSelectBox($citties, $selectedId = null){
		$string = '';
        if(count($citties)){
            foreach($citties as $city){
                /* @var $city \Address\Model\City */
                $string .= '<option value="'. $city->getId() .'" ';
                if($selectedId && $selectedId == $city->getId()) {
                    $string .= 'selected="selected"';
                }
                $string .= '>' . $city->getNativeName() . '</option>';
            }
        }
		return $string;
	}
}