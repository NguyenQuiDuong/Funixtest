<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\DataGrid\System;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;

class Role extends DataGrid{

	public function init(){
		$this->addAttributes(array(
			'id' => 'dgSystemRole'
		));
		$this->addHeader(array(
			'columns' =>array(
				array(
					'name' => 'id',
					'content' => 'ID'
				),
				array(
					'name' => 'name',
					'content' => 'Tên'
				),
				array(
					'name' => 'order',
					'content' => 'Order'
				),
				array(
					'name' => 'action',
					'content' => '<i class="fa fa-edit"></i>'
				),
			)
		));

		if(!is_array($this->getDataSource())) {
			return;
		}
		foreach($this->getDataSource() as $item) {
			/*@var $item \System\Model\Role */
			$row = new Row();
			$this->addRow($row);
			$row->addColumn(array(
				'name' => 'id',
				'content' => $item->getId()
			));
			$row->addColumn(array(
				'name' => 'name',
				'content' => $item->getName()
			));
			$row->addColumn(array(
				'name' => 'order',
				'content' => ''
			));
			$row->addColumn(array(
				'name' => 'action',
				'content' => '<a class="fa fa-edit icon editRole" data-idref="'.$item->getId().'"></a>'.
					'<a class="fa fa-trash icon deleteRole" data-idref="'.$item->getId().'"></a>',
				'attributes' => ['class' => 'colControls']
			));
		}
	}

}