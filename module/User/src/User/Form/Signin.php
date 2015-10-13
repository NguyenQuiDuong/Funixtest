<?php

namespace User\Form;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Validator\StringLength;
use Zend\Form\Element\Captcha;
use Zend\Captcha\ReCaptcha;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Password;

class Signin extends FormBase
{
	const ERROR_INVALID = "Tên đăng nhập hoặc mật khẩu không chính xác";
	const ERROR_LOCKED = "Tài khoản của bạn đã bị khóa";
	const ERROR_INACTIVE = "Tài khoản của bạn chưa được kích hoạt";
	/**
	 * @param null|string $name
	 */
	public function __construct($serviceLocator, $options=null)
	{
		parent::__construct('signin');
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'post');
		$this->setOptions(['layout' => 'fluid']);

		$filter = $this->getInputFilter();

		//$groupBasic = new DisplayGroup('groupBasic');
		//$this->add($groupBasic);

		$csrf = new Csrf('csrf');

		$this->add($csrf);
		$filter->add(array(
			'name' => 'csrf',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
		));

		$username = new Text('username');
		$username->setLabel('Tên đăng nhập:');
		$username->setAttributes(array(
			'type'  => 'text',
			'id' => 'username',
		));
		$this->add($username);
		//$groupBasic->addElement($username);
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
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập Tên đăng nhập'
						)
					)
				),
			),
		));

		$password = new Password('password');
		$password->setAttributes(array(
			'type'  => 'password',
			'id' => 'password'
		));
		$password->setLabel('Mật khẩu:');
		$this->add($password);
		//$groupBasic->addElement($password);
		$filter->add(array(
			'name' => 'password',
			'required' => true,
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập mật khẩu'
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


		$captcha = new Captcha('captcha');
		$captcha->setLabel('Mã bảo mật:');
		$captcha->setCaptcha($this->captcha);

		$this->add($captcha);
		//$groupBasic->addElement($captcha);
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
							'isEmpty' => 'Bạn chưa nhập captcha'
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
				'value' => 'Đăng nhập',
				'id' => 'btnSignin',
				'class' => 'btn btn-primary col-md-12'
			),
		));
		//$groupBasic->addElement($this->get('submit'));

	}

	public function removeCaptcha(){
		if($this->has('captcha')){
			$this->remove('captcha');
			$filter = $this->getInputFilter();
			$filter->remove('captcha');
		}
		return $this;
	}

	public function isValid(){
		$isValid = parent::isValid();
		if($isValid){
			$userService = $this->getServiceLocator()->get('User\Service\User');
			$data = parent::getData();
			if(!$userService->authenticate($data['username'], $data['password'])){
				$this->get('username')->setMessages([self::ERROR_INVALID]);
				return false;
			}
			/* @var $user \User\Model\User */
            $user = $userService->getUser();
//            if(!$user->getActive()){
//            	$this->get('username')->setMessages([self::ERROR_INACTIVE]);
//            	$userService->getAuthService()->clearIdentity();
//            	return false;
//            }
//            if($user->getLocked()){
//            	$this->get('username')->setMessages([self::ERROR_LOCKED]);
//            	$userService->getAuthService()->clearIdentity();
//            	return false;
//            }

		}
		return $isValid;
	}
}