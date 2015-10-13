<?php

namespace Subject\Model;

use Home\Model\Base;
use Home\Model\DateBase;

class Subject extends Base
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;
    
    /**
     * @var int
     */
    protected $categoryId;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $createdById;

    /**
     * @var string
     */
    protected $createdDateTime;
    
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
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

 /**
     * @return the $name
     */
    public function getName()
    {
        return $this->name;
    }

 /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

 /**
     * @return the $categoryId
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

 /**
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

 /**
     * @return the $description
     */
    public function getDescription()
    {
        return $this->description;
    }

 /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

 /**
     * @return the $createdById
     */
    public function getCreatedById()
    {
        return $this->createdById;
    }

 /**
     * @param int $createdById
     */
    public function setCreatedById($createdById)
    {
        $this->createdById = $createdById;
    }

 /**
     * @return the $createdDateTime
     */
    public function getCreatedDateTime()
    {
        return $this->createdDateTime;
    }

 /**
     * @param string $createdDateTime
     */
    public function setCreatedDateTime($createdDateTime)
    {
        $this->createdDateTime = $createdDateTime;
    }
    
    /**
     * @return the $status
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * @param int $createdById
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function toFormValues(){
        $data = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'categoryId' => $this->getCategoryId(),
            'description' => $this->getDescription(),
            	
        );
        return $data;
    }

}