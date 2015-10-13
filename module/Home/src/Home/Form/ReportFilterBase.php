<?php
/**
 *
 * @author KienNN
 *
 */

namespace Home\Form;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Hidden;
use Home\Model\DateBase;

class ReportFilterBase extends FormBase{
	/**
	 * @author KienNN
	 * @param unknown $serviceLocator
	 * @param string $options
	 */
	public function __construct($serviceLocator, $options=null){
		parent::__construct();
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'GET');
		$filter = $this->getInputFilter();

		$filter = $this->getInputFilter();
		$daterangepicker = new Text('daterangepicker');

		$daterangepicker->setAttributes([
				'class' => 'date-range-picker'
				]);
		$this->add($daterangepicker);
		$filter->add(array(
			'name' => 'daterangepicker',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim')
			),
		));
		if($options && isset($options['displayMode']) && $options['displayMode']){
			$displayMode = new Select('displayMode');
			if(is_array($options['displayMode'])){
				$displayMode->setValueOptions($options['displayMode']);
			} else {
				$displayMode->setValueOptions(array(
					'day' => 'Theo ngày',
					'month' => 'Theo tháng',
				));
			}
			$this->add($displayMode);
			$filter->add(array(
				'name' => 'displayMode',
				'required' => false,
				'filters'   => array(
					array('name' => 'StringTrim'),
				),
			));
		}

		$companyId = $this->addElementCompany('companyId', null, ['required' => false,]);
				
		$departmentId = new Select('departmentId');
		$departmentId->setValueOptions(['' => '- Phòng ban -']);
		$this->add($departmentId);
		$this->loadDepartments($departmentId, $companyId);
		$filter->add(array(
		    'name'     => 'departmentId',
		    'required' => false,
		    'filters'  => array(
		        array('name' => 'StringTrim')
		    ),
		));
	}

	/**
	 * (non-PHPdoc)
	 * @see \Zend\Form\Form::setData()
	 */
	public function setData($data){
		if(!isset($data['daterangepicker']) || !$data['daterangepicker']){
			$date =  new DateBase();
			$date->sub(new \DateInterval('P7D'));
			$value = $date->format('d/m/Y');
			$date =  new DateBase();
			$value .= ' - '.$date->format('d/m/Y');
			$data['daterangepicker'] = $value;
		}
		parent::setData($data);
	}

	/**
	 * (non-PHPdoc)
	 * @see \Zend\Form\Form::getData()
	 */
	public function getData($flag=null){
		$data = parent::getData();
		if(isset($data['daterangepicker']) && $data['daterangepicker']){
			$daterange = explode(' - ', $data['daterangepicker']);
			$fromDate = $daterange[0];
			$toDate = $daterange[1];

			$data['fromDate'] = DateBase::toCommonDate(trim($fromDate));
			$data['toDate'] = DateBase::toCommonDate(trim($toDate));
		}
		return $data;
	}
}