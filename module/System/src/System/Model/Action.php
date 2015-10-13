<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model;

use Home\Model\Base;

class Action extends Base{
	const STATUS_ACTIVE = 1;
	const STATUS_INACTIVE = 2;

	const DISPLAY_ACTIVE = 1;
	const DISPLAY_INACTIVE = 2;

	protected $statuses = array(
		self::STATUS_ACTIVE => 'Hoạt động',
		self::STATUS_INACTIVE => 'Không hoạt động'
	);

	protected $displayes = array(
		self::DISPLAY_ACTIVE => 'Hiển thị',
		self::DISPLAY_INACTIVE => 'Không hiển thị'
	);

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var int
	 */
	protected $controllerId;
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
	protected $display;
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
	 * @return the $controllerId
	 */
	public function getControllerId() {
		return $this->controllerId;
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
	 * @return the $display
	 */
	public function getDisplay() {
		return $this->display;
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
	 * @param number $controllerId
	 */
	public function setControllerId($controllerId) {
		$this->controllerId = $controllerId;
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
	 * @param number $display
	 */
	public function setDisplay($display) {
		$this->display = $display;
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
	/**
	 * @return the $statuses
	 */
	public function getStatuses() {
		return $this->statuses;
	}

	/**
	 * @return the $displayes
	 */
	public function getDisplayes() {
		return $this->displayes;
	}

	public function getStatusName($status = null){
		$status = $status?:$this->status;
		if(isset($this->statuses[$status])){
			return $this->statuses[$status];
		}
		return '';
	}

	public function getDisplayName($display = null){
		$display = $display?:$this->display;
		if(isset($this->displayes[$display])){
			return $this->displayes[$display];
		}
		return '';
	}

	public function toStdClass(){
		$std = new \stdClass();
		$std->id = $this->getId();
		$std->name = $this->getName();
		$std->description = $this->getDescription();
		$std->label = $this->getName();
		$std->uri = $this->getOption('uri');
		return $std;
	}
}
