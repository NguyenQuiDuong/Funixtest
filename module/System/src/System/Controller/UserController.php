<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Controller;

use Home\Controller\ControllerBase;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use User\Model\User;
use Home\Model\DateBase;

class UserController extends ControllerBase{

	public function indexAction(){
		$fFilter = new \System\Form\User\Filter($this->getServiceLocator());
		$fFilter->setData($this->params()->fromQuery());
		$this->getViewModel()->setVariable('fFilter', $fFilter);

		if($fFilter->isValid()) {
			$user = new User();
			$user->exchangeArray($fFilter->getData());
			if($this->params()->fromQuery('companyId')){
				$user->addOption('companyId',$this->params()->fromQuery('companyId'));
			}
			$user->addOption('companyIds', $this->company()->getManageabaleIds());
			if ($this->params()->fromQuery('roleCompany')){
			    $user->addOption('roleCompany', $this->params()->fromQuery('roleCompany'));
			}
			if ($this->params()->fromQuery('hasPrivateRole')){
			    $user->addOption('hasPrivateRole', $this->params()->fromQuery('hasPrivateRole'));
			}
			if ($this->params()->fromQuery('departmentId')){
			    $user->addOption('departmentId', $this->params()->fromQuery('departmentId'));
			}
			$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
			$paginator = $userMapper->search($user, $this->getPagingParams(null, 50));
			$this->getViewModel()->setVariable('paginator', $paginator);
		}
		return $this->getViewModel();
	}

	public function changeactiveuserAction(){
		$id = $this->getRequest()->getPost('id');
		$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
		$jsonModel = New JsonModel();
		if(!$id){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Dữ liệu không hợp lệ']
					]);
			return $jsonModel;
		}
		$user = $userMapper->get($id);
		if(!$user){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Không tìm thấy user']
					]);
			return $jsonModel;
		}
		if($user->getActive()){
			$user->setActive(null);
		} else {
			$user->setActive(1);
		}
		$userMapper->save($user);
		$jsonModel->setVariables(['code' => 1]);
		return $jsonModel;
	}

	public function lockuserAction(){
		$id = $this->getRequest()->getPost('id');
		$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
		$jsonModel = New JsonModel();
		if(!$id){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Dữ liệu không hợp lệ']
					]);
			return $jsonModel;
		}
		$user = $userMapper->get($id);
		if(!$user){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Không tìm thấy user']
					]);
			return $jsonModel;
		}
		if($user->getLocked()){
			$user->setLocked(null);
		} else {
			$user->setLocked(1);
		}
		$userMapper->save($user);
		$jsonModel->setVariables(['code' => 1]);
		return $jsonModel;
	}

	public function addAction(){
		/** @var \Zend\Http\Request $request */
		$request = $this->getRequest();
		$sl = $this->getServiceLocator();

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

		$form = new \System\Form\User\Add($this->getServiceLocator());
		$form->setCities($city->toSelectBoxArray($cityMapper->fetchAll()));
		$form->setDistricts($district->toSelectBoxArray($districts));

		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();

			$form->setData($data);

			if($form->isValid()){
				$user = new User();
				$user->exchangeArray($form->getData());
				$user->setSalt($user->generateSalt());
				$user->setPassword($user->createPassword());
				if(!$user->getRole()){
					$user->setRole(User::ROLE_GUEST);
				}
				if($user->getBirthdate()){
					$user->setBirthdate(DateBase::toCommonDate($user->getBirthdate()));
				}
				$user->setActive(1);
				$user->setCreatedById($this->user()->getIdentity());
				$user->setCreatedDate(DateBase::getCurrentDate());
				$user->setCreatedDateTime(DateBase::getCurrentDateTime());
				$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
				$userMapper->save($user);

				if($form->get('afterSubmit')->getValue()) {
					return $this->redirect()->toUrl($form->get('afterSubmit')->getValue());
				}
			}
		}
		$viewModel = new ViewModel();
		$viewModel->setVariable('form', $form);
		return $viewModel;
	}

	public function editAction(){
		$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');

		/** @var \Zend\Http\Request $request */
		$request = $this->getRequest();
		$sl = $this->getServiceLocator();
		$id = $this->params()->fromQuery('id');
		if(!$id || !($user = $userMapper->get($id))){
			return $this->page404();
		}

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
		} elseif ($user->getCityId()){
			$district->setCityId($user->getCityId());
			$districts = $districtMapper->fetchAll($district);
		}

		$form = new \System\Form\User\Edit($this->getServiceLocator());
		$form->setCities($city->toSelectBoxArray($cityMapper->fetchAll()));
		$form->setDistricts($district->toSelectBoxArray($districts));
		$form->populateValues($user->toFormValues());

		if($this->getRequest()->isPost()){

			$form->setData($this->getRequest()->getPost());

			if($form->isValid()){
				$data = $form->getData();
				$user->exchangeArray($form->getData());
				if(isset($data['password']) && $data['password']){
					$user->setSalt($user->generateSalt());
					$user->setPassword($user->createPassword());
				}
				if(!$user->getRole()){
					$user->setRole(User::ROLE_GUEST);
				}

				$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
				$userMapper->save($user);
				return $this->redirect()->toUrl('/system/user/index?id='.$user->getId());

			}
		}
		$viewModel = new ViewModel();
		$viewModel->setVariable('form', $form);
		return $viewModel;
	}

	/**
	 * @return \Zend\View\Model\JsonModel
	 */
	public function suggestAction(){
		$q = trim($this->getRequest()->getPost('q'));
/* 	   $q =   $this->params()->fromQuery('q');
	  $page =   $this->params()->fromQuery('page');  */
		$jsonModel = New JsonModel();
		if(!$q){
			$jsonModel->setVariables([
					'code' => 1,
					'data' => []
					]);
			return $jsonModel;
		}
		$user = new User();
		$user->setUsername($q);
/** @var  $userMapper \User\Model\UserMapper */
		$userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
		$jsonModel->setVariables([
				'code' => 1,
				'data' => $userMapper->suggest($user),
				]);
		return $jsonModel;
	}
	

    /**
     * @return JsonModel
     */
    public function getnameAction()
    {
        if ($this->getRequest()->isPost()) {
            $userId = $this->getRequest()->getPost('userId');
            $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
            /* @var $user \User\Model\User */
            $user = new User();
            $user->setId($userId);
            if ($userMapper->getUser($user)){
            $json = new JsonModel();
            $json->setVariables([
                'code' => 1,
                'id'   => $user->getId(),
                'name' => $user->getUsername() . ' - ' . $user->getFullName(),
                'fullName' => $user->getFullName(),
            ]);
            return $json;
            }else{
                $json = new JsonModel();
                $json->setVariables([
                    'code' => 0,
                    ]);
                return $json;
            }
        }
    }
}