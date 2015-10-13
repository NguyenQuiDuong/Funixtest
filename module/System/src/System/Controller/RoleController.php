<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Controller;

use Home\Controller\ControllerBase;
use Zend\View\Model\JsonModel;
use Home\Model\DateBase;

class RoleController extends ControllerBase{

	public function indexAction(){
		$role = new \System\Model\Role();
		$roleMapper = $this->getServiceLocator()->get('\System\Model\RoleMapper');
		$this->getViewModel()->setVariable('dataSource', $roleMapper->fetchAll($role));
		return $this->getViewModel();
	}

	public function addAction(){
		$jsonModel = new JsonModel();
		$name = $this->getRequest()->getPost('name');
		if(!$name){
			$jsonModel->setVariables([
				'code' => 0,
				'messages' => ['Dữ liệu không hợp lệ']
			]);
			return $jsonModel;
		}
		$role = new \System\Model\Role();
		$role->setName(trim($name));
		$roleMapper = $this->getServiceLocator()->get('\System\Model\RoleMapper');
		if($roleMapper->isExisted($role)){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Tên này đã tồn tại']
					]);
			return $jsonModel;
		}
		$role->setCreatedById($this->user()->getIdentity());
		$role->setCreatedDateTime(DateBase::getCurrentDateTime());
		$roleMapper->save($role);
		$jsonModel->setVariable('code', 1);
		$data = array(
			'id' => $role->getId(),
			'name' => $role->getName(),
			'order' => ''
		);
		$jsonModel->setVariable('data', $data);
		return $jsonModel;
	}

	public function editAction(){
		$jsonModel = new JsonModel();
		$id = trim($this->getRequest()->getPost('id'));
		$name = trim($this->getRequest()->getPost('name'));
		if(!$id || !$name){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Dữ liệu không hợp lệ']
					]);
			return $jsonModel;
		}
		$role = new \System\Model\Role();
		$role->setId($id);
		$roleMapper = $this->getServiceLocator()->get('\System\Model\RoleMapper');
		if(!$roleMapper->get($role)){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Không tìm thấy role']
					]);
			return $jsonModel;
		}
		if($name != $role->getName()){
			$role->setName($name);
			if($roleMapper->isExisted($role)){
				$jsonModel->setVariables([
						'code' => 0,
						'messages' => ['Tên này đã tồn tại']
						]);
				return $jsonModel;
			}
			$roleMapper->save($role);

		}
		$jsonModel->setVariable('code', 1);
		return $jsonModel;

	}

	public function deleteAction(){
		$jsonModel = new JsonModel();
		$id = trim($this->getRequest()->getPost('id'));
		if(!$id){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Dữ liệu không hợp lệ']
					]);
			return $jsonModel;
		}
		$role = new \System\Model\Role();
		$role->setId($id);
		$roleMapper = $this->getServiceLocator()->get('\System\Model\RoleMapper');
		if(!$roleMapper->get($role)){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Không tìm thấy role']
					]);
			return $jsonModel;
		}
		if($roleMapper->isRoleUsed($role)){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Quyền đang được sử dụng, không thể xóa']
					]);
			return $jsonModel;
		}
		$roleMapper->delete($role);
		$jsonModel->setVariable('code', 1);
		return $jsonModel;
	}
}