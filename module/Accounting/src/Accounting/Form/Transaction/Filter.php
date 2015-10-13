<?php
namespace Accounting\Form\Transaction;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\File;
use Zend\InputFilter\FileInput;
use Zend\Form\Element\Hidden;
use Home\Model\DateBase;

class Filter extends FormBase
{
    /**
     *
     * @param null|string $name
     */
    public function __construct($serviceLocator)
    {
        parent::__construct('transactionFilter');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'GET');

        $filter = $this->getInputFilter ();

        $id = new Text('id');
        $id->setAttributes([
	       'placeholder' => 'ID'
        ]);
        $this->add($id);
        $filter->add(array(
            'name' => 'id',
            'required' => false,
            'filters' => array(
                array(
                    'name' => 'StringTrim'
                ),
                array(
                    'name' => 'Digits'
                ),
            )
        ));

        $companyId = $this->addElementCompany('companyId', null, ['required' => false]);

        $transaction = new \Accounting\Model\Transaction();

        $status = new Select('status');
        $status->setValueOptions(['' => '- Trạng thái -'] + $transaction->getStatuses());
        $this->add($status);
        $filter->add(array(
            'name' => 'status',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
            )
        ));

        $type = new Select('type');
        $type->setValueOptions(['' => '- Loại phiếu -'] + $transaction->getTypes());
        $this->add($type);
        $filter->add(array(
            'name' => 'type',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
            )
        ));

        $applyDateRange = new Text('applyDateRange');
        $applyDateRange->setAttributes(array(
        	'placeholder' => 'Ngày hạch toán',
            'class' => 'date-range-picker'
        ));
        $this->add($applyDateRange);
        $filter->add(array(
            'name' => 'applyDateRange',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
            )
        ));

        $createdByName =  new Text('createdByName');
        $createdByName->setAttributes(array(
        	'placeholder' => 'Người tạo'
        ));
        $this->add($createdByName);
        $filter->add(array(
            'name' => 'createdByName',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
            )
        ));

        $createdById = new Hidden('createdById');
        $this->add($createdById);
        $filter->add(array(
            'name' => 'createdById',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'options' => array(),
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Lọc',
                'id' => 'btnFilterCompanyContact',
                'class' => 'btn btn-primary'
            )
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Home\Form\FormBase::getData()
     */
    public function getData($flag = null){
       $data = parent::getData();
       if($data['applyDateRange']){
           $daterange = explode(' - ', $data['applyDateRange']);
           $fromDate  = $daterange[0];
           $toDate    = $daterange[1];

           $data['fromApplyDate'] = DateBase::toCommonDate(trim($fromDate));
           $data['toApplyDate']   = DateBase::toCommonDate(trim($toDate));
       }
       return $data;
    }
}
