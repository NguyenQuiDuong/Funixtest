<?php

namespace User\Form;

use Home\Form\ProvidesEventsForm;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\Password;

class ChangePassword extends ProvidesEventsForm
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
//         $this->add(array(
//         		'name' => 'oldpassword',
//         		'attributes' => array(
//         				'type'  => 'password',
//         				'class' => 'validate[required],minSize[6]',
//         				'id' => 'oldpassword'
//         		),
//         		'options' => array(
//         				'label' => 'Mật khẩu cũ:',
//         				'decorator' => array('type' => 'li')
//         		),
//         ));

//         $this->add(array(
//             'name' => 'newpassword',
//             'attributes' => array(
//                 'type'  => 'password',
//             	'class' => 'validate[required],minSize[6]',
//             	'id' => 'newpassword'
//             ),
//             'options' => array(
//                 'label' => 'Mật khẩu mới:',
//             	'decorator' => array('type' => 'li')
//             ),
//         ));
//         $this->add(array(
//         		'name' => 'repassword',
//         		'attributes' => array(
//         				'type'  => 'password',
//         				'class' => 'validate[required],minSize[6],equals[newpassword]',
//         				'id' => 'repassword'
//         		),
//         		'options' => array(
//         				'label' => 'Nhập lại mật khẩu mới:',
//         				'decorator' => array('type' => 'li')
//         		),
//         ));
//         $this->getEventManager()->trigger('init', $this);
//         $this->add(array(
//         		'name' => 'submit',
//         		'attributes' => array(
//         				'type'  => 'submit',
//         				'value' => 'Xác nhận',
//         				'id' => 'btnSubmit',
//         				'class' => 'htmlBtn first'
//         		),
//         		'options' => array(
//         				'decorator' => array(
//         						'type' => 'li',
//         						'attributes' => array(
//         								'class' => 'btns'
//         						)
//         				)
//         		),
//         ));
        $filter = $this->getInputFilter();
        $changePass = new DisplayGroup('changePass');
        $changePass->setLabel('Đổi mật khẩu');
        $this->add($changePass);
        $oldPass = new Password('oldpassword');
        $oldPass->setLabel('Mật khẩu cũ:');
        $oldPass->setAttributes([
        		'maxlength' => 255,
        		'class' => 'validate[required],minSize[6]',
        		]);
        $this->add($oldPass);
        $changePass->addElement($oldPass);
        $filter->add(array(
        		'name' => 'oldpassword',
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
    											'isEmpty' => 'Bạn chưa nhập Mật khẩu cũ'
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

        $newPass = new Password('newpassword');
        $newPass->setLabel('Mật khẩu mới:');
        $newPass->setAttributes([
        		'maxlength' => 255,
        		'class' => 'validate[required],minSize[6]',
        		]);
        $this->add($newPass);
        $changePass->addElement($newPass);
        $filter->add(array(
        		'name' => 'newpassword',
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
                			'isEmpty' => 'Bạn chưa nhập Mật khẩu mới'
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
        $conPass = new Password('conpassword');
        $conPass->setLabel('Nhập lại mật khẩu mới:');
        $conPass->setAttributes([
        		'maxlength' => 255,
        		'class' => 'validate[required],minSize[6],equals[newpassword]',
        		]);
        $this->add($conPass);
        $changePass->addElement($conPass);
        $filter->add(array(
        		'name' => 'conpassword',
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
                			'isEmpty' => 'Bạn chưa nhập Xác nhận mật khẩu'
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
                        'token' => 'newpassword',
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
        				'type'  => 'submit',
        				'value' => 'Lưu',
        				'id' => 'btnSaveCrmContact',
        				'class' => 'btn btn-primary'
        		),
        ));
    }
}