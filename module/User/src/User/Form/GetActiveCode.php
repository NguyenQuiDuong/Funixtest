<?php

namespace User\Form;

use Home\Form\ProvidesEventsForm,
	Zend\Captcha,
	Zend\Form\Element,
	Zend\Form\Form;

class GetActiveCode extends ProvidesEventsForm
{
	const ERROR_INVALID = "Mật khẩu cũ không chính xác";

	public function showInvalidMessage($error = self::ERROR_INVALID) {
		$this->get('username')->setMessages(array($error));
	}

    /**
     * @param null|string $name
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'profile');
        $this->setOptions(array(
        	'decorator' => array(
        		'type' => 'ul',
        	)
        ));

        $this->add(array(
            'name' => 'inputStr',
            'attributes' => array(
                'type'  => 'text',
            	'class' => 'validate[required],minSize[4]',
            	'id' => 'newpassword'
            ),
            'options' => array(
                'label' => 'Nhập địa chỉ email hoặc tên tài khoản đăng ký:',
            	'decorator' => array('type' => 'li')
            ),
        ));

        /* $this->add(array(
			'type'	=>	'Zend\Form\Element\Captcha',
        	'name'	=>	'captcha',
        	'options'	=>	array(
        		'label'	=>	'Nhập mã Captcha',
        		'captcha'	=>	new Captcha\Dumb(),
        	),
        )); */

        $this->add(array(
        		'name' => 'csrf',
        		'type' => 'Zend\Form\Element\Csrf',
        		'attributes' => array(
        				'type'  => 'csrf',
        		),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Xác nhận',
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

        $this->getEventManager()->trigger('init', $this);
    }
}