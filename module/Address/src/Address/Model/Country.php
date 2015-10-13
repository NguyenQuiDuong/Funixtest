<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Model;
use Home\Model\Base;

class Country extends Base
{
	/**
	 * @var int
	 */
	protected $id;
	
	/**
	 * @var string
	 */
	protected $code;
	
	/**
	 * @var string
	 */
	protected $telephoneCode;
	
	/**
	 * @var string
	 */
	protected $name;
	
	/**
	 * @var string
	 */
	protected $englishName;
	
	/**
	 * @var string
	 */
	protected $nativeName;
	
	/**
	 * @var string
	 */
	protected $searchTerm;
	
	/**
	 * @var double
	 */
	protected $latitude;
	
	/**
	 * @var double
	 */
	protected $longitude;
	
	/**
	 * @var int
	 */
	protected $order;
	
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param number $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return the $code
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $code
	 */
	public function setCode($code) {
		$this->code = $code;
	}

	/**
	 * @return the $telephoneCode
	 */
	public function getTelephoneCode() {
		return $this->telephoneCode;
	}

	/**
	 * @param string $telephoneCode
	 */
	public function setTelephoneCode($telephoneCode) {
		$this->telephoneCode = $telephoneCode;
	}

	/**
	 * @return the $name
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name) {
		$this->name = $name;
	}

	/**
	 * @return the $englishName
	 */
	public function getEnglishName() {
		return $this->englishName;
	}

	/**
	 * @param string $englishName
	 */
	public function setEnglishName($englishName) {
		$this->englishName = $englishName;
	}

	/**
	 * @return the $nativeName
	 */
	public function getNativeName() {
		return $this->nativeName;
	}

	/**
	 * @param string $nativeName
	 */
	public function setNativeName($nativeName) {
		$this->nativeName = $nativeName;
	}

	/**
	 * @return the $searchTerm
	 */
	public function getSearchTerm() {
		return $this->searchTerm;
	}

	/**
	 * @param string $searchTerm
	 */
	public function setSearchTerm($searchTerm) {
		$this->searchTerm = $searchTerm;
	}

	/**
	 * @return the $latitude
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * @param number $latitude
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * @return the $longitude
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * @param number $longitude
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * @return the $order
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * @param number $order
	 */
	public function setOrder($order) {
		$this->order = $order;
	}	
}













