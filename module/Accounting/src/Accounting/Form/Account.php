<?php
/**
 * @author hungpx
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 **/
namespace Accounting\Form;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\File;
use Zend\InputFilter\FileInput;
use Zend\Form\Element\Hidden;

class Account extends FormBase
{

    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('AccountIndex');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');
        
        $filter = $this->getInputFilter();
        
        $group = new DisplayGroup('Accountgroup');
        $group->setLabel('Thêm tài khoản');
        $this->add($group);
        
        /*
         * $parentId = new Select('parentId'); $parentId->setLabel('Công ty mẹ:'); $parentId->setAttributes([ 'maxlength' => 19 ]); $this->add($parentId); $groupCompany->addElement($parentId); $this->loadCompanies($parentId); $filter->add(array( 'name' => 'parentId', 'required' => false ));
         */
        
        // companyId select
        $companyId = $this->addElementCompany('companyId', $group, [
            'required' => false
            ]);
        
        // name
        $name = new Text('name');
        $name->setLabel('Tên tài khoản:');
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
                            'isEmpty' => 'Bạn chưa nhập tên tài khoản'
                        )
                    )
                )
            )
        ));
        
        // parent Text
        $parentName = new Text('parentName');
        $parentName->setLabel('Thuộc khoản mục:');
        $this->add($parentName);
        $group->addElement($parentName);
        $filter->add(array(
            'name' => 'parentName',
            'required' => false
        ));
        
        // parent hidden
        $parentId = new Hidden('parentId');
        $this->add($parentId);
        $group->addElement($parentId);
        $filter->add(array(
            'name' => 'parentId',
            'required' => false
        ));
        
        // code
        $code = new Text('code');
        $code->setLabel('Mã code:');
        $code->setAttributes([
            'maxlength' => 255
        ]);
        $this->add($code);
        $group->addElement($code);
        $filter->add(array(
            'name' => 'code',
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
                            'isEmpty' => 'Bạn chưa nhập mã code'
                        )
                    )
                )
            )
        ));
        

           
        $this->add(array(
            'name' => 'afterSubmit',
            'type' => 'radio',
            'attributes' => array(
                'value' => '/accounting/account/add'
            ),
            'options' => array(
                'layout' => 'fluid',
                'clearBefore' => true,
                'label' => 'Sau khi lưu dữ liệu:',
                'value_options' => array(
                    '/accounting/account/add' => 'Tiếp tục nhập',
                    '/accounting/account/index' => 'Hiện danh sách vừa nhập'
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