<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\DataGrid\Feature;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;

class Role extends DataGrid{
	public function init(){
		$this->addAttributes(array(
			'id' => 'dgSystemRoleFeature'
		));
		$roles = $this->getOption('roles')?:[];
		$features = $this->getOption('features')?:[];

		$roleCols = [];
		$userModel = new \User\Model\User();
		foreach ($roles as $role){
			/*@var $role \System\Model\Role */
			$roleCols[$role->getId()] = $role->getName();
		}

		$header = new Row();
		$header->addColumn(array(
			'name' => 'mcaName',
			'content' => 'Quyền'
		));
		foreach ($roleCols as $roleId => $roleName){
			$header->addColumn(array(
				'name' => 'role_'.$roleId,
				'content' => $roleName
			));
		}
		$this->addHeader($header);

		$mcaList = $this->getOption('mcaList');

		if(!$mcaList || !count($mcaList)
			|| !isset($mcaList['modules']) || !count($mcaList['modules'])
			|| !isset($mcaList['controllers']) || !count($mcaList['controllers'])
			|| !isset($mcaList['actions']) || !count($mcaList['actions'])){
			return $this;
		}

		$modules = $mcaList['modules'];
		$controllers = $mcaList['controllers'];
		$actions = $mcaList['actions'];

		foreach ($modules as $moduleId => $module){

			$row = new Row();
			$row->addColumn(array(
				'name' => 'mcaName',
				'content' => '<b>'.($module['description']?:$module['name']).'</b>',
				'attributes' => array(
					'title' => $module['id'],
					'class' => 'mark mcaName',
				)
			));
			foreach ($roleCols as $roleId => $roleName){
				$row->addColumn(array(
					'name' => 'role_'.$roleId,
					'content' => ''
				));
			}
			$this->addRow($row);
			if(isset($controllers[$module['id']]) && count($controllers[$module['id']])){
				foreach ($controllers[$module['id']] as $controller){
					$row = new Row();
					$row->addColumn(array(
						'name' => 'mcaName',
						'content' => '<b>'.($controller['description']?:$controller['name']).'</b>',
						'attributes' => array(
							'title' => $controller['id'],
							'class' => 'mark mcaName',
							'style' => 'padding-left: 50px;font-weight:bold;'
						)
					));
					foreach ($roleCols as $roleId => $roleName){
						$row->addColumn(array(
							'name' => 'role_'.$roleId,
							'content' => ''
						));
					}
					$this->addRow($row);
					if(isset($actions[$controller['id']]) && count($actions[$controller['id']])){
						foreach ($actions[$controller['id']] as $action){
							$row = new Row();
							$row->addColumn(array(
								'name' => 'mcaName',
								'content' => ($action['description']?:$action['name']),
								'attributes' => array(
									'title' => $action['id'],
									'class' => 'mcaName',
									'style' => 'padding-left: 100px;'
								)
							));
							foreach ($roleCols as $roleId => $roleName){
								$checked = '';
								if(isset($features[$action['id']][$roleId])){
									$checked = 'checked="checked"';
								}
								$content = '<input type="checkbox" class="roleFeature"
										roleid="'.$roleId.'" actionid="'.$action['id'].'" value="1" '.$checked.'/>';
								$row->addColumn(array(
									'name' => 'role_'.$roleId,
									'content' => $content,
									'attributes' => ['class' => 'colControls']
								));
							}
							$this->addRow($row);
						}
					}
				}
			}
		}
	}

}