<?php

namespace Expert\Model\Expert;

use Home\Model\Base;

class Subject extends Base
{

    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $expertId;

    /**
     * @var string
     */
    protected $subjectId;

    /**
     * @var int
     */
    protected $createdById;

    /**
     * @var string
     */
    protected $createdDateTime;

    /**
     * @return int
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
     * @return int
     */
    public function getExpertId()
    {
        return $this->expertId;
    }

    /**
     * @param int $expertId
     */
    public function setExpertId($expertId)
    {
        $this->expertId = $expertId;
    }

    /**
     * @return string
     */
    public function getSubjectId()
    {
        return $this->subjectId;
    }

    /**
     * @param string $subjectId
     */
    public function setSubjectId($subjectId)
    {
        $this->subjectId = $subjectId;
    }

    /**
     * @return int
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
     * @return string
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


    public function toFormValues(){
        $data = array(
            'id' => $this->getId(),
            'expertId' => $this->getName(),
            'subjectId' => $this->getCategoryId(),
            'description' => $this->getDescription(),

        );
        return $data;
    }

}