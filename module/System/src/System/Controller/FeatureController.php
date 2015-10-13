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
use System\Model\Action;
use Home\Model\DateBase;

class FeatureController extends ControllerBase{
	public function indexAction(){
		return $this->redirect()->toUrl('/system/feature/mca');
	}

	public function mcaAction(){
		$viewModel = new ViewModel();
		$sl = $this->getServiceLocator();
		$action = $sl->get('System\Model\Action');
		$actionMapper = $sl->get('System\Model\ActionMapper');
		$dataSource = $actionMapper->fetchAdminGridView($action);
		$viewModel->setVariable('dataSource', $dataSource);
		return $viewModel;
	}

	public function changestatusAction(){
		$sl = $this->getServiceLocator();
		$mcaRole = $this->getRequest()->getPost('mca_role');
		$id = $this->getRequest()->getPost('id');
		$status = $this->getRequest()->getPost('status');
		$display = $this->getRequest()->getPost('display');

		$jsonModel = New JsonModel();
		if(!$id || !$mcaRole || (!$status && !$display)){
			$jsonModel->setVariables([
				'code' => 0,
				'messages' => ['Dữ liệu không hợp lệ 1']
			]);
			return $jsonModel;
		}
		switch ($mcaRole){
			case 'action':
				$action = new \System\Model\Action();
				$action->setId($id);
				$actionMapper = $sl->get('System\Model\ActionMapper');
				if(!$actionMapper->get($action)){
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Không tìm thấy Action']
							]);
					return $jsonModel;
				}
				if($status){
					$action->setStatus($status);
				}
				if($display){
					$action->setDisplay($display);
				}
				$actionMapper->save($action);
				$jsonModel->setVariables([
						'code' => 1,
				]);
				return $jsonModel;
			case 'controller':
				$controller = new \System\Model\Controller();
				$controller->setId($id);
				$controllerMapper = $sl->get('System\Model\ControllerMapper');
				if(!$controllerMapper->get($controller)){
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Không tìm thấy Controller']
							]);
					return $jsonModel;
				}
				if($status){
					$controller->setStatus($status);
				} else {
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Dữ liệu không hợp lệ']
							]);
					return $jsonModel;
				}
				$controllerMapper->save($controller);
				$jsonModel->setVariables([
						'code' => 1,
						]);
				return $jsonModel;
			case 'module':
				$module = new \System\Model\Module();
				$module->setId($id);
				$moduleMapper = $sl->get('System\Model\ModuleMapper');
				if(!$moduleMapper->get($module)){
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Không tìm thấy Controller']
							]);
					return $jsonModel;
				}
				if($status){
					$module->setStatus($status);
				} else {
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Dữ liệu không hợp lệ']
							]);
					return $jsonModel;
				}
				$moduleMapper->save($module);
				$jsonModel->setVariables([
						'code' => 1,
						]);
				return $jsonModel;
			default:
				$jsonModel->setVariables([
						'code' => 0,
						'messages' => ['Dữ liệu không hợp lệ']
						]);
				return $jsonModel;
		}

	}

	public function updatemcaAction(){
		$request = $this->getRequest();

		$sl = $this->getServiceLocator();
		$mcaRole = $this->getRequest()->getPost('mca_role');
		$id = $this->getRequest()->getPost('id');
		$name = $this->getRequest()->getPost('name');
		$description = $this->getRequest()->getPost('description');

		$jsonModel = New JsonModel();
		if(!$id || !$mcaRole || !$name){
			$jsonModel->setVariables([
				'code' => 0,
				'messages' => ['Dữ liệu không hợp lệ']
			]);
			return $jsonModel;
		}
		switch ($mcaRole){
			case 'action':
				$action = new \System\Model\Action();
				$action->setId($id);
				$actionMapper = $sl->get('System\Model\ActionMapper');
				if(!$actionMapper->get($action)){
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Không tìm thấy Action']
							]);
					return $jsonModel;
				}
				if($name){
					$action->setName($name);
				}
				if($description){
					$action->setDescription($description);
				} else {
					$action->setDescription(null);
				}
				if(!$actionMapper->isExisted($action)){
					$actionMapper->save($action);
					$jsonModel->setVariables([
							'code' => 1,
							]);
				} else {
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Tên action đã tồn tại']
					]);
				}

				return $jsonModel;
			case 'controller':
				$controller = new \System\Model\Controller();
				$controller->setId($id);
				$controllerMapper = $sl->get('System\Model\ControllerMapper');
				if(!$controllerMapper->get($controller)){
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Không tìm thấy Controller']
							]);
					return $jsonModel;
				}
				if($name){
					$controller->setName($name);
				}
				if($description){
					$controller->setDescription($description);
				} else {
					$controller->setDescription(null);
				}
				if(!$controllerMapper->isExisted($controller)){
					$controllerMapper->save($controller);
					$jsonModel->setVariables([
							'code' => 1,
							]);
				} else {
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Tên controller đã tồn tại']
					]);
				}

				return $jsonModel;
			case 'module':
				$module = new \System\Model\Module();
				$module->setId($id);
				$moduleMapper = $sl->get('System\Model\ModuleMapper');
				if(!$moduleMapper->get($module)){
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Không tìm thấy Controller']
							]);
					return $jsonModel;
				}
				if($name){
					$module->setName($name);
				}
				if($description){
					$module->setDescription($description);
				} else {
					$module->setDescription(null);
				}
				if(!$moduleMapper->isExisted($module)){
					$moduleMapper->save($module);
					$jsonModel->setVariables([
							'code' => 1,
							]);
				} else {
					$jsonModel->setVariables([
							'code' => 0,
							'messages' => ['Tên module đã tồn tại']
					]);
				}

				return $jsonModel;
			default:
				$jsonModel->setVariables([
						'code' => 0,
						'messages' => ['Dữ liệu không hợp lệ']
						]);
				return $jsonModel;
		}
	}

	public function loaddependencyAction(){
		$actionId = $this->getRequest()->getPost('actionId');
		$jsonModel = New JsonModel();
		if(!$actionId){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => 'Dữ liệu không hợp lệ'
					]);
			return $jsonModel;
		}
		$dependencyMapper = $this->getServiceLocator()->get('System\Model\Action\DependencyMapper');
		$actions = $dependencyMapper->fetchAllDependency($actionId);
		$result = [];
		if(count($actions)){
			foreach ($actions as $action){
				$result[] = $action->toStdClass();
			}
		}
		$jsonModel->setVariables([
				'code' => 1,
				'data' => $result
				]);
		return $jsonModel;
	}

	public function adddependencyAction(){
		$actionId = $this->getRequest()->getPost('id');
		$dependencies = $this->getRequest()->getPost('dependencies');

		$jsonModel = New JsonModel();
		if(!$actionId){
			$jsonModel->setVariables([
				'code' => 0,
				'messages' => ['Dữ liệu không hợp lệ']
			]);
			return $jsonModel;
		}

		$dependencyMapper = $this->getServiceLocator()->get('System\Model\Action\DependencyMapper');
		$dependencyMapper->delete($actionId);

		if($dependencies && is_array($dependencies) && count($dependencies)){
			$moduleMapper = $this->getServiceLocator()->get('System\Model\ModuleMapper');
			$controllerMapper = $this->getServiceLocator()->get('System\Model\ControllerMapper');
			$actionMapper = $this->getServiceLocator()->get('System\Model\ActionMapper');
			foreach ($dependencies as $actionLink){
				$names = explode('/', $actionLink);
				if(count($names) != 4){
					continue;
				}
				$moduleName = $names[1];
				$controllerName = $names[2];
				$actionName = $names[3];

				$module = new \System\Model\Module();
				$module->setName($moduleName);


				if(!$moduleMapper->get($module)){
					continue;
				}

				$controller = new \System\Model\Controller();
				$controller->setName($controllerName);
				$controller->setModuleId($module->getId());


				if(!$controllerMapper->get($controller)){
					continue;
				}

				$action = new \System\Model\Action();
				$action->setControllerId($controller->getId());
				$action->setName($actionName);


				if(!$actionMapper->get($action)){
					continue;
				}

				$dependency = new \System\Model\Action\Dependency();
				$dependency->setActionId($actionId);
				$dependency->setDependencyId($action->getId());

				if(!$dependencyMapper->isExisted($dependency)){
					$dependencyMapper->save($dependency);
				}
			}
		}
		$jsonModel->setVariables(['code' => 1]);
		return $jsonModel;
	}

	public function suggestactionlinkAction(){
		$uri = $this->getRequest()->getPost('q');
		$jsonModel = New JsonModel();
		if(!$uri){
			$jsonModel->setVariables([]);
			return $jsonModel;
		}
		$names = explode('/', $uri);
		$moduleName = null;
		$controllerName = null;
		$actionName = null;
		switch (count($names)){
			case 1:
				$moduleName = $names[0];
				break;
			case 2:
				$moduleName = $names[1];
				break;
			case 3:
				$moduleName = $names[1];
				$controllerName = $names[2];
				break;
			case 4:
				$moduleName = $names[1];
				$controllerName = $names[2];
				$actionName = $names[3];
				break;
		}
		$moduleMapper = $this->getServiceLocator()->get('System\Model\ModuleMapper');
		$controllerMapper = $this->getServiceLocator()->get('System\Model\ControllerMapper');
		$actionMapper = $this->getServiceLocator()->get('System\Model\ActionMapper');
		$result = [];
		if($actionName !== null){
			$module = new \System\Model\Module();
			$module->setName($moduleName);

			if(!$moduleMapper->get($module)){
				$jsonModel->setVariables([]);
				return $jsonModel;
			}

			$controller = new \System\Model\Controller();
			$controller->setName($controllerName);
			$controller->setModuleId($module->getId());

			if(!$controllerMapper->get($controller)){
				$jsonModel->setVariables([]);
				return $jsonModel;
			}

			$result = $actionMapper->suggestUri($controller->getId(), $actionName);
		} elseif ($controllerName !== null){
			$module = new \System\Model\Module();
			$module->setName($moduleName);

			if(!$moduleMapper->get($module)){
				$jsonModel->setVariables([]);
				return $jsonModel;
			}
			$result = $controllerMapper->suggestUri($module->getId(), $controllerName);
		} elseif ($moduleName !== null){
			$result = $moduleMapper->suggestUri($moduleName);
		}
		$jsonModel->setVariables($result);
		return $jsonModel;
	}

	public function companyAction(){
		$form = new \System\Form\System\Company($this->getServiceLocator());
		$form->setData($this->getRequest()->getQuery());
		$this->getViewModel()->setVariable('form', $form);
		$companyId = $this->getRequest()->getQuery('companyId');
		if(!$companyId){
			$this->getViewModel()->setVariable('errorMsg', 'Bạn phải chọn doanh nghiệp');
			return $this->getViewModel();
		}
		if($form->isValid()){
			$formData = $form->getData();
			$action = new Action();
			$action->setDisplay(Action::DISPLAY_ACTIVE);
			$action->setStatus(Action::STATUS_ACTIVE);
			$action->addOption('controllerStatus', Action::STATUS_ACTIVE);
			$action->addOption('moduleStatus', Action::STATUS_ACTIVE);
			$actionMapper = $this->getServiceLocator()->get('\System\Model\ActionMapper');
			$mcaList = $actionMapper->fetchAdminGridView($action);

			$this->getViewModel()->setVariable('mcaList', $mcaList);

			$companyFeature = new \Company\Model\Feature();
			$companyFeature->setCompanyId($formData['companyId']);
			$companyFeatureMapper = $this->getServiceLocator()->get('\Company\Model\FeatureMapper');
			$companyFeatures = $companyFeatureMapper->fetchCompanyFeature($companyFeature);
			$this->getViewModel()->setVariable('features', $companyFeatures);
			$this->getViewModel()->setVariable('companyId', $formData['companyId']);

			if($formData['compareCompanyId']){
				$compareFeature = new \Company\Model\Feature();
				$compareFeature->setCompanyId($formData['compareCompanyId']);
				$compareFeatures = $companyFeatureMapper->fetchCompanyFeature($compareFeature);
				$this->getViewModel()->setVariable('compareFeatures', $compareFeatures);

				$company = new \Company\Model\Company();
				$company->setId($formData['compareCompanyId']);
				$companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
				$companyMapper->get($company);
				$this->getViewModel()->setVariable('compareTitle', $company->getName());
			}
		}
		return $this->getViewModel();
	}

	public function roleAction(){
		$action = new Action();
		$action->setDisplay(Action::DISPLAY_ACTIVE);
		$action->setStatus(Action::STATUS_ACTIVE);
		$action->addOption('controllerStatus', Action::STATUS_ACTIVE);
		$action->addOption('moduleStatus', Action::STATUS_ACTIVE);
		$actionMapper = $this->getServiceLocator()->get('\System\Model\ActionMapper');
		$mcaList = $actionMapper->fetchAdminGridView($action);

		$role = new \System\Model\Role();
		$roleMapper = $this->getServiceLocator()->get('\System\Model\RoleMapper');
		$roles = $roleMapper->fetchAll($role);

		$roleFeature = new \System\Model\Role\Feature();
		$roleFeatureMapper = $this->getServiceLocator()->get('\System\Model\Role\FeatureMapper');
		$features = $roleFeatureMapper->fetchArrayMode($roleFeature);

		$this->getViewModel()->setVariable('mcaList', $mcaList);
		$this->getViewModel()->setVariable('roles', $roles);
		$this->getViewModel()->setVariable('features', $features);
		return $this->getViewModel();
	}

	public function updaterolefeatureAction(){
		$actionId = $this->getRequest()->getPost('actionId');
		$roleId = $this->getRequest()->getPost('roleId');
		$value = $this->getRequest()->getPost('value');
		$jsonModel = new JsonModel();

		if(in_array($roleId, array(
			\User\Model\User::ROLE_ADMIN,
			\User\Model\User::ROLE_SUPERADMIN,
			\User\Model\User::ROLE_MEMBER,
			\User\Model\User::ROLE_GUEST,
		))){
			$jsonModel->setVariables(array(
				'code' => 0,
				'messages' => ['Không thể điều chỉnh quyền này của nhóm người dùng này']
			));
			return $jsonModel;
		}
		$role = new \System\Model\Role();
		$role->setId($roleId);
		$roleMapper = $this->getServiceLocator()->get('\System\Model\RoleMapper');
		if(!$roleMapper->get($role)){
			$jsonModel->setVariables(array(
				'code' => 0,
				'messages' => ['Không tìm thấy quyền này']
			));
			return $jsonModel;
		}

		$action = new Action();
		$action->setId($actionId);
		$actionMapper = $this->getServiceLocator()->get('\System\Model\ActionMapper');
		if(!$actionMapper->get($action)){
			$jsonModel->setVariables(array(
				'code' => 0,
				'messages' => ['Không tìm thấy action này']
			));
			return $jsonModel;
		}

		$roleFeature = new \System\Model\Role\Feature();
		$roleFeature->setActionId($actionId);
		$roleFeature->setRoleId($roleId);
		$roleFeatureMapper = $this->getServiceLocator()->get('\System\Model\Role\FeatureMapper');

		if($value){
			if(!$roleFeatureMapper->isExisted($roleFeature)){
				$roleFeature->setCreatedById($this->user()->getIdentity());
				$roleFeature->setCreatedDateTime(DateBase::getCurrentDateTime());
				$roleFeatureMapper->save($roleFeature);
			}
		} else {
			if($roleFeatureMapper->isExisted($roleFeature)){
				$roleFeatureMapper->delete($roleFeature);
			}
		}
		$jsonModel->setVariable('code', 1);
		return $jsonModel;
	}

	public function changecompanyfeatureAction(){
		$actionId = $this->getRequest()->getPost('actionId');
		$companyId = $this->getRequest()->getPost('companyId');
		$status = $this->getRequest()->getPost('status');

		$jsonModel = new JsonModel();
		if(!$actionId || !$status || !$companyId){
			$jsonModel->setVariables([
				'code' => 0,
				'messages' => ['Dữ liệu không hợp lệ']
			]);
			return $jsonModel;
		}
		$companyFeature = new \Company\Model\Feature();
		$companyFeature->setCompanyId($companyId);
		$companyFeature->setActionId($actionId);
		$companyFeatureMapper = $this->getServiceLocator()->get('\Company\Model\FeatureMapper');
		if($status == \System\Model\Action::STATUS_ACTIVE){
			if(!$companyFeatureMapper->isExisted($companyFeature)){
				$companyFeatureMapper->save($companyFeature);
			}
		} else {
			if($companyFeatureMapper->isExisted($companyFeature)){
				$companyFeatureMapper->delete($companyFeature);
			}
		}
		$jsonModel->setVariables([
				'code' => 1,
				]);
		return $jsonModel;
	}

	public function savecompanyfeatureAction(){

		$actionIds = $this->getRequest()->getPost('actionIds');
		$companyId = $this->getRequest()->getPost('companyId');
		$jsonModel = new JsonModel();
		if(!$companyId){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Dữ liệu không hợp lệ']
					]);
			return $jsonModel;
		}

		$companyFeature = new \Company\Model\Feature();
		$companyFeature->setCompanyId($companyId);
		$companyFeatureMapper = $this->getServiceLocator()->get('\Company\Model\FeatureMapper');
		$companyFeatureMapper->deleteCompanyFeature($companyFeature);
		if($actionIds){
			$actionIds = explode(',', $actionIds);
			foreach ($actionIds as $actionId){
				$companyFeature->setActionId($actionId);
				if(!$companyFeatureMapper->isExisted($companyFeature)){
					$companyFeatureMapper->save($companyFeature);
				}
			}
		}

		$jsonModel->setVariables([
				'code' => 1,
				]);
		return $jsonModel;
	}
}
