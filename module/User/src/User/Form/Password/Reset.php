<?php

namespace User\Form\Password;

use Home\Form\FormBase;
use Zend\Validator\StringLength;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\Password;

class Reset extends FormBase
{
	/**
	 * @param null|string $name
	 */
	public function __construct($serviceLocator, $options=null)
	{
		parent::__construct('passwordForgot');
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'post');

		$filter = $this->getInputFilter();

		$groupBasic = new DisplayGroup('groupBasic');
		$this->add($groupBasic);

		$password = new Password('password');
		$password->setLabel('Mật khẩu mới:');
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
		$rePassword->setLabel('Nhập lại mật khẩu mới:');
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

		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Đặt lại mật khẩu',
				'id' => 'btnSubmit',
				'class' => 'htmlBtn first btn btn-primary'
			),
			'options' => array(
				'clearBefore' => true,
				'decorator' => array(
					'type' => 'li',
					'attributes' => array(
						'class' => 'btns'
					)
				)
			),
		));
	}
}