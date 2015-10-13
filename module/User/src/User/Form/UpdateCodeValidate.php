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

class UpdateCodeValidate extends FormBase
{
    public function __construct($serviceLocator, $options=null)
    {

        parent::__construct('updatecode');
        $this->setServiceLocator($serviceLocator);
        $filter = $this->getInputFilter();
        $code = new Text('code');
        $this->add($code);
        $filter->add(array(
            'name' => 'code',
            'required' => true,
            'filters'   => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập mã nhân viên hoặc mã nhân viên không hợp lệ.'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'options' => array(
                        'min' => '1',
                        'max' => '4',
                        'messages' => array(
                            \Zend\Validator\StringLength::INVALID => 'Mã nhân viên không hợp lệ.',
                            \Zend\Validator\StringLength::TOO_SHORT => 'Mã nhân viên không hợp lệ. Là chuỗi số từ 1 đến 4 chữ số',
                            \Zend\Validator\StringLength::TOO_LONG => 'Mã nhân viên không hợp lệ. Là chuỗi số từ 1 đến 4 chữ số',
                        )
                    )
                ),
            ),
        ));
    }
}