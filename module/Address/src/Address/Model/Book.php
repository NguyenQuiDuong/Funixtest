<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Model;

use Home\Model\Base;

class Book extends Base {

	/**
	 * @var int
	 */
    protected $id;

    /**
     * @var int
     */
    protected $cityId;

    /**
     * @var int
     */
    protected $districtId;

    /**
     * @var int
     */
    protected $wardld;

    /**
     * @var int
     */
    protected $shippingZoneld;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $mobile;

    /**
     * @var int
     */
    protected $bankId;

    /**
     * @var string
     */
    protected $bankBranch;

    /**
     * @var string
     */
    protected $bankAccountNumber;

    /**
     * @var string
     */
    protected $bankAccountHolder;

    /**
     * @var string
     */
    protected $note;

    /**
     * @var int
     */
    protected $createdById;

    /**
     * @var \DateTime
     */
    protected $createdDateTime;

	/**
	 * @var string
	 */
    protected  $cityName;

    /**
     * @var string
     */
    protected  $districtName;

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $bankAccountHolder
     */
    public function setBankAccountHolder($bankAccountHolder)
    {
        $this->bankAccountHolder = $bankAccountHolder;
    }

    /**
     * @return string
     */
    public function getBankAccountHolder()
    {
        return $this->bankAccountHolder;
    }

    /**
     * @param string $bankAccountNumber
     */
    public function setBankAccountNumber($bankAccountNumber)
    {
        $this->bankAccountNumber = $bankAccountNumber;
    }

    /**
     * @return string
     */
    public function getBankAccountNumber()
    {
        return $this->bankAccountNumber;
    }

    /**
     * @param string $bankBranch
     */
    public function setBankBranch($bankBranch)
    {
        $this->bankBranch = $bankBranch;
    }

    /**
     * @return string
     */
    public function getBankBranch()
    {
        return $this->bankBranch;
    }

    /**
     * @param int $bankId
     */
    public function setBankId($bankId)
    {
        $this->bankId = $bankId;
    }

    /**
     * @return int
     */
    public function getBankId()
    {
        return $this->bankId;
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param mixed $cityName
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;
    }

    /**
     * @return mixed
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * @param int $createdById
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;
    }

    /**
     * @return int
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

    /**
     * @param \DateTime $createdDateTime
     */
    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

    /**
     * @param int $districtId
     */
    public function setDistrictId($districtId)
    {
        $this->districtId = $districtId;
    }

    /**
     * @return int
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * @param string $districtName
     */
    public function setDistrictName($districtName)
    {
        $this->districtName = $districtName;
    }

    /**
     * @return string
     */
    public function getDistrictName()
    {
        return $this->districtName;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param int $shippingZoneld
     */
    public function setShippingZoneld($shippingZoneld)
    {
        $this->shippingZoneld = $shippingZoneld;
    }

    /**
     * @return int
     */
    public function getShippingZoneld()
    {
        return $this->shippingZoneld;
    }

    /**
     * @param int $wardld
     */
    public function setWardld($wardld)
    {
        $this->wardld = $wardld;
    }

    /**
     * @return int
     */
    public function getWardld()
    {
        return $this->wardld;
    }
}