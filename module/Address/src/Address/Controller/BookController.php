<?php
/**
 * Address\Controller
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Controller;

use Address\Model\Book;

use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Address\Model\BookMapper;


class BookController extends  AbstractActionController
{
	public function indexAction()
	{
		$add = new \Address\Model\Book();
		$addMapper = $this->getServiceLocator()->get('Address\Model\BookMapper');

		/* @var $cityMapper \Address\Model\CityMapper */
    	$cityMapper = $this->getServiceLocator()->get('Address\Model\CityMapper');
    	$city = new \Address\Model\City();

    	/* @var $districtMapper \Address\Model\DistrictMapper */
    	$districtMapper = $this->getServiceLocator()->get('Address\Model\DistrictMapper');
    	$district = new \Address\Model\District();

    	$districts = array();
		if(!!($cityId = $this->getRequest()->getPost()->get('cityId'))) {
			$district->setCityId($cityId);
			$districts = $districtMapper->fetchAll($district);
		}

		/* @var $form \User\Form\Signup */
		$form = $this->getServiceLocator()->get('Address\Form\Book');
		$form->setInputFilter($this->getServiceLocator()->get('Address\Form\BookFilter'));
		$form->setCities($city->toSelectBoxArray($cityMapper->fetchAll()));
		$form->setDistricts($district->toSelectBoxArray($districts));
    	$form->bind($add);

		$viewModel = new ViewModel();
        if($this->params()->fromQuery('layout') == 'false')
        {
            $viewModel->setTerminal(true);
        }
    	if($this->getRequest()->isPost()) {
    		$form->setData($this->getRequest()->getPost());
    		$address = new Book();
    		if($form->isValid()) {
    			$address->exchangeArray((array)$this->getRequest()->getPost());
    			$address->setCreatedById($this->user()->getUser()->getId());
				$addMapper->saveAddress($address);
				$this->redirect()->toUrl('/profile');
            }
        }
    	$viewModel->setVariable('form', $form);
		return $viewModel;
	}

	public function removeAction(){
		$addId = $this->getRequest()->getPost()->get('addId');

		$addService = $this->getServiceLocator()->get('Address\Model\BookMapper');
		$addService->removeAddress($addId);
		return JsonModel;
	}

	public function editAction(){

	}
}