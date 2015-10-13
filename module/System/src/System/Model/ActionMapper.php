<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Model;

use Home\Model\BaseMapper;

class ActionMapper extends BaseMapper{
	/**
	 * @var string
	 */
	protected $tableName = 'system_actions';

	CONST TABLE_NAME = 'system_actions';

	/**
	 * @param \System\Model\Action $item
	 * @return true|false
	 */
	public function save($item){
		$data = array(
			'controllerId' => $item->getControllerId(),
			'name' => $item->getName(),
			'description' => $item->getDescription()?:null,
			'status' => $item->getStatus(),
			'display' => $item->getDisplay(),
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
	 * @param \System\Model\Action $item
	 */
	public function isExisted($item){
		$dbAdapter = $this->getDbAdapter();
		if(!$item->getControllerId() || !$item->getName()){
			return false;
		}
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		$select->where([
			'controllerId' => $item->getControllerId(),
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
	 * @param \System\Model\Action $item
	 */
	public function fetchAdminGridView($item){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($item->getStatus()){
			$select->where(['status' => $item->getStatus()]);
		}
		if($item->getDisplay()){
			$select->where(['display' => $item->getDisplay()]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$actions = [];
		$usersIds = [];
		if($rows->count()){
			foreach ($rows as $row){
				$actions[$row['controllerId']][$row['id']] = $row;
				if($row['createdById'] && !isset($usersIds[$row['createdById']])){
					$usersIds[$row['createdById']] = $row['createdById'];
				}

			}
		}
		unset($rows);
		$select = $this->getDbSql()->select(\System\Model\ControllerMapper::TABLE_NAME);
		if($item->getOption('controllerStatus')){
			$select->where(['status' => $item->getOption('controllerStatus')]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$controllers = [];
		if($rows->count()){
			foreach ($rows as $row){
				$controllers[$row['moduleId']][$row['id']] = $row;
				if($row['createdById'] && !isset($usersIds[$row['createdById']])){
					$usersIds[$row['createdById']] = $row['createdById'];
				}
			}
		}
		unset($rows);
		$select = $this->getDbSql()->select(\System\Model\ModuleMapper::TABLE_NAME);
		if($item->getOption('moduleStatus')){
			$select->where(['status' => $item->getOption('moduleStatus')]);
		}
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$modules = [];
		if($rows->count()){
			foreach ($rows as $row){
				$modules[$row['id']] = $row;
				if($row['createdById'] && !isset($usersIds[$row['createdById']])){
					$usersIds[$row['createdById']] = $row['createdById'];
				}
			}
		}
		unset($rows);
		$users = [];
		if(count($usersIds)){
			$select = $this->getDbSql()->select(\User\Model\UserMapper::TABLE_NAME);
			$select->where(['id' => $usersIds]);
			$query = $this->getDbSql()->buildSqlString($select);
			$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
			if($rows->count()){
				foreach ($rows as $row){
					$users[$row['id']] = $row['fullName'];
				}
			}
			unset($rows);
		}
		$result = [
			'modules' => [],
			'controllers' => [],
			'actions' => []
		];
		foreach ($actions as $controllerId => $controlerActions){
			foreach ($controlerActions as $action){
				if(isset($users[$action['createdById']])){
					$action['createdBy'] = $users[$action['createdById']];
				}
				$result['actions'][$controllerId][$action['id']] = $action;
			}
		}
		foreach ($controllers as $moduleId => $moduleControllers){
			foreach ($moduleControllers as $controller){
				if(isset($users[$controller['createdById']])){
					$controller['createdBy'] = $users[$controller['createdById']];
				}
				$result['controllers'][$moduleId][$controller['id']] = $controller;
			}
		}
		foreach ($modules as $module){
			if(isset($users[$module['createdById']])){
				$module['createdBy'] = $users[$module['createdById']];
			}
			$result['modules'][$module['id']] = $module;
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Action $action
	 * @return NULL
	 */
	public function get($action){
		if(!$action->getId() && (!$action->getControllerId() || !$action->getName())){
			return null;
		}
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(self::TABLE_NAME);
		if($action->getId()){
			$select->where(['id' => $action->getId()]);
		}
		if($action->getControllerId() && $action->getName()){
			$select->where([
				'controllerId' => $action->getControllerId(),
				'name' => $action->getName()
			]);
		}
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			$action->exchangeArray((array)$rows->current());
			return $action;
		}
		return null;
	}

	/**
	 * @author KienNN
	 */
	public function suggestUri($controllerId, $name = ''){
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(['sa' => self::TABLE_NAME]);
		$select->join(['sc' => \System\Model\ControllerMapper::TABLE_NAME], 'sa.controllerId = sc.id',[
			'sc.name' => 'name'
		]);
		$select->join(['sm' => \System\Model\ModuleMapper::TABLE_NAME], 'sc.moduleId = sm.id',[
			'sm.name' => 'name'
		]);
		$select->where([
			'sa.controllerId' => $controllerId
		]);
		if($name){
			$select->where([
				'sa.name LIKE ?' => $name.'%'
			]);
		}
		$select->order(['sa.name']);
		$select->limit(20);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		$result = [];
		if($rows->count()){
			foreach ($rows as $row){
				$result[] = array('label' => '/'.$row['sm.name'].'/'.$row['sc.name'].'/'.$row['name']);
			}
		}
		return $result;
	}

	/**
	 * @author KienNN
	 * @param \System\Model\Action $item
	 */
	public function getByPath($item){
		if(!$item->getOption('path') && (!$item->getOption('moduleName')
				|| !$item->getOption('controllerName') || !$item->getName())){
			return null;
		}
		$names = [];
		if($item->getOption('path')){
			$path = explode('/', $item->getOption('path'));
			if(count($path) != 4){
				return null;
			}
			$name['module'] = $path[1];
			$name['controller'] = $path[2];
			$name['action'] = $path[3];
		} else {
			$name['module'] = $item->getOption('moduleName');
			$name['controller'] = $item->getOption('controllerName');
			$name['action'] = $item->getName();
		}
		$dbAdapter = $this->getDbAdapter();
		$select = $this->getDbSql()->select(['sa' => self::TABLE_NAME]);
		$select->join(['sc' => \System\Model\ControllerMapper::TABLE_NAME], 'sa.controllerId = sc.id', []);
		$select->join(['sm' => \System\Model\ModuleMapper::TABLE_NAME], 'sc.moduleId=sm.id', []);
		$select->where([
			'sa.name' => $name['action'],
			'sc.name' => $name['controller'],
			'sm.name' => $name['module']
		]);
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($rows->count()){
			$item->exchangeArray((array) $rows->current());
			return $item;

		}
		return null;
	}
}