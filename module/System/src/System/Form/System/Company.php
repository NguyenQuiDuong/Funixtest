<?php

namespace System\Form\System;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;

class Company extends FormBase{
	/**
	 * @param null|string $name
	 */
	public function __construct($serviceLocator, $options = null){
		parent::__construct('userManageFilter');
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'GET');

		$filter = $this->getInputFilter();

		$companyId = $this->addElementCompany('companyId', null, ['required' => false]);
		$compareCompanyId = $this->addElementCompany('compareCompanyId', null, ['required' => false]);


		$this->add(array(
			'name' => 'submit',
			'options' => array(
			),
			'attributes' => array(
				'type'  => 'submit',
				'value' => 'Lọc',
				'id' => 'btnFilterCrmContact',
				'class' => 'btn btn-primary'
			),
		));
	}
}