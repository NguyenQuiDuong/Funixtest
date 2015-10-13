<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\DataGrid\User;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;
use Home\Model\Consts;

class User extends DataGrid{
	/**
	 * @author KienNN
	 */
	public function init(){
		$this->setAttributes(array(
			'id' => 'dgUser'
		));
		$this->addHeader([
				'attributes' => array(

				),
				'options' => array(),
				'columns' => array(
					array(
						'name' => 'id',
						'content' => 'ID',
					),
					array(
						'name' => 'username',
						'content' => 'Tên đăng nhập',
					),
					array(
						'name' => 'role',
						'content' => 'Phân quyền',
					),
					array(
						'name' => 'company',
						'content' => 'Trực thuộc',
					),
					array(
						'name' => 'fullName',
						'content' => 'Tên đầy đủ',
					),
					array(
						'name' => 'mobile',
						'content' => 'Mobile',
					),
					array(
						'name' => 'email',
						'content' => 'Email',
					),
					array(
						'name' => 'createdBy',
						'content' => 'Người tạo',
					),
					array(
						'name' => 'createdDateTime',
						'content' => 'Ngày tạo',
					),
					array(
						'name' => 'active',
						'content' => 'Kích hoạt',
					),
					array(
						'name' => 'locked',
						'content' => 'Khóa',
					),
					array(
						'name' => 'edit',
						'content' => '<i class="fa fa-edit"></i>',
						'attributes' => ['class' => 'colControls', 'title' => 'Sửa']
					),
				)
				]);
		if(!is_array($this->getDataSource()) && !$this->getDataSource() instanceof \Zend\Paginator\Paginator) {
			return;
		}
		if(!$this->getDataSource()->getCurrentModels()){
			return null;
		}
		foreach($this->getDataSource()->getCurrentModels() as $item) {
		    
			/* @var $item \User\Model\User */
			$row = new Row();
			$row->addColumn(array(
				'name' 			=> 'id',
				'content' 		=> $item->getId(),
				'attributes' 	=> []
			));
			
			$content = '';
			$content .= $item->getUsername();
			if($item->getOption('hasPrivateRole')){
				$content.='<i style="margin-left:3px;" data-toggle="tooltip" data-placement="top" data-original-title="có phân quyền riêng" class="glyphicon glyphicon-registration-mark text-danger"></i>';
			}
			$row->addColumn(array(
				'name' 			=> 'username',
				'content' 		=> $content,
				'attributes' 	=> []
			));
			$content = [];
			$content[] = $item->getRoleDisplayName();
			if($item->getOption('role')){
			    foreach ($item->getOption('role') as $role){
			        $content[] = '<span class="text-primary">'.$role.'</span>';
			    }
			}
			$row->addColumn(array(
				'name' 			=> 'role',
				'content' 		=> implode('<div class="line-break"></div>', $content),
				'attributes' 	=> []
			));
			
			$content = [];
			if($item->getOption('companies')){
				foreach ($item->getOption('companies') as $companyName){
					$content[] = $companyName;
				}
			}
			if ($item->getOption('departments')){
			    foreach ($item->getOption('departments') as $departmentName){
			        $content[] = '<span class="text-primary">'.$departmentName.'</span>';
			    }
			}
			$row->addColumn(array(
				'name' 			=> 'company',
				'content' 		=> implode('<div class="line-break"></div>', $content),
				'attributes' 	=> []
			));
			$row->addColumn(array(
				'name' 			=> 'fullName',
				'content' 		=> $item->getFullName(),
				'attributes' 	=> []
			));
			$row->addColumn(array(
				'name' 			=> 'mobile',
				'content' 		=> $item->getMobile(),
				'attributes' 	=> []
			));
			$row->addColumn(array(
				'name' 			=> 'email',
				'content' 		=> $item->getEmail(),
				'attributes' 	=> []
			));
			$row->addColumn(array(
				'name' 			=> 'createdBy',
				'content' 		=> $item->getOption('createdBy')?$item->getOption('createdBy')->getFullName():'',
				'attributes' 	=> []
			));
			$row->addColumn(array(
				'name' 			=> 'createdDateTime',
				'content' 		=> $item->getCreatedDateTime(),
				'attributes' 	=> []
			));
			$content = '';
			if($item->getActive()){
				$content = '<a class="fa fa-check color-green icon changeActive" idref="'.$item->getId().'"></a>';
			} else {
				$content = '<a class="fa fa-minus-circle color-red icon changeActive" idref="'.$item->getId().'"></a>';
			}
			$row->addColumn(array(
				'name' 			=> 'active',
				'content' 		=> $content,
				'attributes' 	=> ['class' => 'colControls']
			));
			$content = '';
			if($item->getLocked()){
				$content = '<a class="fa fa-lock color-red icon changeLocked" idref="'.$item->getId().'"></a>';
			} else {
				$content = '<a class="fa fa-unlock color-blue icon changeLocked" idref="'.$item->getId().'"></a>';
			}
			$row->addColumn(array(
				'name' 			=> 'locked',
				'content' 		=> $content,
				'attributes' 	=> ['class' => 'colControls']
			));
			$row->addColumn(array(
				'name' 			=> 'edit',
				'content' 		=> '<a href="/system/user/edit?id='. $item->getId() .'" class="fa fa-edit"></a>',
				'attributes' 	=> ['class' => 'colControls']
			));
			$this->addRow($row);
		}
	}
}