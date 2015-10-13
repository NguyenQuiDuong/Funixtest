<?php

namespace System\Form\User;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;

class Filter extends FormBase{
	/**
	 * @param null|string $username
	 */
	public function __construct($serviceLocator){
		parent::__construct('userManageFilter');
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'GET');

		$filter = $this->getInputFilter();
		$companyId = $this->addElementCompany('companyId', null, ['required' => false]);
		$departmentId = new Select('departmentId');
		$departmentId->setValueOptions(['' => '- Phòng ban -']);
		$this->add($departmentId);
		$this->loadDepartments($departmentId, $companyId);
		$filter->add(array(
		    'name'     => 'departmentId',
		    'required' => false,
		    'filters'  => array(
		        array('name' => 'StringTrim')
		    ),
		));
		$id = new Text('id');
		$id->setAttributes([
				'maxlength' => 255,
				'placeholder' => 'ID'
				]);
		$this->add($id);
		$filter->add(array(
			'name' => 'id',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim')
			),
		));

		$username = new Text('username');
		$username->setAttributes([
				'maxlength' => 255,
				'placeholder' => 'Họ tên'
				]);
		$this->add($username);
		$filter->add(array(
			'name' => 'username',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim')
			),
		));
		
		$email = new Text('email');
		$email->setAttributes([
				'maxlength' => 255,
				'placeholder' => 'Email'
				]);
		$this->add($email);
		$filter->add(array(
			'name' => 'email',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim')
			),
		));
		
		//phân quyền riêng
		$hasPrivateRole = new Select('hasPrivateRole');
		$hasPrivateRole->setValueOptions(array(
		    ''	=>	'- Phân quyền riêng -',
		    '1' => 'Có ',
		    '-1' => 'Không '
		));
		$this->add($hasPrivateRole);
		$filter->add(array(
		    'name' => 'hasPrivateRole',
		    'required' => false,
		));

		$user = new \User\Model\User();
		$roleValues = array(
			'' => '- Quyền hạn -',
		) + $user->getRoleDisplays();
		unset($roleValues[$user::ROLE_SUPERADMIN]);
		$role = new Select('role');
		$role->setValueOptions($roleValues);
		$this->add($role);
		$filter->add(array(
			'name' => 'role',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
				array('name' => 'Digits')
			),
		));
		
		$roleCompany = new Select('roleCompany');
        $roleCompany->setValueOptions(['' => '- Nhóm quyền -']);
        $this->add($roleCompany);
        $this->loadRole($roleCompany, $companyId);
        $filter->add(array(
            'name'     => 'roleCompany',
            'required' => false,
            'filters'  => array(
                array('name' => 'StringTrim')
            ),
        ));
		

		$active = new Select('active');
		$active->setValueOptions(array(
			'' => '- Kích hoạt',
			'1' => 'Đã kích hoạt',
			'-1' => 'Chưa kích hoạt'
		));
		$this->add($active);
		$filter->add(array(
			'name' => 'active',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim')
			),
		));

		$locked = new Select('locked');
		$locked->setValueOptions(array(
			'' => '- Khóa -',
			'1' => 'Đã khóa',
			'-1' => 'Chưa khóa'
		));
		$this->add($locked);
		$filter->add(array(
			'name' => 'locked',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim')
			),
		));

		$this->add(array(
			'name' => 'submit',
			'options' => array(
			),
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Lọc',
				'id' => 'btnFilterCrmContact',
				'class' => 'btn btn-primary'
			),
		));
	}
}