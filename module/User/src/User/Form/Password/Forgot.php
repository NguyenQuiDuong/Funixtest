<?php

namespace User\Form\Password;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Validator\StringLength;
use Zend\Form\Element\Captcha;
use Zend\Captcha\ReCaptcha;
use ZendX\Form\Element\DisplayGroup;

class Forgot extends FormBase
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

		$email = new Text('email');
		$email->setLabel('Email:');
		$email->setAttributes([
			'maxlength' => 255,
			'autocomplete' => 'off'
		]);
		$email->setOptions(['descriptions' => ['Nhập email khi bạn đăng ký tài khoản']]);
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

			),
		));
		$config = $this->getServiceLocator()->get('Config');
		$this->captcha = new ReCaptcha(array(
			'pubkey' => $config['captcha']['reCAPTCHA']['publicKey'],
			'privkey' => $config['captcha']['reCAPTCHA']['privateKey'],
			'ssl' => !isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? false : true
		));

		$this->captcha->setPrivkey($config['captcha']['reCAPTCHA']['privateKey']);
		$this->captcha->setPubkey($config['captcha']['reCAPTCHA']['publicKey']);

		$captcha = new Captcha('captcha');
		$captcha->setLabel('Mã bảo mật:');
		$captcha->setCaptcha($this->captcha);
		$this->add($captcha);
		$groupBasic->addElement($captcha);
		$filter->add(array(
			'name' => 'captcha',
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
				$this->captcha
			)
		));


		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Khôi phục mật khẩu',
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

	public function isValid(){
		$isValid = parent::isValid();
		if($isValid){
			$data = parent::getData();
			$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
			if(!($user = $userMapper->get(null, null, $data['email']))){
				$this->get('email')->setMessages(['Email không tồn tại trong hệ thống']);
				$isValid = false;
			} else {
				if(!$user->getActive()){
					$this->get('email')->setMessages(['Tài khoản chưa được active']);
					$isValid = false;
				}
				if($user->getLocked()){
					$this->get('email')->setMessages(['Tài khoản chưa được active']);
					$isValid = false;
				}
			}
		}
		return $isValid;
	}
}