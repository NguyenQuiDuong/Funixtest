<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model;

use Home\Model\BaseMapper;

class RoleMapper extends BaseMapper{
	CONST TABLE_NAME = 'system_roles';

	/**
	 * @param \System\Model\Role $item
	 * @return true|false
	 */
	public function save($item){
		$data = array(
			'name' => $item->getName(),
			'createdById' => $item->getCreatedById(),
			'createdDateTime' => $item->getCreatedDateTime(),
		);
		$results = false;
		$dbAdapter = $this->getDbAdapter();
		if (null === ($id = $item->getId())) {
			$insert = $this->getDbSql()->insert(self::TABLE_NAME);
			$insert->values($data);
			$query = $this->getDbSql()->buildSqlString($insert);
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
			$item->setId($dbAdapter->getDriver()->getLastGeneratedValue());
		} else {
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
	 * @param \System\Model\Role $item
	 */
	public function get($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getId()){
			return  null;
		}
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($item->getId()){
			$select->where(['id' => $item->getId()]);
		}
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			$item->exchangeArray((array)$rows->current());
			return $item;
		}
		return null;
	}

	/**
	 *
	 * @param \System\Model\Role $item
	 */
	public function isExisted($item){
		$dbAdapter = $this->getDbAdapter();
		if($item->getName()){
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
			$item->exchangeArray((array) $result->current());
			return true;
		}
		return false;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Role $item
	 */
	public function fetchAll($item){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($item->getOption('names')){
			$select->where(['name' => $item->getOption('names')]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$result = [];
		if($rows->count()){
			foreach ($rows as $row){
				$role = new \System\Model\Role();
				$role->exchangeArray((array) $row);
				$result[] = $role;
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Role $item
	 */
	public function isRoleUsed($item){
		if(!$item->getId()){
			return null;
		}
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(\User\Model\UserMapper::TABLE_NAME);
		$select->where(['role' => $item->getId()]);
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			return true;
		}
		$select = $this->getDbSql()->select(\System\Model\Role\FeatureMapper::TABLE_NAME);
		$select->where(['role' => $item->getId()]);
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			return true;
		}
		return false;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Role $item
	 */
	public function delete($item){
		if(!$item->getId()){
			return false;
		}
		$dbAdapter = $this->getDbAdapter();
		$delete = $this->getDbSql()->delete(self::TABLE_NAME);
		$delete->where(['id' => $item->getId()]);
		$query = $this->getDbSql()->buildSqlString($delete);
		$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		return $results;
	}


}