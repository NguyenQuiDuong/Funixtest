<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model\Action;

use Home\Model\Base;

class Dependency extends Base{
	/**
	 * @var int
	 */
	protected $actionId;
	/**
	 * @var int
	 */
	protected $dependencyId;
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
	 * @return the $actionId
	 */
	public function getActionId() {
		return $this->actionId;
	}

	/**
	 * @return the $dependencyId
	 */
	public function getDependencyId() {
		return $this->dependencyId;
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
	 * @param number $actionId
	 */
	public function setActionId($actionId) {
		$this->actionId = $actionId;
	}

	/**
	 * @param number $dependencyId
	 */
	public function setDependencyId($dependencyId) {
		$this->dependencyId = $dependencyId;
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