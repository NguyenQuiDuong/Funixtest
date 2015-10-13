<?php

namespace System\Form\User;

use Home\Form\FormBase;
use Home\Model\DateBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Select;
use Zend\Form\Element\Date;
use ZendX\Form\Element\DisplayGroup;
use Zend\Validator\StringLength;
use Zend\Uri\UriFactory;
use Zend\Form\Element\Password;

class Add extends FormBase{
	/**
	 * @param null|string $name
	 */
	public function __construct($serviceLocator, $options=null){
		parent::__construct('fUserAdd');
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'post');

		$filter = $this->getInputFilter();
		$groupBasic = new DisplayGroup('groupBasic');
		$groupBasic->setLabel('Thông tin cơ bản');
		$this->add($groupBasic);

		$username = new Text('username');
		$username->setLabel('Tên đăng nhập:');
		$username->setAttributes([
			'maxlength' => 50,
			'autocomplete' => 'off'
		]);
		$this->add($username);
		$groupBasic->addElement($username);
		$filter->add(array(
			'name' => 'username',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
				array('name' => 'StringToLower')
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập tên đăng nhập'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => 50,
						'min' => 4,
						'messages' => array(
							StringLength::TOO_LONG => 'Tên đăng nhập giới hạn 4-50 kí tự',
							StringLength::TOO_SHORT => 'Tên đăng nhập giới hạn 4-50 kí tự'
						)
					)
				),
				array(
					'name'    => 'Regex',
					'break_chain_on_failure' => true,
					'options' => array(
						'pattern' => "/^[a-z0-9_-]{4,32}$/",
						'messages' => array(
							'regexNotMatch' => 'Chỉ chấp nhận các kí tự là chữ, chữ số, dấu - và dấu _'
						)
					),
				),
				array(
					'name'    => 'Db\NoRecordExists',
					'options' => array(
						'table' => 'users',
						'field' => 'username',
						'adapter' => \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter(),
						'messages' => array(
							'recordFound' => "Tên đăng nhập này đã được sử dụng"
						)
					),
				),
			),
		));

		$fulName = new Text('fullName');
		$fulName->setLabel('Họ tên:');
		$fulName->setAttributes([
			'maxlength' => 255,
			'autocomplete' => 'off'
		]);
		$this->add($fulName);
		$groupBasic->addElement($fulName);
		$filter->add(array(
			'name' => 'fullName',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập họ tên'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => 255,
						'min' => 4,
						'messages' => array(
							StringLength::TOO_LONG => 'Tên đăng nhập giới hạn 4-250 kí tự',
							StringLength::TOO_SHORT => 'Tên đăng nhập giới hạn 4-250 kí tự'
						)
					)
				),
			),
		));

		$email = new Text('email');
		$email->setLabel('Email:');
		$email->setAttributes([
				'maxlength' => 255,
				'autocomplete' => 'off'
				]);
		$this->add($email);
		$groupBasic->addElement($email);
		$filter->add(array(
			'name' => 'email',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập email'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => 50,
						'min' => 4,
						'messages' => array(
							StringLength::TOO_LONG => 'Tên đăng nhập giới hạn 4-50 kí tự',
							StringLength::TOO_SHORT => 'Tên đăng nhập giới hạn 4-50 kí tự'
						)
					)
				),
				array(
					'name'    => 'EmailAddress',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'emailAddressInvalidFormat' => 'Địa chỉ email không hợp lệ'
						)
					)
				),
				array(
					'name'    => 'Db\NoRecordExists',
					'options' => array(
						'table' => 'users',
						'field' => 'email',
						'adapter' => \Zend\Db\TableGateway\Feature\GlobalAdapterFeature::getStaticAdapter(),
						'messages' => array(
							'recordFound' => "Email này đã được sử dụng"
						)
					),
				),
			),
		));

		$user = new \User\Model\User();
		$role = new Select('role');
		$role->setLabel('Phân quyền:');
		$role->setValueOptions(['' => '- Phân quyền -'] + $user->getRoleDisplays());
		$this->add($role);
		$groupBasic->addElement($role);
		$filter->add(array(
			'name' => 'role',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa chọn Phân quyền'
						)
					)
				),
			),
		));


		$password = new Password('password');
		$password->setLabel('Mật khẩu:');
		$password->setAttributes([
			'maxlength' => 32,
			'autocomplete' => 'off'
		]);
		$this->add($password);
		$groupBasic->addElement($password);
		$filter->add(array(
			'name' => 'password',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập Mật khẩu'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => 32,
						'min' => 6,
						'messages' => array(
							StringLength::TOO_LONG => 'Mật khẩu giới hạn 6-32 kí tự',
							StringLength::TOO_SHORT => 'Mật khẩu giới hạn 6-32 kí tự'
						)
					)
				),
			),
		));

		$rePassword = new Password('rePassword');
		$rePassword->setLabel('Nhập lại Mật khẩu:');
		$rePassword->setAttributes([
			'maxlength' => 32,
			'autocomplete' => 'off'
		]);
		$this->add($rePassword);
		$groupBasic->addElement($rePassword);
		$filter->add(array(
			'name' => 'rePassword',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập lại Mật khẩu'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => 32,
						'min' => 6,
						'messages' => array(
							StringLength::TOO_LONG => 'Mật khẩu giới hạn 6-32 kí tự',
							StringLength::TOO_SHORT => 'Mật khẩu giới hạn 6-32 kí tự'
						)
					)
				),
				array(
					'name'    => 'Identical',
					'break_chain_on_failure' => true,
					'options' => array(
						'token' => 'password',
						'messages' => array(
							'notSame' => 'Xác nhận mật khẩu không chính xác'
						)
					),
				),
			),
		));

		$mobile = new Text('mobile');
		$mobile->setLabel('Mobile:');
		$mobile->setAttributes([
			'maxlength' => 15
		]);
		$mobile->setOptions([
			'leftIcon' => 'fa fa-mobile'
		]);
		$this->add($mobile);
		$groupBasic->addElement($mobile);
		$filter->add(array(
			'name' => 'mobile',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
				array('name' => 'Digits')
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập Mobile'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'min' => 10,
						'max' => 11,
						'messages' => array(
							StringLength::INVALID => 'Mobile phải là dạng 10 hoặc 11 chữ số',
							StringLength::TOO_SHORT => 'Mobile phải là dạng 10 hoặc 11 chữ số',
							StringLength::TOO_LONG => 'Mobile phải là dạng 10 hoặc 11 chữ số'
						)
					)
				),
			),
		));

		// group additional
		$groupAdditional = new DisplayGroup('groupAdditional');
		$groupAdditional->setLabel('Thông tin cá nhân');
		$this->add($groupAdditional);

		$gender = new Select('gender');
		$this->add($gender);
		$groupAdditional->addElement($gender);
		$gender->setLabel('Giới tính:');
		$gender->setValueOptions(array(
			'' => '- Giới tính -',
			\Home\Model\Consts::GENDER_MALE 	=> 'Nam',
			\Home\Model\Consts::GENDER_FEMALE 	=> 'Nữ',
		));
		$filter->add(array(
			'name' => 'gender',
			'required' => false,
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập Giới tính'
						)
					)
				),
			),
		));

		$birthdate = new Text('birthdate');
		$birthdate->setLabel('Ngày sinh:');
		$birthdate->setAttributes([
			'class' => 'datetimepicker',
			'data-date-format'=>"DD/MM/YYYY"
			]);
		$birthdate->setOptions([
			'leftIcon' => 'fa fa-calendar'
		]);
		$this->add($birthdate);
		$groupAdditional->addElement($birthdate);
		$filter->add(array(
			'name' => 'birthdate',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'Date',
					'break_chain_on_failure' => true,
					'options' => array(
						'format' => DateBase::getDisplayDateFormat(),
						'messages' => array(
							'dateInvalid' => 'Ngày sinh không hợp lệ',
							'dateInvalidDate' => 'Ngày sinh không hợp lệ',
							'dateFalseFormat' => 'Ngày sinh không hợp lệ'
						)
					)
				),
			),
		));

		$cityId = new Select('cityId');
		$cityId->setLabel('Thành phố:');
		$cityId->setValueOptions(array(
			'' => '- Thành phố -'
		));
		$this->add($cityId);
		$groupAdditional->addElement($cityId);
		$filter->add(array(
			'name' => 'cityId',
			'required' => false,
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập thành phố'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => 250,
						'messages' => array(
							StringLength::INVALID => 'Địa chỉ chỉ giới hạn nhỏ hơn 250 kí tự',

						)
					)
				),
			),
		));

		$districtId = new Select('districtId');
		$districtId->setLabel('Quận huyện:');
		$districtId->setValueOptions(array(
			'' => '- Quận huyện -'
		));
		$this->add($districtId);
		$groupAdditional->addElement($districtId);
		$filter->add(array(
			'name' => 'districtId',
			'required' => false,
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập thành phố'
						)
					)
				),
			),
		));

		$address = new Text('address');
		$address->setLabel('Địa chỉ:');
		$address->setAttributes([
			'maxlength' => 255
		]);
		$this->add($address);
		$groupAdditional->addElement($address);
		$filter->add(array(
			'name' => 'address',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập địa chỉ'
						)
					)
				),
			),
		));

		$this->add(array(
			'name' => 'afterSubmit',
			'type' => 'radio',
			'attributes' => array(
				'value' => '/system/user/add',
			),
			'options' => array(
				'layout' => 'fluid',
				'clearBefore' => true,
				'label' => 'Sau khi lưu dữ liệu:',
				'value_options' => array(
					'/system/user/add' 		=> 'Tiếp tục nhập người dùng',
					'/system/user/index' 	=> 'Hiện danh sách người dùng',
				),
			),
		));

		$this->add(array(
			'name' => 'submit',
			'options' => array(
				'clearBefore' => true
			),
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Lưu',
				'id' => 'btnSaveCrmContact',
				'class' => 'btn btn-primary'
			),
		));
	}

	public function setCities($arr)
	{
		$this->get('cityId')->setValueOptions(['' => '- Thành phố -'] + $arr);
	}

	public function setDistricts($arr)
	{
		$this->get('districtId')->setValueOptions(['' => '- Quận huyện -'] + $arr);
	}

	public function isValid(){
		$isVaild = parent::isValid();
		if($isVaild){
			$user = new \User\Model\User();
			$user->exchangeArray($this->getData());
			$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
			if($userMapper->isExisted($user)){
				$this->get('username')->setMessages(['Tên tài khoản hoặc email này đã được sử dụng']);
				$isVaild = false;
			}
		}
		return $isVaild;
	}
}