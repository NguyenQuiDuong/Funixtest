<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\DataGrid;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;

class Mca extends DataGrid{
	public function init(){
		$this->addAttributes(array(
			'id' => 'dgSystemFeatureMca',
			'class' => 'table-hover'
		));
		$this->addHeader([
			'attributes' => array(
				'id' => 'd'
			),
			'options' => array(),
			'columns' => array(
				array(
					'name' => 'name',
					'content' => 'Tên'
				),
				array(
					'name' => 'description',
					'content' => 'Mô tả'
				),
				array(
					'name' => 'updatedDateTime',
					'content' => '<i class="fa fa-calendar-o"></i>'
				),
				array(
					'name' => 'dependency',
					'content' => '<i class="fa fa-link"></i>'
				),
				array(
					'name' => 'display',
					'content' => 'Hiển thị'
				),
				array(
					'name' => 'accountUsing',
					'content' => 'Account'
				),
				array(
					'name' => 'userUsing',
					'content' => 'User'
				),
				array(
					'name' => 'status',
					'content' => 'Trạng thái'
				),
				array(
					'name' => 'edit',
					'content' => '<i class="fa fa-pencil-square-o"></i>'
				),
				array(
					'name' => 'deploy',
					'content' => '<i class="fa fa-cogs"></i>'
				),
				array(
					'name' => 'remove',
					'content' => '<i class="fa fa-trash-o color-red"></i>'
				),
			)
		]);
		if($this->getDataSource()){
			$dataSource = $this->getDataSource();
			$modules = $dataSource['modules'];
			$controllers = $dataSource['controllers'];
			$actions = $dataSource['actions'];
			if($modules && count($modules)){
				foreach ($modules as $module){
					$row = new Row();
					$row->setAttributes(array(
						'mca_role' => 'module',
						'idref' => $module['id'],
						'class' => '_'.$module['name']
					));
					$row->addColumn(array(
						'name' => 'name',
						'content' => $module['name'],
						'attributes' => array(
							'title' => $module['id'],
							'class' => 'mark mcaName',
							'style' => 'font-weight:bold;'
						)
					));
					$row->addColumn(array(
						'name' => 'description',
						'content' => $module['description'],
						'attributes' => array(
							'title' => $module['id'],
							'class' => 'mark mcaDescription',
							'style' => 'font-weight:bold;'
						)
					));
					$content='';
					if($module['updatedDateTime']){
						$content = '<i class="fa fa fa-calendar-o" title="'.$module['updatedDateTime'].'"></i>';
					}
					$row->addColumn(array(
						'name' => 'updatedDateTime',
						'content' => $content,
					));
					$row->addColumn(array(
						'name' => 'dependency',
						'content' => '',
					));
					$row->addColumn(array(
						'name' => 'display',
						'content' => '',
					));
					$row->addColumn(array(
						'name' => 'accountUsing',
						'content' => '',
					));
					$row->addColumn(array(
						'name' => 'userUsing',
						'content' => '',
					));

					$row->addColumn(array(
						'name' => 'status',
						'content' => $this->renderStatus($module['status']),
						'attributes' => ['class' => 'colControls']
					));
					$row->addColumn(array(
						'name' => 'edit',
						'content' => '<a class="editMca fa fa-pencil-square-o"></a>',
						'attributes' => ['class' => 'colControls']
					));
					$row->addColumn(array(
						'name' => 'deploy',
						'content' => '',
						'attributes' => ['class' => 'colControls']
					));
					$row->addColumn(array(
						'name' => 'remove',
						'content' => '<a class="removeMca fa fa-trash-o color-red"></a>',
						'attributes' => ['class' => 'colControls']
					));
					$this->addRow($row);
					if(isset($controllers[$module['id']]) && count($controllers[$module['id']])){
						foreach ($controllers[$module['id']] as $controller){
							$row = new Row();
							$row->setAttributes(array(
								'mca_role' => 'controller',
								'idref' => $controller['id'],
								'class' => '_'.$module['name'].'_'.$controller['name']
							));
							$row->addColumn(array(
								'name' => 'name',
								'content' => $controller['name'],
								'attributes' => array(
									'title' => $controller['id'],
									'class' => 'mark mcaName',
									'style' => 'padding-left: 50px;font-weight:bold;'
								)
							));
							$row->addColumn(array(
								'name' => 'description',
								'content' => $controller['description'],
								'attributes' => array(
									'title' => $controller['id'],
									'class' => 'mark mcaDescription',
									'style' => 'padding-left: 50px;font-weight:bold;'
								)
							));
							$content='';
							if($module['updatedDateTime']){
								$content = '<i class="fa fa fa-calendar-o" title="'.$controller['updatedDateTime'].'"></i>';
							}
							$row->addColumn(array(
								'name' => 'updatedDateTime',
								'content' => $content,
							));
							$row->addColumn(array(
								'name' => 'dependency',
								'content' => '',
							));
							$row->addColumn(array(
								'name' => 'display',
								'content' => '',
							));
							$row->addColumn(array(
								'name' => 'accountUsing',
								'content' => '',
							));
							$row->addColumn(array(
								'name' => 'userUsing',
								'content' => '',
							));

							$row->addColumn(array(
								'name' => 'status',
								'content' => $this->renderStatus($controller['status']),
								'attributes' => ['class' => 'colControls']
							));
							$row->addColumn(array(
								'name' => 'edit',
								'content' => '<a class="editMca fa fa-pencil-square-o"></a>',
								'attributes' => ['class' => 'colControls']
							));
							$row->addColumn(array(
								'name' => 'deploy',
								'content' => '',
								'attributes' => ['class' => 'colControls']
							));
							$row->addColumn(array(
								'name' => 'remove',
								'content' => '<a class="removeMca fa fa-trash-o color-red"></a>',
								'attributes' => ['class' => 'colControls']
							));
							$this->addRow($row);
							if(isset($actions[$controller['id']]) && count($actions[$controller['id']])){
								foreach ($actions[$controller['id']] as $action){
									$row = new Row();
									$row->setAttributes(array(
										'mca_role' => 'action',
										'idref' => $action['id'],
										'class' => '_'.$module['name'].'_'.$controller['name'].'_'.$action['name']
									));
									$row->addColumn(array(
										'name' => 'name',
										'content' => $action['name'],
										'attributes' => array(
											'title' => $action['id'],
											'class' => 'mcaName',
											'style' => 'padding-left: 100px;'
										)
									));
									$row->addColumn(array(
										'name' => 'description',
										'content' => $action['description'],
										'attributes' => array(
											'title' => $action['id'],
											'class' => 'mcaDescription',
											'style' => 'padding-left: 100px;'
										)
									));
									$content='';
									if($module['updatedDateTime']){
										$content = '<i class="fa fa fa-calendar-o" title="'.$action['updatedDateTime'].'"></i>';
									}
									$row->addColumn(array(
										'name' => 'updatedDateTime',
										'content' => $content,
										'attributes' => ['class' => 'colControls']
									));
									$row->addColumn(array(
										'name' => 'dependency',
										'content' => '<a class="fa fa-link color-black addDependence"></a>',
										'attributes' => ['class' => 'colControls']
									));
									$row->addColumn(array(
										'name' => 'display',
										'content' => $this->renderDisplay($action['display']),
										'attributes' => ['class' => 'colControls']
									));
									$row->addColumn(array(
										'name' => 'accountUsing',
										'content' => '',
									));
									$row->addColumn(array(
										'name' => 'userUsing',
										'content' => '',
									));

									$row->addColumn(array(
										'name' => 'status',
										'content' => $this->renderStatus($action['status']),
										'attributes' => ['class' => 'colControls']
									));
									$row->addColumn(array(
										'name' => 'edit',
										'content' => '<a class="editMca fa fa-pencil-square-o"></a>',
										'attributes' => ['class' => 'colControls']
									));
									$row->addColumn(array(
										'name' => 'deploy',
										'content' => '',
										'attributes' => ['class' => 'colControls']
									));
									$row->addColumn(array(
										'name' => 'remove',
										'content' => '<a class="removeMca fa fa-trash-o color-red"></a>',
										'attributes' => ['class' => 'colControls']
									));
									$this->addRow($row);
								}
							}
						}
					}
				}
			}
		}
	}
	private function renderStatus($status = null){
		$content = '<div class="statusSelect">';
		$content .= '<a class="changeStatus">';
		if($status == \System\Model\Action::STATUS_ACTIVE){
			$content .= '<i class="fa fa-check color-green"></i>';
		} else {
			$content .= '<i class="fa fa-minus-circle color-red"></i>';
		}
		$content .= '</a></div>';
		return $content;
	}
	private function renderDisplay($display = null){
		$content = '<div class="displaySelect">';
		$content .= '<a class="changeDisplay">';
		if($display == \System\Model\Action::STATUS_ACTIVE){
			$content .= '<i class="fa fa-check color-green"></i>';
		} else {
			$content .= '<i class="fa fa-minus-circle color-red"></i>';
		}
		$content .= '</a></div>';
		return $content;
	}
}

