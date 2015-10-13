<?php
/**
 * @author KienNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 **/
namespace Accounting\DataGrid;

use ZendX\DataGrid\DataGrid;
use ZendX\DataGrid\Row;
use Home\Model\DateBase;
use Zend\Validator\InArray;

class Transaction extends DataGrid
{
    /**
     */
    public function init()
    {
        $loginUserId = $this->getOption('loginUserId');
        $this->setAttributes(array(
            'id' => 'dgTransaction'
        ));
        $this->addHeader(array(
            'columns' => array(
                array(
                    'name' => 'id',
                    'content' => 'ID'
                ),
                array(
                    'name' => 'company',
                    'content' => 'Công ty'
                ),
                array(
                    'name' => 'applyDate',
                    'content' => 'Ngày hạch toán'
                ),
                array(
                    'name' => 'type',
                    'content' => 'Loại phiếu'
                ),
                array(
                    'name' => 'totalItems',
                    'content' => 'Số hạng mục'
                ),
                array(
                    'name' => 'valueBeforeTax',
                    'content' => 'Giá trị'
                ),
                array(
                    'name' => 'createdBy',
                    'content' => 'Người tạo'
                ),
                array(
                    'name' => 'approveBy',
                    'content' => 'Duyệt'
                ),
                array(
                    'name' => 'accountingBy',
                    'content' => 'Hạch toán'
                ),
                /** @todo đang đợi để làm nốt
                array(
                    'name' => 'paymentBy',
                    'content' => 'Xác nhận thu chi'
                ), */
                array(
                    'name' => 'action',
                    'content' => '<i class="fa fa-edit icon"></i>'
                ),
            )

        ));

        if (!$this->getDataSource() instanceof \Zend\Paginator\Paginator || !$this->getDataSource()->getCurrentModels()) {
            return;
        }
        foreach ($this->getDataSource()->getCurrentModels() as $transaction){
            /*@var $transaction \Accounting\Model\Transaction */
            $row = new Row();
            $this->addRow($row);
            $row->addColumn(array(
            	'name' => 'id',
                'content' => $transaction->getId()
            ));
            $row->addColumn(array(
                'name' => 'company',
                'content' => $transaction->getOption('companyName')
            ));
            $row->addColumn(array(
                'name' => 'applyDate',
                'content' => DateBase::toDisplayDate($transaction->getApplyDate())
            ));
            $content = '';
            if($transaction->getType() == \Accounting\Model\Transaction::TYPE_PAYMENT){
                $content = '<b class="text-danger">'.$transaction->getTypeName().'</b>';
            } else {
                $content = '<b class="text-info">'.$transaction->getTypeName().'</b>';
            }

            $row->addColumn(array(
                'name' => 'type',
                'content' => $content
            ));
            $row->addColumn(array(
                'name' => 'totalItem',
                'content' => $transaction->getOption('totalItems'),
                'attributes' => ['class' => 'colNumber']
            ));
            $row->addColumn(array(
                'name' => 'valueBeforeTax',
                'content' => \Home\Model\Format::toNumber($transaction->getOption('valueBeforeTax')),
                'attributes' => ['class' => 'colNumber']
            ));
            $content = [];
            if($transaction->getOption('createdBy')){
                $content[] = '<div>'.($transaction->getOption('createdBy')->getFullName()
                    ?:$transaction->getOption('createdBy')->getEmail()).'</div>';
            }
            $content[] ='<div><i class="date">'.DateBase::toDisplayDateTime($transaction->getCreatedDateTime()).'</i></div>';
            $row->addColumn(array(
                'name' => 'createdBy',
                'content' => implode('', $content),

            ));
            $content = [];
            if($transaction->getApprovedById()){
                if($transaction->getStatus() == \Accounting\Model\Transaction::STATUS_APPROVED){
                    if($transaction->getOption('approvedBy')){
                        $content[] = '<div class="text-success"><b>Duyệt: </b>'.($transaction->getOption('approvedBy')->getFullName()
                            ?:$transaction->getOption('approvedBy')->getEmail()).'</div>';
                    }
                    if($transaction->getApprovedDateTime()){
                        $content[] = '<div class="text-success"><i class="date">'.DateBase::toDisplayDateTime($transaction->getApprovedDateTime()).'</i></div>';
                    }
                    $content[] = '<a href="/accounting/transaction/approvereq?id='.$transaction->getId().'">Hủy duyệt</a>';
                } elseif ($transaction->getStatus() == \Accounting\Model\Transaction::STATUS_INAPPROVED){
                    if($transaction->getOption('approvedBy')){
                        $content[] = '<div class="text-danger"><b>Hủy: </b>'.($transaction->getOption('approvedBy')->getFullName()
                            ?:$transaction->getOption('approvedBy')->getEmail()).'</div>';
                    }
                    if($transaction->getApprovedDateTime()){
                        $content[] = '<div class="text-danger"><i class="date">'.DateBase::toDisplayDateTime($transaction->getApprovedDateTime()).'</i></div>';
                    }
                    $content[] = '<a href="/accounting/transaction/approvereq?id='.$transaction->getId().'">Duyệt</a>';
                } else {
                    if($transaction->getOption('approvedBy')){
                        $content[] = '<div class="text-success"><b>Duyệt: </b>'.($transaction->getOption('approvedBy')->getFullName()
                            ?:$transaction->getOption('approvedBy')->getEmail()).'</div>';
                    }
                    if($transaction->getApprovedDateTime()){
                        $content[] = '<div class="text-success"><i class="date">'.DateBase::toDisplayDateTime($transaction->getApprovedDateTime()).'</i></div>';
                    }
                }

            } else {
                $content[] = '<a href="/accounting/transaction/approvereq?id='.$transaction->getId().'">Duyệt</a>';
            }

            $row->addColumn(array(
                'name' => 'approvedBy',
                'content' => implode('', $content),
                'attributes' => ['class' => 'colControls']
            ));

            $content = [];
            if($transaction->getAccountingById()){
                if($transaction->getOption('accountingBy')){
                    $content[] = '<div>'.($transaction->getOption('accountingBy')->getFullName()
                        ?:$transaction->getOption('accountingBy')->getEmail()).'</div>';
                }
                if($transaction->getAccountingDateTime()){
                    $content[] = '<div><i class="date">'.DateBase::toDisplayDateTime($transaction->getAccountingDateTime()).'</i></div>';
                }
            } else {
                $content[] = '<a href="/accounting/transaction/accountingreq?id='.$transaction->getId().'">Duyệt</a>';
            }
            $row->addColumn(array(
                'name' => 'accountingBy',
                'content' => implode('', $content),
                'attributes' => ['class' => 'colControls']
            ));

           /** @todo: đang đợi chỉ thị để làm nốt
            $content = [];
            if($transaction->getPaymentById()){
                if($transaction->getOption('paymentBy')){
                    $content[] = '<div>'.($transaction->getOption('paymentBy')->getFullName()
                        ?:$transaction->getOption('paymentBy')->getEmail()).'</div>';
                }
                if($transaction->getPaymentDateTime()){
                    $content[] = '<div><i class="date">'.DateBase::toDisplayDateTime($transaction->getPaymentDateTime()).'</i></div>';
                }
            } else {
                $content[] = '<a href="/accounting/transaction/payment?id='.$transaction->getId().'">Duyệt</a>';
            }
            $row->addColumn(array(
                'name' => 'paymentBy',
                'content' => implode('', $content),
                'attributes' => ['class' => 'colControls']
            )); */
            $content = [];
            if($transaction->getStatus() == \Accounting\Model\Transaction::STATUS_NEW){
                if($transaction->getCreatedById() == $loginUserId){
                    $content[] = '<a class="fa fa-edit icon" href="/accounting/transaction/editreq?id='.$transaction->getId().'"></a>';
                }
            }
            $row->addColumn(array(
                'name' => 'action',
                'content' => implode('', $content),
                'attributes' => ['class' => 'colControls']
            ));
        }
    }
}