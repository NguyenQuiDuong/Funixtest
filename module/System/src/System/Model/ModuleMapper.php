<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model;

use Home\Model\BaseMapper;

class ModuleMapper extends BaseMapper{
	/**
	 * @var string
	 */
	protected $tableName = 'system_modules';

	CONST TABLE_NAME = 'system_modules';

	/**
	 * @param \System\Model\Module $item
	 * @return true|false
	 */
	public function save($item){
		$data = array(
			'name' => $item->getName(),
			'description' => $item->getDescription()?:null,
			'status' => $item->getStatus(),
			'createdById' => $item->getCreatedById(),
			'createdDateTime' => $item->getCreatedDateTime(),
			'updatedDateTime' => $item->getUpdatedDateTime()?:null,
		);
		$results = false;
		$dbAdapter = $this->getDbAdapter();
		if (null === ($id = $item->getId())) {
			$insert = $this->getDbSql()->insert(self::TABLE_NAME);
			$insert->values($data);
			$query = $this->getDbSql()->buildSqlString($insert);
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
			$item->setId($dbAdapter->getDriver()->getLastGeneratedValue());
		}
		else {
			$update = $this->getDbSql()->update(self::TABLE_NAME);
			$update->set($data);
			$update->where(array("id"=> (int)$item->getId()));
			$selectString = $this->getDbSql()->buildSqlString($update);
			$results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
		}
		return $results;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Module $item
	 */
	public function isExisted($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getName()){
			return false;
		}
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		$select->where([
			'name' => $item->getName()
		]);
		if($item->getId()){
			$select->where(['id != ?' => $item->getId()]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$result = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($result->count() == 1) {
			$item->exchangeArray((array)$result->current());
			return true;
		}
		return false;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Module $module
	 */
	public function get($module){
		$dbAdapter = $this->getDbAdapter($module);
		if(!$module->getId() && !$module->getName()){
			return null;
		}
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($module->getId()){
			$select->where(['id' => $module->getId()]);
		}
		if($module->getName()){
			$select->where(['name' => $module->getName()]);
		}
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			$module->exchangeArray((array)$rows->current());
			return $module;
		}
		return null;
	}

	/**
	 * @author KienNN
	 * @param string $name
	 */
	public function suggestUri($name=''){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($name){
			$select->where(['name LIKE ?' => $name.'%']);
		}
		$select->order(['name']);
		$select->limit(20);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$result = [];
		if($rows->count()){
			foreach ($rows as $row){
				$result[] = array('label' => '/'.$row['name']);
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Module $item
	 */
	public function fetchAll($item){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($item->getName()){
			$select->where(['name' => $item->getName()]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$result = [];
		if($rows->count()){
			foreach ($rows as $row){
				$row = (array) $row;
				$module = new \System\Model\Module();
				$module->exchangeArray($row);
				$result[] = $module;
			}
		}
		return $result;

	}
}