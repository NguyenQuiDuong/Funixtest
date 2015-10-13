<?php

namespace Admin\Form\Subject;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\File;
use Zend\InputFilter\FileInput;
use Zend\Form\Element\Hidden;

class Category extends FormBase
{

    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('AccountIndex');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');
        
        $filter = $this->getInputFilter();
        
        $group = new DisplayGroup('Subject');
        $group->setLabel('Thêm danh mục môn học');
        $this->add($group);
        
        
        // name
        $name = new Text('name');
        $name->setLabel('Tên danh mục:');
        $name->setAttributes([
            'maxlength' => 255
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
                            'isEmpty' => 'Bạn chưa nhập tên danh mục'
                        )
                    )
                )
            )
        ));
        
        // code
        $description = new Textarea('description');
        $description->setLabel('Mô tả danh mục:');
        $description->setAttributes([
            'maxlength' => 255,
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
                            'isEmpty' => 'Bạn chưa nhập mô tả danh mục'
                        )
                    )
                )
            )
        ));
        

           
        $this->add(array(
            'name' => 'afterSubmit',
            'type' => 'radio',
            'attributes' => array(
                'value' => '/admin/subject/addcategory'
            ),
            'options' => array(
                'layout' => 'fluid',
                'clearBefore' => true,
                'label' => 'Sau khi lưu dữ liệu:',
                'value_options' => array(
                    '/admin/subject/addcategory' => 'Tiếp tục nhập',
                    '/admin/subject/category' => 'Hiện danh sách vừa nhập'
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