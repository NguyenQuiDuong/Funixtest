<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model\Role;

use Home\Model\Base;

class Feature extends Base{
	/**
	 * @var int
	 */
	protected $roleId;
	/**
	 * @var int
	 */
	protected $actionId;
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
	 * @return the $roleId
	 */
	public function getRoleId() {
		return $this->roleId;
	}

	/**
	 * @return the $actionId
	 */
	public function getActionId() {
		return $this->actionId;
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
	 * @param number $roleId
	 */
	public function setRoleId($roleId) {
		$this->roleId = $roleId;
	}

	/**
	 * @param number $actionId
	 */
	public function setActionId($actionId) {
		$this->actionId = $actionId;
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