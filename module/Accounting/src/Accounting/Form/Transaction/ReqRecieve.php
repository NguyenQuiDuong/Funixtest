<?php
/**
 * @author KienNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 **/
namespace Accounting\Form\Transaction;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;
use Zend\Form\Element\Select;
use Home\Model\DateBase;
use ZendX\Form\Element\DisplayGroup;

class ReqRecieve extends FormBase
{
    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('fTransaction');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $basicGroup = new DisplayGroup('basicGroup');
        $this->add($basicGroup);

        $companyId = $this->addElementCompany('companyId', $basicGroup, ['required' => true]);

        $applyDate = new Text('applyDate');
        $applyDate->setLabel('Ngày hạch toán:');
        $applyDate->setAttribute('class', 'datepicker');
        $this->add($applyDate);
        $basicGroup->addElement($applyDate);
        $applyDate->setValue(DateBase::toDisplayDate(DateBase::getCurrentDate()));
        $filter->add(array(
            'name' => 'applyDate',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập ngày hạch toán'
                        )
                    )
                )
            )
        ));

        $description = new Text('description');
        $description->setLabel('Nội dung:');
        $this->add($description);
        $basicGroup->addElement($description);
        $filter->add(array(
            'name' => 'description',
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
                            'isEmpty' => 'Bạn chưa nhập nội dung phiếu thu'
                        )
                    )
                )
            )
        ));

        $accountId = new Select('accountId');
        $accountId->setLabel('Quỹ thu:');
        $accountId->setValueOptions(['' => '- Quỹ thu -']);
        $this->loadAccountingAccount($accountId, $companyId);
        $this->add($accountId);
        $basicGroup->addElement($accountId);
        $filter->add(array(
            'name' => 'accountId',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập quỹ thu'
                        )
                    )
                ),
                array(
                	'name' => 'InArray',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'haystack' => array_keys($accountId->getValueOptions()),
                        'messages' => array(
                            'notInArray' => 'Bạn chưa nhập quỹ thu'
                        )
                    )
                )
            )
        ));

        $items = new Hidden('items');
        $this->add($items);
        $filter->add(array(
            'name' => 'items',
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
                            'isEmpty' => 'Bạn chưa nhập chi tiết các khoản thu'
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'name' => 'afterSubmit',
            'type' => 'radio',
            'attributes' => array(
                'value' => '/accounting/transaction/addreqrecieve'
            ),
            'options' => array(
                'layout' => 'fluid',
                'clearBefore' => true,
                'label' => 'Sau khi lưu dữ liệu:',
                'value_options' => array(
                    '/accounting/transaction/addreqrecieve' => 'Tiếp tục nhập',
                    '/accounting/transaction/index' => 'Hiện danh sách vừa nhập'
                )
            )
        ));

        $this->add(array(
            'name' => 'btnSubmit',
            'options' => array(
                'clearBefore' => true
            ),
            'attributes' => array(
                'type' => 'button',
                'value' => 'Lưu',
                'id' => 'btnSave',
                'class' => 'btn btn-primary'
            )
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Form\Form::isValid()
     */
    public function isValid()
    {
        $isValid = parent::isValid();
        if($isValid){
            $data = parent::getData();

            // lấy danh sách category
            $expenseCategory = new \Accounting\Model\ExpenseCategory();
            $expenseCategory->setCompanyId($data['companyId']);
            $expenseCategory->addOption('fetchOnlyIds', true);
            $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
            $expenseCategoryIds = $expenseCategoryMapper->fetchAll($expenseCategory);

            $itemsArray = json_decode($data['items'], true);
            foreach ($itemsArray as $itemDataPopulate){
                $itemDataPopulate['accountId'] = $data['accountId'];
                $itemValidator = new \Accounting\Form\Transaction\ItemValidate($this->getServiceLocator(), [
            	   'expenseCategoryIds' => $expenseCategoryIds
                ]);
                $itemValidator->setData($itemDataPopulate);
                if(!$itemValidator->isValid()){
                    $this->get('items')->setMessages($itemValidator->getErrorMessagesList());
                    $isValid = false;
                }
            }
        }
        return $isValid;
    }

    /**
     * (non-PHPdoc)
     * @see \Home\Form\FormBase::getData()
     */
    public function getData($flag = null)
    {
        $data = parent::getData($flag);
        if($data['applyDate']){
            $data['applyDate'] = DateBase::toCommonDate($data['applyDate']);
        }
        $itemsArray = json_decode($data['items'], true);
        $itemData = [];
        foreach ($itemsArray as $itemDataPopulate){
            $itemDataPopulate['accountId'] = $data['accountId'];
            $itemValidator = new \Accounting\Form\Transaction\ItemValidate($this->getServiceLocator());
            $itemValidator->setData($itemDataPopulate);
            $itemValidator->isValid();
            $itemData[] = $itemValidator->getData();
        }
        $data['itemData'] = $itemData;
        return $data;
    }
}