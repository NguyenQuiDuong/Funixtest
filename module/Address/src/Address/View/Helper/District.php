<?php
/**
 * Address\View\Helper\District
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorInterface;

class District extends AbstractHelper {

	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

    /**
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
		return $this->serviceLocator;
	}

    /**
     * @param $serviceLocator
     */
    public function setServiceLocator($serviceLocator) {
		$this->serviceLocator = $serviceLocator;
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

    /**
     * @param int $cityId
     * @return array|null
     */
    public function loadDistrict($cityId) {
		/* @var $dtMapper \Address\Model\DistrictMapper */
		$dtMapper = $this->getServiceLocator()->get('Address\Model\DistrictMapper');
		$dt = new \Address\Model\District();
		$dt->setCityId($cityId);
		return $dtMapper->fetchAll($dt);
	}

	/**
	 * @param array $ditricts
	 * @param int|null $selectedId
	 * @return string
	 */
	public function toSelectBox($ditricts, $selectedId = null){
		$string = '';
		foreach($ditricts as $dt){
			/* @var $dt \Address\Model\District */
			$string .= '<option value="'. $dt->getId() .'" ';
			if($selectedId && $selectedId == $dt->getId()) {
				$string .= 'selected="selected"';
			}
			$string .= '>' . $dt->getName() . '</option>';
		}
		return $string;
	}
}