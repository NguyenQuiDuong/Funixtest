<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Accounting\Model;

use Home\Model\Base;

class Account extends Base
{
    const ID_CASH = 1;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    protected $statuses = array(
        self::STATUS_ACTIVE => 'Hoạt động',
        self::STATUS_INACTIVE => 'Không hoạt động',
    );

    const TYPE_BANK             = 1;
    const TYPE_PAYMENT_GATEWAY  = 2;
    const TYPE_CASH             = 3;
    const TYPE_INSTALLMENT      = 5;
    const TYPE_DEFAULT          = 6;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $type;
    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var int
     */
    protected $companyId;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $remain;

    /**
     * @var int
     */
    protected $status;


    /**
     * @var int
     */
    protected $createdById;

    /**
     * @var string
     */
    protected $createdDateTime;
	/**
     * @return the $id
     */
    public function getId()
    {
        return $this->id;
    }

	/**
     * @return the $parentId
     */
    public function getParentId()
    {
        return $this->parentId;
    }

	/**
     * @return the $companyId
     */
    public function getCompanyId()
    {
        return $this->companyId;
    }

	/**
     * @return the $code
     */
    public function getCode()
    {
        return $this->code;
    }

	/**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

	/**
     * @return the $status
     */
    public function getStatus()
    {
        return $this->status;
    }

	/**
     * @return the $createdById
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

	/**
     * @return the $createdDateTime
     */
    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

	/**
     * @param number $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

	/**
     * @param number $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

	/**
     * @param number $companyId
     */
    public function setCompanyId($companyId)
    {
        $this->companyId = $companyId;
    }

	/**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

	/**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

	/**
     * @param number $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

	/**
     * @param number $createdById
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;
    }

	/**
     * @param string $createdDateTime
     */
    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }
    public function toFormValues() {
        return array (
            'id'                => $this->getId (),
            'parentId'          => $this->getParentId(),
            'companyId'         => $this->getCompanyId(),
            'code'              => $this->getCode(),
            'name'              => $this->getName(),
            'status'            => $this->getStatus(),
            'createdById'       => $this->getCreatedById(),
            'createdDateTime'   => $this->getCreatedDateTime(),

        );
    }

    private function derecursiveObject($items){
        $result = array();
        if($items && count($items)){
            foreach ($items as $node) {
                $item = $node['obj'];
                $item->addOption('ord', $node['ord']);
                $result[$item->getId()] = $item;
                if(isset($node['childs']) && count($node['childs'])){
                    $result += $this->derecursiveObject($node['childs']);
                }
            }
        }
        return $result;
    }

    public function toRecursivedArray($items){
        return $this->derecursiveObject($this->toRecursivedTree($items));
    }

    public function toRecursivedTree($items){
        if(!$items || !count($items)){
            return [];
        }
        $arrayIndexed = [];
        foreach ($items as $item){
            $arrayIndexed[$item->getId()] = $item;
        }
        $result = $this->recursiveObject($arrayIndexed);
        return $result;
    }

    private function recursiveObject($items, $parentId = 0, $ord = 0){
        $result = array();
        if($items && count($items)){
            foreach ($items as $item) {
                $current_parent = $item->getParentId()?:0;
                if($current_parent == $parentId) {
                    unset($items[$item->getId()]);
                    $result[] =	array(
                        'ord' => $ord,
                        'obj' => $item,
                        'childs' => $this->recursiveObject($items, $item->getId(), $ord+1),
                    );
                }
            }
        }
        return $result;
    }
    public function prepairSuggest($q=null){
        $this->setName($q?:$this->getName());
    }
	/**
     * @return the $type
     */
    public function getType()
    {
        return $this->type;
    }

	/**
     * @return the $remain
     */
    public function getRemain()
    {
        return $this->remain;
    }

	/**
     * @param number $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

	/**
     * @param string $remain
     */
    public function setRemain($remain)
    {
        $this->remain = $remain;
    }

}