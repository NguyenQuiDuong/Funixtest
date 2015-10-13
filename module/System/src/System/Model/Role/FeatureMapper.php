<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model\Role;

use Home\Model\BaseMapper;

class FeatureMapper extends BaseMapper{
	CONST TABLE_NAME = 'system_role_features';
	/**
	 * @param \System\Model\Role\Feature $item
	 * @return true|false
	 */
	public function save($item){
		$data = array(
			'roleId' => $item->getRoleId(),
			'actionId' => $item->getActionId(),
			'createdById' => $item->getCreatedById(),
			'createdDateTime' => $item->getCreatedDateTime(),
			'updatedDateTime' => $item->getUpdatedDateTime()?:null
		);
		$results = false;
		$dbAdapter = $this->getDbAdapter();
		if (!$this->isExisted($item)) {
			$insert = $this->getDbSql()->insert(self::TABLE_NAME);
			$insert->values($data);
			$query = $this->getDbSql()->buildSqlString($insert);
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		} else {
			$update = $this->getDbSql()->update(self::TABLE_NAME);
			$update->set($data);
			$update->where([
				'roleId' => $item->getRoleId(),
				'actionId' => $item->getActionId()
				]);
			$selectString = $this->getDbSql()->buildSqlString($update);
			$results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
		}
		return $results;
	}

	/**
	 *
	 * @param \System\Model\Role\Feature $item
	 */
	public function isExisted($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getRoleId() || !$item->getActionId()){
			return false;
		}
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		$select->where([
				'roleId' => $item->getRoleId(),
				'actionId' => $item->getActionId()
				]);
		$query = $this->getDbSql()->buildSqlString($select);
		$result = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($result->count() == 1) {
			$item->exchangeArray($result->current());
			return true;
		}
		return false;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Role\Feature $item
	 */
	public function fetchArrayMode($item){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$result = [];
		if($rows->count()){
			foreach ($rows as $row){
				$row = (array) $row;
				$result[$row['actionId']][$row['roleId']] = 1;
			}
		}
		return $result;
	}

	/**
	 *
	 * @param \System\Model\Role\Feature $item
	 */
	public function delete($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getRoleId() || !$item->getActionId()){
			return false;
		}
		$delete = $this->getDbSql()->delete(self::TABLE_NAME);
		$delete->where(['roleId' => $item->getRoleId(),
			'actionId' => $item->getActionId()]);
		$query = $this->getDbSql()->buildSqlString($delete);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		return true;
	}
}