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


        // name
        $userName = new Text('userName');
        $userName->setLabel('Người dùng: ');
        $userName->setAttributes([
            'maxlength' => 255
        ]);
        $this->add($userName);
        $group->addElement($userName);
        $filter->add(array(
            'name' => 'userName',
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
                            'isEmpty' => 'Bạn chưa nhập người dùng'
                        )
                    )
                )
            )
        ));
        $userId = new Hidden('userId');
        $this->add($userId);
        $group->addElement($userId);
        $filter->add(array(
            'name' => 'userId',
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
                            'isEmpty' => 'Bạn chưa nhập người dùng'
                        )
                    )
                )
            )
        ));

        // code
        $description = new Textarea('description');
        $description->setLabel('Mô tả mentor:');
        $description->setAttributes([
            'class' => 'form-control basicEditor ',
            'style' => 'min-height: 300px;'
        ]);
        $this->add($description);
        $group->addElement($description);
        $filter->add(array(
            'name' => 'description',
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
                            'isEmpty' => 'Bạn chưa nhập mô tả môn học'
                        )
                    )
                )
            )
        ));

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
            'name' => 'afterSubmit',
            'type' => 'radio',
            'attributes' => array(
                'value' => '/admin/expert/add'
            ),
            'options' => array(
                'layout' => 'fluid',
                'clearBefore' => true,
                'label' => 'Sau khi lưu dữ liệu:',
                'value_options' => array(
                    '/admin/expert/add' => 'Tiếp tục nhập',
                    '/admin/expert/index' => 'Hiện danh sách vừa nhập'
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