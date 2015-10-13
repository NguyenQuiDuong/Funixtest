<?php

namespace Accounting\Form;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Form\Element\Textarea;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\File;
use Zend\InputFilter\FileInput;

class ExpenseCategoryFilter extends FormBase{
	/**
	 *
	 * @param null|string $name        	
	 */
	public function __construct($serviceLocator) {
		parent::__construct ( 'ideaFilter' );
		$this->setServiceLocator ( $serviceLocator );
		$this->setAttribute ( 'method', 'GET' );
		
		$filter = $this->getInputFilter ();		
		
		//CompanyId
		$companyId = new Select ( 'companyId' );
		$companyId->setLabel ( 'Chọn công ty:' );
		$this->add ( $companyId );
		$this->loadCompanies($companyId) ;
		$filter->add ( array (
				'name' => 'companyId',
				'required' => false,
				'filters' => array (
						array (
								'name' => 'StringTrim'
						)
						)
				)
		) ;
		
		
		$this->add ( array (
				'name' => 'submit',
				'options' => array (),
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Lọc',
						'id' => 'btnFilterCompanyContact',
						'class' => 'btn btn-primary' 
				) 
		) );
	}
}

?>