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

class ApproveReq extends FormBase
{
    protected $companyId;
    protected $id;
    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('fTransaction');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $basicGroup = new DisplayGroup('basicGroup');
        $this->add($basicGroup);


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

        $isInApprove = new Hidden('isInApprove');
        $this->add($isInApprove);
        $filter->add(array(
            'name' => 'isInApprove',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim',
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
            $expenseCategory->setCompanyId($this->getCompanyId());
            $expenseCategory->addOption('fetchOnlyIds', true);
            $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
            $expenseCategoryIds = $expenseCategoryMapper->fetchAll($expenseCategory);

            // lấy danh sách accountId
            $account = new \Accounting\Model\Account();
            $account->setCompanyId($this->getCompanyId());
            $account->addOption('fetchOnlyIds', true);
            $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
            $accountIds = $accountMapper->fetchAll($account);

            $itemsArray = json_decode($data['items'], true);
            foreach ($itemsArray as $itemDataPopulate){
                $itemValidator = new \Accounting\Form\Transaction\ItemValidate($this->getServiceLocator(), [
                	   'expenseCategoryIds' => $expenseCategoryIds,
                        'accountIds' => $accountIds
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
            $itemDataPopulate['transactionId'] = $this->getId();
            $itemValidator = new \Accounting\Form\Transaction\ItemValidate($this->getServiceLocator());
            $itemValidator->setData($itemDataPopulate);
            $itemValidator->isValid();
            $itemData[] = $itemValidator->getData();
        }
        $data['itemData'] = $itemData;
        return $data;
    }
	/**
     * @return the $companyId
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

	/**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * @param field_type $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

	/**
     * @param field_type $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

}