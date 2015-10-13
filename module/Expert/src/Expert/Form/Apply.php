<?php

namespace Expert\Form;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\File;
use Zend\InputFilter\FileInput;
use Zend\Form\Element\Hidden;

class Apply extends FormBase
{

    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('AccountIndex');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $group = new DisplayGroup('Subject');
        $group->setLabel('Trở thành mentor  ');
        $this->add($group);


        // name
        $name = new Text('name');
        $name->setLabel('Tên danh mục:');
        $name->setAttributes([
            'maxlength' => 255,
            'placeholder' => 'Tên'
        ]);
        $this->add($name);
        $group->addElement($name);
        $filter->add(array(
            'name' => 'name',
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
                            'isEmpty' => 'Bạn chưa nhập tên'
                        )
                    )
                )
            )
        ));

        $email = new Text('email');
        $email->setLabel('Tên danh mục:');
        $email->setAttributes([
            'maxlength' => 255,
            'placeholder' => 'Email'
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
                            'isEmpty' => 'Bạn chưa nhập tên'
                        )
                    )
                )
            )
        ));


        $this->add(array(
            'name' => 'submit',
            'options' => array(
                'clearBefore' => true
            ),
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Lưu',
                'id' => 'btnSave',
                'class' => 'btn btn-primary'
            )
        ));
    }
}

?>