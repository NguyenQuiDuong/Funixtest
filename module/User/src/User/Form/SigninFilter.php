<?php

namespace User\Form;

use Home\InputFilter\ProvidesEventsInputFilter;

class SigninFilter extends ProvidesEventsInputFilter
{
    public function __construct()
    {
//         $this->add(array(
//             'name'       => 'csrf',
//             'validators' => array(
//                 array(
//                     'name'    => 'Csrf',
//                 	'options' => array(
//                 		'messages' => array(
//                 			'notSame' => 'Xin vui lòng kiểm tra lại thông tin'
//                 		)
//                 	)
//                 ),
//             ),
//         ));

        $this->add(array(
            'name'       => 'username',
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

        $this->add(array(
            'name'       => 'password',
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                	'options' => array(
                		'messages' => array(
                			'isEmpty' => 'Bạn chưa nhập Mật khẩu'
                		)
                	)
                ),
            ),
        ));

        $this->getEventManager()->trigger('init', $this);
    }
}