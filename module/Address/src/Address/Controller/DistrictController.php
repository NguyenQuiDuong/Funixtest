<?php
/**
 * Product\Controller
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class DistrictController extends AbstractActionController
{
	public function loadAction()
	{
		$sl = $this->getServiceLocator();

		$district = new \Address\Model\District();
		$district->setCityId((int)$this->getRequest()->getQuery()->get('cityId'));
		/*@var $districtMapper \Cart\Model\DistrictMapper */
		$districtMapper = $sl->get('Address\Model\DistrictMapper');

		return new JsonModel(
			$district->toSelectBoxArray($districtMapper->fetchAll($district))
		);
	}
}