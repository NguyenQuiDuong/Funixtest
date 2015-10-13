<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Accounting\Model\Transaction;

use Home\Model\Base;

class Item extends Base
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var int
     */
    protected $transactionId;
    /**
     * @var int
     */
    protected $date;
    /**
     * @var int
     */
    protected $amount;
    /**
     * @var int
     */
    protected $debitAccountId;
    /**
     * @var int
     */
    protected $creditAccountId;
    /**
     * @var int
     */
    protected $itemType;
    /**
     * @var int
     */
    protected $itemId;
    /**
     * @var int
     */
    protected $description;
    /**
     * @var int
     */
    protected $status;
	/**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * @return the $transactionId
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

	/**
     * @return the $date
     */
    public function getDate()
    {
        return $this->date;
    }

	/**
     * @return the $amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

	/**
     * @return the $debitAccountId
     */
    public function getDebitAccountId()
    {
        return $this->debitAccountId;
    }

	/**
     * @return the $creditAccountId
     */
    public function getCreditAccountId()
    {
        return $this->creditAccountId;
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
     * @param number $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

	/**
     * @param number $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

	/**
     * @param number $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

	/**
     * @param number $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

	/**
     * @param number $debitAccountId
     */
    public function setDebitAccountId($debitAccountId)
    {
        $this->debitAccountId = $debitAccountId;
    }

	/**
     * @param number $creditAccountId
     */
    public function setCreditAccountId($creditAccountId)
    {
        $this->creditAccountId = $creditAccountId;
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

    public function toFormValues(){
        return array(
        	'id' => $this->getId(),
            'transactionId'          =>  $this->getTransactionId(),
            'date'          =>  $this->getDate(),
            'amount'          =>  $this->getAmount(),
            'debitAccountId'          =>  $this->getDebitAccountId()?:null,
            'creditAccountId'          =>  $this->getCreditAccountId()?:null,
            'itemType'          =>  $this->getItemType()?:null,
            'itemId'          =>  $this->getItemId()?:null,
            'description'          =>  $this->getDescription()?:null,
            'status'          =>  $this->getStatus(),
        );
    }

}