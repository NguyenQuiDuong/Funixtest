<?php
/**
 * Address\Controller
 *
 * @category    Shop99 library
 * @copyright    http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace Address\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

class AddressController extends AbstractActionController
{
    public function indexAction()
    {

    }

    /*
     * @author Chautm:
     * Gets all address book when user login
     * */
    public function bookAction()
    {
        $addr = new \Address\Model\Book();

        /* @var $addressMapper \Address\Model\BookMapper */
        $addressMapper = $this->getServiceLocator()->get('Address\Model\BookMapper');
        /* @var $userService \User\Service\User */
        $userService = $this->getServiceLocator()->get('User\Service\User');

        /* @var $user \User\Model\User */
        $user = $userService->getUser();
        if ($user) {
            $addr->setCreatedById($user->getId());
        }
        $addrs = $addressMapper->searchAddress($addr);

        $viewModel = new ViewModel();
        $viewModel->setVariables([
            'addrs' => $addrs,
            'user' => $user
        ]);
        return $viewModel;
    }

    public function addbookAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $addressBk = new \Address\Model\Book();

        $sl = $this->getServiceLocator();
        /* @var $addMapper \Address\Model\BookMapper */
        $addMapper = $sl->get('Address\Model\BookMapper');

        /* @var $cityMapper \Address\Model\CityMapper */
        $cityMapper = $sl->get('Address\Model\CityMapper');
        $city = new \Address\Model\City();

        /* @var $districtMapper \Address\Model\DistrictMapper */
        $districtMapper = $sl->get('Address\Model\DistrictMapper');
        $district = new \Address\Model\District();

        $districts = array();
        if (!!($cityId = $request->getPost('cityId'))) {
            $district->setCityId($cityId);
            $districts = $districtMapper->fetchAll($district);
        }

        /* @var $form \Address\Form\Book */
        $form = $sl->get('Address\Form\Book');
        $form->setCities($city->toSelectBoxArray($cityMapper->fetchAll()));
        $form->setDistricts($district->toSelectBoxArray($districts));
        $form->setInputFilter($sl->get('Address\Form\BookFilter'));
        $form->bind($addressBk);

        $viewModel = new ViewModel();
        if ($this->params()->fromQuery('layout') == 'false') {
            $viewModel->setTerminal(true);
        }

        if ($request->isPost()) {
            $form->setData($request->getPost());
            $address = new \Address\Model\Book();
            if ($form->isValid()) {
                $address->exchangeArray((array)$request->getPost());
                $address->setCreatedById($this->user()->getUser()->getId());
                $addMapper->saveAddress($address);
                $this->redirect()->toUrl('/address/book');
            }
        }

        $viewModel->setVariable('form', $form);
        return $viewModel;
    }
    
    public function ajaxaddbookAction()
    {
    	/* @var $request \Zend\Http\Request */
    	$request = $this->getRequest();
        $sl = $this->getServiceLocator();
        
    	$valEmail = new \Zend\Validator\EmailAddress();
    	$valRequired = new \Zend\Validator\NotEmpty();
    	$valMobile = new \Zend\Validator\Regex("/^[0-9]+$/");
    	
    	if (!$valRequired->isValid($request->getPost('name'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('name' => 'Họ tên không được để trống')));
    	}
    	if (!$valRequired->isValid($request->getPost('mobile'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('mobile' => 'Điện thoại không được để trống')));
    	}
    	if (!$valMobile->isValid($request->getPost('mobile'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('mobile' => 'Điện thoại không hợp lệ')));
    	}
    	if (!$valRequired->isValid($request->getPost('email'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('email' => 'Email không được để trống')));
    	}
    	if (!$valEmail->isValid($request->getPost('email'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('email' => 'Email không hợp lệ')));
    	}
    	if (!$valRequired->isValid($request->getPost('cityId'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('cityId' => 'Thành phố không được để trống')));
    	}
    	if (!$valRequired->isValid($request->getPost('districtId'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('districtId' => 'Quận huyện không được để trống')));
    	}
    	if (!$valRequired->isValid($request->getPost('address'))) {
    		return new JsonModel(array('code' => 0, 'messages' => array('address' => 'Địa chỉ không được để trống')));
    	}

    	$address = new \Address\Model\Book();
        $address->exchangeArray((array)$request->getPost());
        $address->setCreatedById($this->user()->getUser()->getId());
        /* @var $addMapper \Address\Model\BookMapper */
        $addMapper = $sl->get('Address\Model\BookMapper');
        $addMapper->saveAddress($address);

        if ($request->getPost("id")) { //If this action is used to edit a book
        	return new JsonModel(['code' => 0]);
        } else {
	        //Get the address that was just entered to DB
	        $newaddr = $addMapper->searchAddress($address)[0];
	        
	        return new JsonModel([
	        	'code' => 1,
	        	'address' => [
                    'id' => $newaddr->getId(),
		        	'name' => $newaddr->getName(),
		        	'email' => $newaddr->getEmail(),
		        	'mobile' => $newaddr->getMobile(),
		        	'address' => $newaddr->getAddress(),
		        	'cityId' => $newaddr->getCityId(),
		        	'cityName' => $newaddr->getCityName(),
		        	'districtId' => $newaddr->getDistrictId(),
		        	'districtName' => $newaddr->getDistrictName(),
                ]
	        ]);
        }
    }

    public function removebookAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $addrId = (int)$request->getPost("id");

        /* @var $addrbkMapper \Address\Model\BookMapper */
        $addrbkMapper = $this->getServiceLocator()->get('Address\Model\BookMapper');

        if (!$addrbkMapper->removeAddress($addrId)) {
            return new JsonModel(array(
                'code' => 0,
                'message' => 'Lỗi!'
            ));
        }

        return new JsonModel(array(
            'code' => 1
        ));
    }

    public function editbookAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $sl = $this->getServiceLocator();

        /* @var $addrbkMapper \Address\Model\BookMapper */
        $addrbkMapper = $sl->get('Address\Model\BookMapper');
        $addressBk = new \Address\Model\Book();
      
        $addressBk->setId((int)$this->params('id'));

        $addressBk = $addrbkMapper->get($addressBk->getId());
        /* @var $cityMapper \Address\Model\CityMapper */

        $cityMapper = $sl->get('Address\Model\CityMapper');
        $city = new \Address\Model\City();

        /* @var $districtMapper \Address\Model\DistrictMapper */
        $districtMapper = $sl->get('Address\Model\DistrictMapper');
        $district = new \Address\Model\District();

        if (!!($cityId = $request->getPost('cityId'))) {
            $district->setCityId($cityId);
        } else {
            $district->setCityId($addressBk->getCityId());
        }
        $districts = $districtMapper->fetchAll($district);

        /* @var $form \Address\Form\Book */
        $form = $sl->get('Address\Form\Book');
        $form->setCities($city->toSelectBoxArray($cityMapper->fetchAll()));
        $form->setDistricts($district->toSelectBoxArray($districts));
        $form->setInputFilter($sl->get('Address\Form\BookFilter'));
        $form->bind($addressBk);

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $addressBk->exchangeArray((array)$request->getPost());
                $addressBk->setCreatedById($this->user()->getUser()->getId());
                $addrbkMapper->saveAddress($addressBk);
            }
        }

        if ($this->params()->fromQuery('format') == 'json') {
            return new JsonModel(array(
                'addr' => $addressBk,
                'code' => 1
            ));
        }

        $viewModel = new ViewModel();
        $viewModel->setVariable('form', $form);
        $viewModel->setVariable('addressBook', $addressBk);
        return $viewModel;
    }
}