<?php

namespace Admin\Form\Expert;

use Home\Form\FormBase;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use ZendX\Form\Element\DisplayGroup;

class Expert extends FormBase
{

    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('AccountIndex');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $group = new DisplayGroup('Subject');
        $group->setLabel('Thêm mentor');
        $this->add($group);

        $subject = new Text('subject');
        $subject->setLabel('Tên môn:');
        $subject->setAttributes([
            'maxlength' => 255,
            'style' => 'width:100% !important',
            'placeholder' => 'Tên môn học'
        ]);
        $subject->setOptions(array(
            'tagsinput' => true,
            'description' => 'Tên môn học'
        ));
        $this->add($subject);
        $group->addElement($subject);
        $filter->add(array(
            'name' => 'subject',
            'required' => false,
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
                            'isEmpty' => 'Bạn chưa nhập tên môn'
                        )
                    )
                )
            )
        ));

        $subjectId = new Hidden('subjectId');
        $subjectId->setLabel('Tên môn:');
        $this->add($subjectId);
        $group->addElement($subjectId);
        $filter->add(array(
            'name' => 'subjectId',
            'required' => false,
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
                            'isEmpty' => 'Bạn chưa nhập tên môn'
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