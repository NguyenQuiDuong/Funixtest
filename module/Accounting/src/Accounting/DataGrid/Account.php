<?php
/**
 * @author hungpx
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 **/
namespace Accounting\DataGrid;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;
use Home\Model\Consts;
use Home\Form;
use Home\Model\DateBase;
/* @var $item \Accounting\Model\Account */
class Account extends DataGrid
{
    /**
     *
     * @author Hungpx
     */
    public function init()
    {
        $this->addHeader([
            'attributes' => array( ),
            'options' => array(),
            'columns' => array(             
                array(
                    'name' => 'id',
                    'content' => 'ID'
                ),
                array(
                    'name' => 'name',
                    'content' => 'Tên tài khoản'
                ), 
                array(
                    'name' => 'code',
                    'content' => 'Mã Code'
                ),
                array(
                    'name' => 'status',
                    'content' => 'Trạng thái',
                    'attributes' => ['class' => 'maxw50']
                ),
                array(
                    'name' => 'companyId',
                    'content' => 'Tên Công ty '
                ),
                array(
                    'name' => 'createdById',
                    'content' => 'Người tạo'
                ),
               
                array(
                    'name' => 'edit',
                    'content' => '<i class="fa fa-edit"></i> '
                ),
            )
            
        ]);
        
        if (! is_array($this->getDataSource()) && ! $this->getDataSource() instanceof \Zend\Paginator\Paginator) {
            return;
        }
        
        if ($this->getDataSource() > 0) {
            foreach ($this->getDataSource() as $item) {
                
                $row = new Row();
                $this->addRow($row);
                if($item->getOptions()['ord']==0){
                    $ord = 10;
                }else{
                    $ord = $item->getOptions()['ord']*50;
                    $font = '';
                }
         
                //$title .= "<div><i style='font-size:0.85em'>(" . $item->getOption('userName') . " - " . DateBase::toDisplayDateTime($item->getCreatedDateTime()) . ")</i></div>";
                
                // Add $item to row
                $row->addColumn(array(
                    'name' => 'id',
                    'content' => $item->getId(),
                    'attributes' => []
                ));
                
                // name
                $row->addColumn(array(
                    'name' => 'name',
                    'content' => $item->getName(),
                    'attributes' => [
                        'style' => 'position: relative',
                        'style' => 'padding-left: '.$ord.'px;',
                    ]
                )); 
                
                // Code
                $row->addColumn(array(
                    'name' => 'code',
                    'content' => $item->getCode(),
                    'attributes' => [ ]
                ));
                
                // Status
                if($item->getStatus()==1){
                    $status = '<i style="color:green;font-size:14px;" class="fa fa-check-circle"></i>';
                }else{
                    $status = '<div class="label label-warning">Khóa</div>';
                }
                $row->addColumn(array(
                    'name' => 'status',
                    'content' => $status,
                    'attributes' => ['style' => 'width:30px;']
                ));
                                                         
                // company
                $row->addColumn(array(
                    'name' => 'companyId',
                    'content' => $item->getOption('companyName'),
                    'attributes' => []
                ));
              
                // createdById
                $row->addColumn(array(
                    'name' => 'createdById',
                    'content' => $item->getOption('userName'),
                    'attributes' => [
                        'style' => 'position: relative'
                        ]
                ));
                
               
                
                if ($item->getStatus() == 1) {
                    $edit = '<a href="/accounting/account/edit?id=' . $item->getId() . '" class="fa fa-edit"></a>';
                } else {
                    $edit = '<a href="#" class="fa fa-edit edit-item"></a>';
                }
                $row->addColumn(array(
                    'name' => 'edit',
                    'content' => $edit . '<a href="#" value="' . $item->getId() . '" class="fa fa-trash-o deleteAccount del_Item mgleft5" ></a>',
                    'attributes' => [
                        'class' => 'colControls'
                    ]
                ));
            }
        }
    }
}

?>