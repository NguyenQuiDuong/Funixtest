<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\DataGrid\System;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;

class Company extends DataGrid{
	public function init(){
		$this->addAttributes(array(
			'id' => 'dgSystemCompanyFeature',
			'class' => 'table-hover'
		));

		$mcaList = $this->getOption('mcaList');
		$features = $this->getOption('features');
		$compareFeatures = $this->getOption('compareFeatures');
		$compareTitle = $this->getOption('compareTitle');
		$companyId = $this->getOption('companyId');
		$hasCompareFeature = false;
		$hasFeature = false;
		if($compareFeatures && count($compareFeatures)){
			$hasCompareFeature = true;
		}
		if($features && count($features)){
			$hasFeature = true;
		}

		$header = new Row();
		$this->addHeader($header);
		$header->addColumn(array(
			'name' => 'mcaName',
			'content' => 'Quyền'
		));

		if($compareTitle){
			$header->addColumn(array(
				'name' => 'compareFeature',
				'content' => $compareTitle.' <a class="fa fa-arrow-right color-red exchangeFeature"></a>'
			));
		}
		$header->addColumn(array(
			'name' => 'feature',
			'content' => 'Trạng thái'
		));

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
			$row->setAttributes(array(
				'mca_role' => 'module',
				'idref' => $module['id'],
				'class' => '_'.$module['name']
			));
			$row->addColumn(array(
				'name' => 'mcaName',
				'content' => '<b>'.($module['description']?:$module['name']).'</b>',
				'attributes' => array(
					'title' => $module['id'],
					'class' => 'mark mcaName',
				)
			));
			if($compareTitle){
				$row->addColumn(array(
					'name' => 'compareFeature',
					'content' => ''
				));
			}
			$row->addColumn(array(
				'name' => 'feature',
				'content' => ''
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
						'name' => 'mcaName',
						'content' => '<b>'.($controller['description']?:$controller['name']).'</b>',
						'attributes' => array(
							'title' => $controller['id'],
							'class' => 'mark mcaName',
							'style' => 'padding-left: 50px;font-weight:bold;'
						)
					));
					if($compareTitle){
						$row->addColumn(array(
							'name' => 'compareFeature',
							'content' => ''
						));
					}
					$row->addColumn(array(
						'name' => 'feature',
						'content' => ''
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
								'name' => 'mcaName',
								'content' => ($action['description']?:$action['name']),
								'attributes' => array(
									'title' => $action['id'],
									'class' => 'mcaName',
									'style' => 'padding-left: 100px;'
								)
							));
							if($compareTitle){
								$content = '';
								if($hasCompareFeature){
									if(isset($compareFeatures[$action['id']])){
										$content = '<i class="fa icon fa-check color-green compareFeature"></i>';
									} else {
										$content = '<i class="fa icon fa-minus-circle color-red compareFeature"></i>';
									}
								} else {
									$content = '<i class="fa icon fa-check color-green compareFeature"></i>';
								}
								$row->addColumn(array(
									'name' => 'compareFeature',
									'content' => $content,
									'attributes' => ['class' => 'colControls']
								));
							}
							$content = '';
							if($hasFeature){
								if(isset($features[$action['id']])){
									$content = '<a class="fa icon fa-check color-green changeStatus" idref="'.$action['id'].'" companyid="'.$companyId.'"></a>';
								} else {
									$content = '<a class="fa icon fa-minus-circle color-red changeStatus" idref="'.$action['id'].'" companyid="'.$companyId.'"></a>';
								}
							} else {
								$content = '<a class="fa icon fa-minus-circle color-red changeStatus" idref="'.$action['id'].'" companyid="'.$companyId.'"></a>';
							}
							$row->addColumn(array(
								'name' => 'feature',
								'content' => $content,
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