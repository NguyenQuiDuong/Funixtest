<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model;

use Home\Model\Base;

class Module extends Base{
	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var int
	 */
	protected $name;
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
	protected $createdById;
	/**
	 * @var int
	 */
	protected $createdDateTime;
	/**
	 * @var int
	 */
	protected $updatedDateTime;
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return the $status
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @return the $createdById
	 */
	public function getCreatedById() {
		return $this->createdById;
	}

	/**
	 * @return the $createdDateTime
	 */
	public function getCreatedDateTime() {
		return $this->createdDateTime;
	}

	/**
	 * @return the $updatedDateTime
	 */
	public function getUpdatedDateTime() {
		return $this->updatedDateTime;
	}

	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param number $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @param number $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param number $status
	 */
	public function setStatus($status) {
		$this->status = $status;
	}

	/**
	 * @param number $createdById
	 */
	public function setCreatedById($createdById) {
		$this->createdById = $createdById;
	}

	/**
	 * @param number $createdDateTime
	 */
	public function setCreatedDateTime($createdDateTime) {
		$this->createdDateTime = $createdDateTime;
	}

	/**
	 * @param number $updatedDateTime
	 */
	public function setUpdatedDateTime($updatedDateTime) {
		$this->updatedDateTime = $updatedDateTime;
	}

}
