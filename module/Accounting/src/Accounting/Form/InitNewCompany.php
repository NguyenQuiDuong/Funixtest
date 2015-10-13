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

class InitNewCompany extends FormBase
{
    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('AccountIndex');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $group = new DisplayGroup('Accountgroup');
        $this->add($group);

        /*
         * $parentId = new Select('parentId'); $parentId->setLabel('Công ty mẹ:'); $parentId->setAttributes([ 'maxlength' => 19 ]); $this->add($parentId); $groupCompany->addElement($parentId); $this->loadCompanies($parentId); $filter->add(array( 'name' => 'parentId', 'required' => false ));
        */

        // companyId select
        $companyId = $this->addElementCompany('companyId', $group, [
            'required' => true
            ]);
        $this->add(array(
            'name' => 'afterSubmit',
            'type' => 'radio',
            'attributes' => array(
                'value' => '/accounting/account/initnewcompany'
            ),
            'options' => array(
                'layout' => 'fluid',
                'clearBefore' => true,
                'label' => 'Sau khi lưu dữ liệu:',
                'value_options' => array(
                    '/accounting/account/initnewcompany' => 'Tiếp tục nhập',
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
                'value' => 'Tạo',
                'id' => 'btnSave',
                'class' => 'btn btn-primary'
            )
        ));
    }
}