<?php
namespace Address\Form;

use Home\Form\ProvidesEventsForm;
use Zend\Form\Element;

class Book extends ProvidesEventsForm{

	public function setCities($array){
		if(!!($city = $this->get('cityId'))){
			$city->setValueOptions(array('' => '- Thành phố -') + $array);
		}
	}
	public function setDistricts($array){
		if(!!($district = $this->get('districtId'))){
			$district->setValueOptions(array('' => '- Quận Huyện -')+$array);
		}
	}
	public function __construct(){
		parent::__construct();
		$this->setAttribute('method','post');
		$this->setAttribute('class','formAddress');
		$this->setOptions(array(
			'decorator'	=>	array(
				'type'	=>	'ul',
			)
		));

		$this->add(array(
			'name'	=>	'name',
			'attributes'	=>	array(
				'type'	=>	'text',
				'class'	=>	'tb validate[required]',
				'id'	=>	'name'
			),
			'options'	=>	array(
				'label'	=>	'Tên đầy đủ:',
				'decorator'	=>	array('type'=>'li')
			),
		));

		$this->add(array(
			'name'	=>	'mobile',
			'attributes'	=>	array(
				'type'	=>	'text',
				'class'	=>	'tb validate[required,custom[phone]]',
				'id'	=>	'phone',
			),
			'options'	=>	array(
				'label'	=>	'Mobile:',
				'decorator'	=>	array('type'=>'li')
			),
		));

		$this->add(array(
			'name'	=>	'email',
			'attributes'	=>	array(
				'type'	=>	'Zend\Form\Element\Email',
				'class'	=>	'tb validate[required,custom[email]]',
				'id'	=>	'email',
			),
			'options'	=>	array(
				'label'	=>	'Email:',
				'decorator'	=>	array('type'=>'li')
			),
		));

		$this->add(array(
				'name' => 'cityId',
				'type' => 'select',
				'attributes' => array(
						'id' => 'cityId',
						'class'=>'validate[required]',
				),
				'options' => array(
						'label' => 'Thành phố:',
						'decorator' => array(
								'type' => 'li'
						)
				),
		));
		$this->add(array(
				'name' => 'districtId',
				'type' => 'select',
				'attributes' => array(
						'id' => 'districtId',
						'class'=>'validate[required]',
				),
				'options' => array(
						'label' => 'Quận huyện:',
						'decorator' => array(
								'type' => 'li'
						)
				),
		));
		$this->add(array(
				'name' => 'address',
				'attributes' => array(
						'type'  => 'text',
						'class' => 'tb',
						'id' => 'address'
				),
				'options' => array(
						'label' => 'Địa chỉ:',
						'decorator' => array(
								'type' => 'li'
						)
				),
		));

		$this->add(array(
				'name' => 'submit',
				'attributes' => array(
					'type'  => 'submit',
					'value' => 'Gửi',
					'id' => 'btnSubmit',
					'class' => 'htmlBtn first'
				),
				'options' => array(
					'decorator' => array(
						'type' => 'li',
						'attributes' => array(
						'class' => 'btns'
						)
					)
				),
		));

		$this->getEventManager()->trigger('init', $this);
	}
}