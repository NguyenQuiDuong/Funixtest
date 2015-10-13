<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Accounting\Model;

use Home\Model\Base;
use Home\Model\DateBase;

class Transaction extends Base
{
    const TYPE_DEBIT    = 1; // Báo nợ (Ngân hàng: Báo có thì là ghi Nợ, Báo nợ thì ghi Có)
    const TYPE_CREDIT   = 2; // Báo có
    const TYPE_RECEIPT  = 3; // Phiếu thu
    const TYPE_PAY      = 4; // Phiếu chi


    protected $types = array(
        self::TYPE_DEBIT    => 'Báo nợ (Rút tiền)',
        self::TYPE_CREDIT   => 'Báo có (Nộp tiền)',
        self::TYPE_RECEIPT  => 'Phiếu thu',
        self::TYPE_PAY      => 'Phiếu chi',
    );

    const STATUS_NEW = 1;
    const STATUS_APPROVED = 2;
    const STATUS_ACCOUNTING = 3;
    const STATUS_PAYMENT = 4;
    const STATUS_INAPPROVED = 5;

    protected $statuses = array(
    	self::STATUS_NEW => 'Mới',
        self::STATUS_APPROVED => 'Quản lí đã duyệt',
        self::STATUS_ACCOUNTING => 'Kế toán đã hạch toán',
        self::STATUS_PAYMENT => 'Thủ quỹ đã xác nhận',
        self::STATUS_INAPPROVED => 'Không duyệt'
    );

    const ITEM_TYPE_CRM_CONTRACT = 1;

    protected $itemTypes = array(
    	self::ITEM_TYPE_CRM_CONTRACT => 'Hợp đồng khách hàng'
    );

    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $companyId;
    /**
     * @var int
     */
    protected $type;
    /**
     * @var int
     */
    protected $description;
    /**
     * @var int
     */
    protected $status;
    /**
     * @var int
     */
    protected $applyDate;
    /**
     * @var int
     */
    protected $amount;
    /**
     * @var int
     */
    protected $createdDate;
    /**
     * @var int
     */
    protected $createdById;
    /**
     * @var int
     */
    protected $createdTime;
    /**
     * @var int
     */
    protected $approvedById;
    /**
     * @var int
     */
    protected $approvedDateTime;
    /**
     * @var int
     */
    protected $accountingById;
    /**
     * @var int
     */
    protected $accountingDateTime;
    /**
     * @var int
     */
    protected $paymentById;
    /**
     * @var int
     */
    protected $paymentDateTime;

    /**
     * @var int
     */
    protected $itemType;
    /**
     * @var int
     */
    protected $itemId;

	/**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * @return the $companyId
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

	/**
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

	/**
     * @return the $description
     */
    public function getDescription()
    {
        return $this->description;
    }

	/**
     * @return the $status
     */
    public function getStatus()
    {
        return $this->status;
    }

	/**
     * @return the $createdDate
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

	/**
     * @return the $createdById
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }



	/**
     * @return the $approvedById
     */
    public function getApprovedById()
    {
        return $this->approvedById;
    }

	/**
     * @return the $approvedDateTime
     */
    public function getApprovedDateTime()
    {
        return $this->approvedDateTime;
    }

	/**
     * @return the $accountingById
     */
    public function getAccountingById()
    {
        return $this->accountingById;
    }

	/**
     * @return the $accountingDateTime
     */
    public function getAccountingDateTime()
    {
        return $this->accountingDateTime;
    }

	/**
     * @return the $paymentById
     */
    public function getPaymentById()
    {
        return $this->paymentById;
    }

	/**
     * @return the $paymentDateTime
     */
    public function getPaymentDateTime()
    {
        return $this->paymentDateTime;
    }

	/**
     * @param number $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

	/**
     * @param number $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

	/**
     * @param number $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

	/**
     * @param number $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

	/**
     * @param number $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

	/**
     * @param number $createdDate
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;
    }

	/**
     * @param number $createdById
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;
    }



	/**
     * @param number $approvedById
     */
    public function setApprovedById($approvedById)
    {
        $this->approvedById = $approvedById;
    }

	/**
     * @param number $approvedDateTime
     */
    public function setApprovedDateTime($approvedDateTime)
    {
        $this->approvedDateTime = $approvedDateTime;
    }

	/**
     * @param number $accountingById
     */
    public function setAccountingById($accountingById)
    {
        $this->accountingById = $accountingById;
    }

	/**
     * @param number $accountingDateTime
     */
    public function setAccountingDateTime($accountingDateTime)
    {
        $this->accountingDateTime = $accountingDateTime;
    }

	/**
     * @param number $paymentById
     */
    public function setPaymentById($paymentById)
    {
        $this->paymentById = $paymentById;
    }

	/**
     * @param number $paymentDateTime
     */
    public function setPaymentDateTime($paymentDateTime)
    {
        $this->paymentDateTime = $paymentDateTime;
    }
	/**
     * @return the $applyDate
     */
    public function getApplyDate()
    {
        return $this->applyDate;
    }

	/**
     * @param number $applyDate
     */
    public function setApplyDate($applyDate)
    {
        $this->applyDate = $applyDate;
    }
	/**
     * @return the $types
     */
    public function getTypes()
    {
        return $this->types;
    }

	/**
     * @return the $statuses
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    public function getStatusName($status = null){
        $status = $status?:$this->getStatus();
        if($status && isset($this->statuses[$status])){
            return $this->statuses[$status];
        }
        return '';
    }

    public function getTypeName($type = null){
        $type = $type?:$this->getType();
        if($type && isset($this->types[$type])){
            return $this->types[$type];
        }
        return '';
    }

	/**
     * @return the $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

	/**
     * @return the $createdTime
     */
    public function getCreatedTime()
    {
        return $this->createdTime;
    }

	/**
     * @return the $itemType
     */
    public function getItemType()
    {
        return $this->itemType;
    }

	/**
     * @return the $itemId
     */
    public function getItemId()
    {
        return $this->itemId;
    }

	/**
     * @param number $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

	/**
     * @param number $createdTime
     */
    public function setCreatedTime($createdTime)
    {
        $this->createdTime = $createdTime;
    }

	/**
     * @param number $itemType
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
    }

	/**
     * @param number $itemId
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    public function toFormValues(){
        $data =  array(
            'id'                 => $this->getId(),
            'companyId'          =>  $this->getCompanyId(),
            'type'               =>  $this->getType(),
            'applyDate'          =>  DateBase::toDisplayDate($this->getApplyDate())?:null,
            'amount'             =>  $this->getAmount()?:null,
            'description'        =>  $this->getDescription()?:null,
            'status'             =>  $this->getStatus(),
            'itemType'           =>  $this->getItemType()?:null,
            'itemId'             =>  $this->getItemId()?:null,
        );
        $items = [];
        if($this->getOption('items')){
            foreach ($this->getOption('items') as $item){
                /*@var  $item \Accounting\Model\Transaction\Item */
                $items[] = $item->toFormValues();
            }
        }
        $data['items'] = $items;
        return $data;
    }
}