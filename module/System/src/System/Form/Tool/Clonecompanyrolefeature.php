<?php

namespace System\Form\Tool;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use ZendX\Form\Element\DisplayGroup;

class Clonecompanyrolefeature extends FormBase{
    /**
     * @param null|string $name
     */
    public function __construct($serviceLocator, $options = null){
        parent::__construct('userManageFilter');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'POST');

        $filter = $this->getInputFilter();

        $groupBasic = new DisplayGroup('groupBasic');
        $this->add($groupBasic);

        $fromCompanyId = new Text('fromCompanyId');
		$fromCompanyId->setLabel('From companyId:');
		$fromCompanyId->setAttributes([
				'maxlength' => 255,
				'autocomplete' => 'off'
				]);
		$this->add($fromCompanyId);
		$groupBasic->addElement($fromCompanyId);
		$filter->add(array(
			'name' => 'fromCompanyId',
			'required' => true,
			'filters'   => array(
				array('name' => 'StringTrim'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập fromCompanyId'
						)
					)
				),


			),
		));

		$fromRoleId = new Text('fromRoleId');
		$fromRoleId->setLabel('From role:');

		$fromRoleId->setAttributes([
		    'autocomplete' => 'off'
		    ]);
		$this->add($fromRoleId);
		$groupBasic->addElement($fromRoleId);
		$filter->add(array(
		    'name' => 'fromRoleId',
		    'required' => true,
		    'filters'   => array(
		        array('name' => 'StringTrim'),
		    ),
		    'validators' => array(
		        array(
		            'name'    => 'NotEmpty',
		            'break_chain_on_failure' => true,
		            'options' => array(
		                'messages' => array(
		                    'isEmpty' => 'Bạn chưa nhập fromRole'
		                )
		            )
		        ),


		    ),
		));


		$toCompanyId = new Text('toCompanyId');
		$toCompanyId->setLabel('To CompanyIds:');
		$toCompanyId->setAttributes([
		    'autocomplete' => 'off'
		    ]);
		$this->add($toCompanyId);
		$groupBasic->addElement($toCompanyId);
		$filter->add(array(
		    'name' => 'toCompanyId',
		    'required' => true,
		    'filters'   => array(
		        array('name' => 'StringTrim'),
		    ),
		    'validators' => array(
		        array(
		            'name'    => 'NotEmpty',
		            'break_chain_on_failure' => true,
		            'options' => array(
		                'messages' => array(
		                    'isEmpty' => 'Bạn chưa nhập toCompanyId'
		                )
		            )
		        ),
		    ),
		));

		$asRole = new Text('asRole');
		$asRole->setLabel('As role:');
		$asRole->setAttributes([
		    'maxlength' => 255,
		    'autocomplete' => 'off'
		    ]);
		$this->add($asRole);
		$groupBasic->addElement($asRole);

		$filter->add(array(
		    'name' => 'asRole',
		    'required' => true,
		    'filters'   => array(
		        array('name' => 'StringTrim'),
		    ),
		    'validators' => array(
		        array(
		            'name'    => 'NotEmpty',
		            'break_chain_on_failure' => true,
		            'options' => array(
		                'messages' => array(
		                    'isEmpty' => 'Bạn chưa nhập as role'
		                )
		            )
		        ),
		    ),
		));

		$this->add(array(
		    'name' => 'submit',
		    'options' => array(
		        'clearBefore' => true
		    ),
		    'attributes' => array(
		        'type'  => 'submit',
		        'value' => 'Lưu',
		        'id' => 'btnSaveCrmContact',
		        'class' => 'btn btn-primary'
		    ),
		));
    }
}