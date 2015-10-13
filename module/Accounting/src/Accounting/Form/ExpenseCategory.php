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

class ExpenseCategory extends FormBase
{
    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('ExpenseCategoryIndex');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');
        
        $filter = $this->getInputFilter();
        
        $group = new DisplayGroup('Expensegroup');
        $group->setLabel('Thêm tài khoản');
        $this->add($group);
        
        /*
         * $parentId = new Select('parentId'); $parentId->setLabel('Công ty mẹ:'); $parentId->setAttributes([ 'maxlength' => 19 ]); $this->add($parentId); $groupCompany->addElement($parentId); $this->loadCompanies($parentId); $filter->add(array( 'name' => 'parentId', 'required' => false ));
         */
        
        // companyId
        $companyId = $this->addElementCompany('companyId', $group, [
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
                            'isEmpty' => 'Bạn chưa chọn công ty'
                        )
                    )
                )
            )
        ]
        );
        
        // name
        $name = new Text('name');
        $name->setLabel('Tên khoản mục:');
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
                            'isEmpty' => 'Bạn chưa nhập tên khoản mục'
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
        
        // id hidden
        $id = new Hidden('id');
        $this->add($id);
        $group->addElement($id);
        $filter->add(array(
            'name' => 'id',
            'required' => false
        ));
        
        $this->add(array(
            'name' => 'afterSubmit',
            'type' => 'radio',
            'attributes' => array(
                'value' => '/accounting/expense/addcategory'
            ),
            'options' => array(
                'layout' => 'fluid',
                'clearBefore' => true,
                'label' => 'Sau khi lưu dữ liệu:',
                'value_options' => array(
                    '/accounting/expense/addcategory' => 'Tiếp tục nhập',
                    '/accounting/expense/category' => 'Hiện danh sách vừa nhập'
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

    public function isValid()
    {
        $isVaild = parent::isValid();
        if ($isVaild) {
            $data = parent::getData();
            $expenseCategory = new \Accounting\Model\ExpenseCategory();
            if($data['id']){
                $expenseCategory->setId($data['id']);
            }
            if ($data['companyId']) {
                $expenseCategory->setCompanyId($data['companyId']);
            }
            if ($data['code']) {
                $expenseCategory->setCode($data['code']);
            }
            if ($data['name']) {
                $expenseCategory->setName($data['name']);
            }
            $expenseCategoryMapper = $this->getServiceLocator()->get('Accounting\Model\ExpenseCategoryMapper');
            $result = $expenseCategoryMapper->checkunique($expenseCategory);
            if ($result && $result == 'code') {
                $this->get('code')->setMessages([
                    'Mã code này đã tồn tại'
                ]);
                $isVaild = false;
            }
            if ($result && $result == 'name') {
                $this->get('name')->setMessages([
                    'tên này đã tồn tại'
                    ]);
                $isVaild = false;
            }
            
            return $isVaild;
        }
    }
}

?>