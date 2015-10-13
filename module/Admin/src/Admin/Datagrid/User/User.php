<?php

namespace Admin\DataGrid\User;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;
use Home\Form;
class User extends DataGrid
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
                    'name' => 'fullName',
                    'content' => 'Họ tên'
                ),
                array(
                    'name' => 'email',
                    'content' => 'Email'
                ),
                array(
                    'name' => 'status',
                    'content' => 'Trạng thái'
                ),
                array(
                    'name' => 'createdDateTime',
                    'content' => 'Ngày đăng kí'
                ),

            )

        ]);

        if (! is_array($this->getDataSource()) && ! $this->getDataSource() instanceof \Zend\Paginator\Paginator) {
            return;
        }

        if ($this->getDataSource() > 0) {
            /** @var  $item \User\Model\User */
            foreach ($this->getDataSource() as $item) {

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
                    'content' => $item->getFullName(),
                    'attributes' => [
                    ]
                ));

                $row->addColumn(array(
                    'name' => 'email',
                    'content' => $item->getEmail(),
                    'attributes' => [
                    ]
                ));

                // Status
//                if($item->getStatus()==1){
//                    $status = '<i style="color:green;font-size:14px;" class="fa fa-check-circle"></i>';
//                }else{
//                    $status = '<div class="label label-warning">Khóa</div>';
//                }
                $row->addColumn(array(
                    'name' => 'status',
                    'content' => $status,
                    'attributes' => ['style' => 'width:30px;']
                ));


                $row->addColumn(array(
                    'name' => 'createdDateTime',
                    'content' => $item->getCreatedDateTime(),
                    'attributes' => [
                        'style' => 'position: relative'
                    ]
                ));

            }
        }
    }
}

?>