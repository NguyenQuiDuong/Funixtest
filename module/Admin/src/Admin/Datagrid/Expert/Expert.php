<?php

namespace Admin\DataGrid\Expert;

use Home\Model\DateBase;
use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;
use Home\Form;
class Expert extends DataGrid
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
                    'name' => 'description',
                    'content' => 'Mô tả'
                ),
                array(
                    'name' => 'birthdate',
                    'content' => 'Ngày sinh'
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
            /** @var  $item \Expert\Model\Expert */
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
                    'content' => $item->getOption('user')->getFullName(),
                    'attributes' => [
                    ]
                ));

                $row->addColumn(array(
                    'name' => 'email',
                    'content' => $item->getOption('user')->getEmail(),
                    'attributes' => [
                    ]
                ));
                $row->addColumn(array(
                    'name' => 'description',
                    'content' => $item->getDescription(),
                    'attributes' => [
                    ]
                ));
                $row->addColumn(array(
                    'name' => 'birthdate',
                    'content' => DateBase::toDisplayDate($item->getOption('user')->getBirthdate()),
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
                    'name' => 'createdDateTime',
                    'content' => DateBase::toDisplayDateTime($item->getCreatedDateTime()),
                    'attributes' => [
                        'style' => 'position: relative'
                    ]
                ));

            }
        }
    }
}

?>