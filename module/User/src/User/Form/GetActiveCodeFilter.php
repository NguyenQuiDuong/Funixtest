<?php

namespace User\Form;

use Home\InputFilter\ProvidesEventsInputFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GetActiveCodeFilter extends ProvidesEventsInputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name'       => 'inputStr',
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                	'break_chain_on_failure' => true,
                	'options' => array(
                		'messages' => array(
                			'isEmpty' => 'Bạn phải nhập địa chỉ email đăng ký hoặc tên đăng nhập tài khoản đăng ký'
                		)
                	)
                ),
                array(
                    'name'    => 'StringLength',
                	'break_chain_on_failure' => true,
                    'options' => array(
                        'min' => 4,
                    	'messages' => array(
							'stringLengthTooShort' => 'Tên đăng nhập hoặc địa chỉ email phải có từ 4 ký tự'
                    	)
                    ),
                ),
            ),
        ));
        $this->getEventManager()->trigger('init', $this);
    }
}