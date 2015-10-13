<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model;

use Home\Model\BaseMapper;

class ControllerMapper extends BaseMapper{
	CONST TABLE_NAME = 'system_controllers';

	/**
	 * @param \System\Model\Controller $item
	 * @return true|false
	 */
	public function save($item){
		$data = array(
			'moduleId' => $item->getModuleId(),
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
	 *
	 * @param \System\Model\Controller $item
	 */
	public function isExisted($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getName() || !$item->getModuleId()){
			return false;
		}
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		$select->where([
				'moduleId' => $item->getModuleId(),
				'name' => $item->getName()
				]);
		if($item->getId()){
			$select->where(['id != ?' => $item->getId()]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$result = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($result->count() == 1) {
			$item->exchangeArray((array) $result->current());
			return true;
		}
		return false;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Controller $controller
	 */
	public function get($controller){
		$dbAdapter = $this->getDbAdapter();
		if(!$controller->getId() && (!$controller->getModuleId() || !$controller->getName())){
			return null;
		}
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($controller->getId()){
			$select->where(['id' => $controller->getId()]);
		}
		if($controller->getModuleId() && $controller->getName()){
			$select->where([
				'moduleId' => $controller->getModuleId(),
				'name' => $controller->getName()
			]);
		}
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			$controller->exchangeArray((array)$rows->current());
			return $controller;
		}
		return null;
	}

	/**
	 *
	 * @param unknown $moduleId
	 * @param string $name
	 * @author KienNN
	 */
	public function suggestUri($moduleId, $name = ''){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(['sc' => self::TABLE_NAME]);
		$select->join(['sm' => \System\Model\ModuleMapper::TABLE_NAME], 'sc.moduleId = sm.id',[
				'sm.name' => 'name'
				]);
		$select->where(['sc.moduleId' => $moduleId]);
		if($name){
			$select->where(['sc.name LIKE ?' => $name.'%']);
		}
		$select->order(['sc.name']);
		$select->limit(20);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$result = [];
		if($rows->count()){
			foreach ($rows as $row){
				$result[] = array('label' => '/'.$row['sm.name'].'/'.$row['name']);
			}
		}
		return $result;
	}
}