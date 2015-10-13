<?php

namespace Subject\Model\Subject;

use Home\Model\Base;
use Home\Model\DateBase;

class Category extends Base
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

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
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function toFormValues(){
        $data = array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            	
        );
        return $data;
    }

}