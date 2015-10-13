<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model\Action;

use Home\Model\BaseMapper;

class DependencyMapper extends BaseMapper{
	CONST TABLE_NAME = 'system_action_dependencies';

	/**
	 * @param \System\Model\Action\Dependency $item
	 * @return true|false
	 */
	public function save($item){
		$data = array(
			'actionId' => $item->getActionId(),
			'dependencyId' => $item->getDependencyId(),
			'createdById' => $item->getCreatedById(),
			'createdDateTime' => $item->getCreatedDateTime(),
			'updatedDateTime' => $item->getUpdatedDateTime()?:null,
		);
		$results = false;
		$dbAdapter = $this->getDbAdapter();
		$itemClone = clone $item;
		if (!$this->isExisted($itemClone)) {
			$insert = $this->getDbSql()->insert(self::TABLE_NAME);
			$insert->values($data);
			$query = $this->getDbSql()->buildSqlString($insert);
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		} else {
			$update = $this->getDbSql()->update(self::TABLE_NAME);
			$update->set($data);
			$update->where(array(
				"actionId"=> (int)$item->getActionId(),
				'dependencyId' => (int)$item->getDependencyId()
			));
			$selectString = $this->getDbSql()->buildSqlString($update);
			$results = $dbAdapter->query($selectString, $dbAdapter::QUERY_MODE_EXECUTE);
		}
		return $results;
	}

	/**
	 *
	 * @param \System\Model\Action\Dependency $item
	 */
	public function isExisted($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getActionId() || !$item->getDependencyId()){
			return false;
		}
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		$select->where([
				'actionId' => $item->getActionId(),
				'dependencyId' => $item->getDependencyId()
				]);
		$query = $this->getDbSql()->buildSqlString($select);
		$result = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($result->count() == 1) {
			$item->exchangeArray((array) $result->current());
			return true;
		}
		return false;
	}

	public function fetchAllDependency($actionId){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(['sa' => \System\Model\Action\DependencyMapper::TABLE_NAME]);
		$select->where(['sa.actionid' => $actionId]);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$actionIds = [];
		if($rows->count()){
			foreach ($rows as $row){
				$actionIds[$row['dependencyId']] = $row['dependencyId'];
			}
		}
		$result = [];
		if(count($actionIds)){
			$select = $this->getDbSql()->select(['a' => \System\Model\ActionMapper::TABLE_NAME]);
			$select->join(['sc' => \System\Model\ControllerMapper::TABLE_NAME], 'a.controllerId = sc.id',[
					'sc.name' => 'name'
					]);
			$select->join(['sm' => \System\Model\ModuleMapper::TABLE_NAME], 'sc.moduleId = sm.id',[
					'sm.name' => 'name'
					]);
			$select->where(['a.id' => $actionIds]);
			$query = $this->getDbSql()->buildSqlString($select);
			$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
			if($rows->count()){
				foreach ($rows as $row){
					$action = new \System\Model\Action();
					$action->exchangeArray((array) $row);
					$action->addOption('uri', '/'.$row['sm.name'].'/'.$row['sc.name'].'/'.$row['name']);
					$result[] = $action;
				}
			}
		}
		return $result;
	}

	public function delete($actionId = null, $dependencyId = null){
		if(!$actionId && !$dependencyId){
			return false;
		}
		$dbAdapter = $this->getDbAdapter();
		$delete = $this->getDbSql()->delete(self::TABLE_NAME);
		if($actionId){
			$delete->where([
				'actionId' => $actionId
			]);
		}
		if($dependencyId){
			$delete->where([
				'dependencyId' => $dependencyId
			]);
		}
		$deleteString = $this->getDbSql()->buildSqlString($delete);
		$results = $dbAdapter->query($deleteString, $dbAdapter::QUERY_MODE_EXECUTE);
		return $results;
	}
}