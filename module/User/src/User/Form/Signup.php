<?php

namespace User\Form;

use Home\Form\FormBase;
use Zend\Form\Element\Password;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\Text;

class Signup extends FormBase
{
//	public function setCities($arr)
//	{
//		if(!!($city = $this->get('userInfo')->get('cityId'))) {
//			$city->setValueOptions(['' => '- Thành phố -'] + $arr);
//		}
//	}

//	public function setDistricts($arr)
//	{
//		if(!!($district = $this->get('userInfo')->get('districtId'))) {
//			$district->setValueOptions(['' => '- Quận huyện -'] + $arr);
//		}
//	}

	/**
	 * @param null|string $name
	 */
	public function __construct($serviceLocator, $options = null)
	{
		parent::__construct($serviceLocator);

		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'post');
		$this->setAttribute('class', 'f');
		$this->setAttribute('autocomplete', 'off');
		$this->setOptions(['layout' => 'form-2-cols']);

		$filter = $this->getInputFilter();

		$group = new DisplayGroup('Signup');
		$group->setLabel('Đăng kí');
		$this->add($group);


		// name
		$email = new Text('email');
		$email->setLabel('Email:');
		$email->setAttributes([
			'maxlength' => 255
		]);
		$this->add($email);
		$group->addElement($email);
		$filter->add(array(
			'name' => 'email',
			'required' => true,
			'filters' => array(
				array(
					'name' => 'StringTrim'
				)
			),
			'validators' => array(
				array(
					'name' => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập địa chỉ email'
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
			)
		));

		$username = new Text('username');
		$username->setLabel('Tên đăng nhập:');
		$username->setAttributes([
			'maxlength' => 255
		]);
		$this->add($username);
		$group->addElement($username);
		$filter->add(array(
			'name' => 'username',
			'required' => true,
			'filters' => array(
				array(
					'name' => 'StringTrim'
				)
			),
			'validators' => array(
				array(
					'name' => 'NotEmpty',
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
						'min' => 4,
						'messages' => array(
							'stringLengthTooShort' => 'Tên đăng nhập phải có từ 4 đến 32 kí tự'
						)
					),
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
				)
			)));

		// code
		$password = new Password('password');
		$password->setLabel('Mật khẩu');
		$this->add($password);
		$group->addElement($password);
		$filter->add(array(
			'name' => 'password',
			'required' => true,
			'filters' => array(
				array(
					'name' => 'StringTrim'
				)
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
						'min' => 6,
						'messages' => array(
							'stringLengthTooShort' => 'Mật khẩu phải có từ 6 kí tự trở lên'
						)
					),
				),
			),
		));

		$password2 = new Password('password2');
		$password2->setLabel('Xác nhận mật khẩu:');
		$this->add($password2);
		$group->addElement($password2);
		$filter->add(array(
			'name' => 'password2',
			'required' => true,
			'filters' => array(
				array(
					'name' => 'StringTrim'
				)
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
						'min' => 6,
						'messages' => array(
							'stringLengthTooShort' => 'Mật khẩu phải có từ 6 kí tự trở lên'
						)
					),
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

		$this->add(array(
			'name' => 'submit',
			'options' => array(
				'clearBefore' => true
			),
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Đăng kí',
				'id' => 'btnSave',
				'class' => 'btn btn-primary'
			)
		));
	}
}