<?php

namespace Admin\DataGrid\Subject;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;
use Home\Form;
class Subject extends DataGrid
{
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
                    'content' => 'Tên môn học'
                ),
                array(
                    'name' => 'category',
                    'content' => 'Danh mục'
                ),
                array(
                    'name' => 'description',
                    'content' => 'Mô tả'
                ),
                array(
                    'name' => 'status',
                    'content' => 'Trạng thái'
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
            /** @var $item \Subject\Model\Subject */

                $row = new Row();
                $this->addRow($row);

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
                    ]
                ));

                $row->addColumn(array(
                    'name' => 'categoryId',
                    'content' => $item->getOption('category')->getName(),
                    'attributes' => [
                    ]
                ));

                $row->addColumn(array(
                    'name' => 'description',
                    'content' => $item->getDescription(),
                    'attributes' => [
                    ]
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


                // createdById
                $row->addColumn(array(
                    'name' => 'createdById',
                    'content' => $item->getOption('userName'),
                    'attributes' => [
                        'style' => 'position: relative'
                    ]
                ));



                $edit = '<a href="#" class="fa fa-edit edit-item"></a>';
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