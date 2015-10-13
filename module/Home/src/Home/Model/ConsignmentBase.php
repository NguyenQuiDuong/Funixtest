<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class ConsignmentBase implements ServiceLocatorAwareInterface{
	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}

	/**
	 * @var array<string>
	 */
	protected $keyValues;
	/**
	 * @var array<string>
	 */
	protected $keys;

	/**
	 * @var array<string>
	 */
	protected $keyDescriptions;

	/**
	 * @var array<string>
	 */
	protected $usingKeys;

	/**
	 * @return the $keyDescriptions
	 */
	public function getKeyDescriptions() {
		return $this->keyDescriptions;
	}

	/**
	 * @param array<string> $keyDescriptions
	 */
	public function setKeyDescriptions($keyDescriptions) {
		$this->keyDescriptions = $keyDescriptions;
	}

	/**
	 * @return the $keys
	 */
	public function getKeys() {
		return $this->keys;
	}

	/**
	 * @param array<string> $keys
	 */
	public function setKeys($keys) {
		$this->keys = $keys;
	}

	/**
	 * @return the $keyValues
	 */
	public function getKeyValues() {
		return $this->keyValues;
	}
	public function getKeyLabel($key){
		if(isset($this->keyDescriptions[$key])){
			return $this->keyDescriptions[$key];
		}
		return $key;
	}
	/**
	 * @param array<string> $keyValues
	 */
	public function setKeyValues($keyValues) {
		$this->keyValues = $keyValues;
	}

	public function getUsingKeys($content) {

		$usingKey = array();
		foreach ($this->keys as $variable){
			if(strpos($content, "{".$variable."}") !== false) {
				$usingKey[] = $variable;
			}
		}
		$this->usingKeys = $usingKey;
		return $usingKey;
	}

	/**
	 * @param array $content
	 */
	public function replaceContent($content) {
		foreach ($this->usingKeys as $item) {
			if(isset($this->keyValues[$item]) && $this->keyValues[$item]!= "") {
				$content = str_replace("{".$item."}", $this->keyValues[$item], $content);
			} else {
				$content = str_replace("{".$item."}", "_______________________________________", $content);
			}
		}
		return $content;
	}

	public function replaceKeyContent($content) {
		foreach ($this->usingKeys as $item) {
			if(isset($this->keyValues[$item]) && $this->keyValues[$item]!= "") {
				$content = str_replace("{".$item."}", $this->keyValues[$item], $content);
			} else {
				$content = str_replace("{".$item."}", "....................................", $content);
			}
		}
		return $content;
	}
}