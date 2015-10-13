<?php

namespace User\Form;

use Home\InputFilter\ProvidesEventsInputFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ChangePasswordFilter extends ProvidesEventsInputFilter
{
    public function __construct()
    {
    	$this->add(array(
    			'name'       => 'oldpassword',
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
        $this->add(array(
            'name'       => 'newpassword',
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

        $this->add(array(
            'name'       => 'repassword',
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
        $this->getEventManager()->trigger('init', $this);
    }
}