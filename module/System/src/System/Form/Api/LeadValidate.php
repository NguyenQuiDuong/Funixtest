<?php

namespace System\Form\Api;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Select;
use Zend\Validator\StringLength;
use Home\Filter\HTMLPurifier;

class LeadValidate extends FormBase
{
    /**
     * @param null|string $name
     */
    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('leadValidate');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'POST');
        $filter = $this->getInputFilter();

        $token = new Text('token');
        $this->add($token);
        $filter->add(array(
            'name' => 'token',
            'required' => true,
            'filters'   => array(
                array('name' => 'StringTrim'),
                //array('name' => 'Digits'),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập token'
                        )
                    )
                ),
            ),
        ));

        $companyId = new Text('companyId');
        $this->add($companyId);
        $filter->add(array(
            'name' => 'companyId',
            'required' => true,
            'filters'   => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
            ),
        ));

        $name = new Text('name');
        $this->add($name);
        $filter->add(array(
            'name' => 'name',
            'required' => true,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
        ));

        $mobile = new Text('mobile');
        $this->add($mobile);
        $filter->add(array(
			'name' => 'mobile',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
				array('name' => 'Digits'),
				new \Home\Filter\Mobile(),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập Mobile'
						)
					)
				),
				array(
					'name'    => 'StringLength',
					'break_chain_on_failure' => true,
					'options' => array(
						'min' => 10,
						'max' => 11,
						'messages' => array(
							StringLength::INVALID => 'Mobile phải là dạng 10 hoặc 11 chữ số',
							StringLength::TOO_SHORT => 'Mobile phải là dạng 10 hoặc 11 chữ số',
							StringLength::TOO_LONG => 'Mobile phải là dạng 10 hoặc 11 chữ số'
						)
					)
				),
			),
		));

        $mobile2 = new Text('mobile2');
        $this->add($mobile2);
        $filter->add(array(
            'name' => 'mobile2',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
                new \Home\Filter\Mobile(),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập Mobile'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'min' => 10,
                        'max' => 11,
                        'messages' => array(
                            StringLength::INVALID => 'Mobile phải là dạng 10 hoặc 11 chữ số',
                            StringLength::TOO_SHORT => 'Mobile phải là dạng 10 hoặc 11 chữ số',
                            StringLength::TOO_LONG => 'Mobile phải là dạng 10 hoặc 11 chữ số'
                        )
                    )
                ),
            ),
        ));

        $phone = new Text('phone');
        $this->add($phone);
        $filter->add(array(
            'name' => 'phone',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
                new \Home\Filter\Mobile(),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập Mobile'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'min' => 10,
                        'max' => 11,
                        'messages' => array(
                            StringLength::INVALID => 'Mobile phải là dạng 10 hoặc 11 chữ số',
                            StringLength::TOO_SHORT => 'Mobile phải là dạng 10 hoặc 11 chữ số',
                            StringLength::TOO_LONG => 'Mobile phải là dạng 10 hoặc 11 chữ số'
                        )
                    )
                ),
            ),
        ));

        $phone2 = new Text('phone2');
        $this->add($phone2);
        $filter->add(array(
            'name' => 'phone2',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
                new \Home\Filter\Mobile(),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập Mobile'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'min' => 10,
                        'max' => 11,
                        'messages' => array(
                            StringLength::INVALID => 'Mobile phải là dạng 10 hoặc 11 chữ số',
                            StringLength::TOO_SHORT => 'Mobile phải là dạng 10 hoặc 11 chữ số',
                            StringLength::TOO_LONG => 'Mobile phải là dạng 10 hoặc 11 chữ số'
                        )
                    )
                ),
            ),
        ));

        $email = new Text('email');
        $email->setLabel('Email:');
        $email->setAttributes([
            'maxlength' => 255
            ]);

        $this->add($email);
        $filter->add(array(
            'name' => 'email',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập Họ tên khách hàng'
                        )
                    )
                ),
                array(
                    'name'    => 'EmailAddress',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\EmailAddress::INVALID => 'Email không hợp lệ'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'max' => 255,
                        'messages' => array(
                            StringLength::TOO_LONG => 'email chỉ giới hạn 255 kí tự'
                        )
                    )
                ),
            ),
        ));

        $cityId = new Select('cityId');
        $cityId->setLabel('Thành phố:');
        $cityId->setValueOptions(array(
            '' => '- Thành phố -'
        ));
        $this->add($cityId);
        $this->loadCities($cityId);
        $filter->add(array(
            'name' => 'cityId',
            'required' => true,
	        'filters'   => array(
		        array('name' => 'StringTrim'),
		        array('name' => 'Digits'),
	        ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập thành phố'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'max' => 250,
                        'messages' => array(
                            StringLength::INVALID => 'Địa chỉ chỉ giới hạn nhỏ hơn 250 kí tự',

                        )
                    )
                ),
            ),
        ));

        $districtId = new Select('districtId');
        $districtId->setLabel('Quận huyện:');
        $districtId->setValueOptions(array(
            '' => '- Quận huyện -'
        ));
        $this->add($districtId);
        $this->loadDistricts($districtId, $cityId, $options);
        $filter->add(array(
            'name' => 'districtId',
            'required' => true,
	        'filters'   => array(
		        array('name' => 'StringTrim'),
		        array('name' => 'Digits'),
	        ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập thành phố'
                        )
                    )
                ),
            ),
        ));

        $address = new Text('address');
        $address->setLabel('Địa chỉ:');
        $address->setAttributes([
            'maxlength' => 255
            ]);
        $this->add($address);
        $filter->add(array(
            'name' => 'address',
            'required' => true,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập địa chỉ'
                        )
                    )
                ),
            ),
        ));


        $website = new Text('website');
        $website->setLabel('Website doanh nghiệp:');
        $website->setAttributes([
            'maxlength' => 255
            ]);
        $this->add($website);
        $filter->add(array(
            'name' => 'website',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
                new \Home\Filter\DomainName(),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập website doanh nghiệp khách hàng'
                        )
                    )
                ),
                array(
                    'name'    => 'Hostname',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Hostname::INVALID_URI => 'Hostname không hợp lệ'
                        )
                    )
                ),
                array(
                    'name'    => 'StringLength',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'max' => 255,
                        'messages' => array(
                            StringLength::TOO_LONG => 'Website chỉ giới hạn 255 kí tự'
                        )
                    )
                ),
            ),
        ));

        $nhanhStoreId = new Text('nhanhStoreId');
        $this->add($nhanhStoreId);
        $filter->add(array(
            'name' => 'nhanhStoreId',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits'),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập nhanh store Id'
                        )
                    )
                ),
            ),
        ));

        $nhanhStoreName = new Text('nhanhStoreName');
        $this->add($nhanhStoreName);
        $filter->add(array(
            'name' => 'nhanhStoreName',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập nhanh store Name'
                        )
                    )
                ),
            ),
        ));

        $sourceReference = new Text('sourceReference');
        $this->add($sourceReference);
        $filter->add(array(
            'name' => 'sourceReference',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập sourceReference'
                        )
                    )
                ),
            ),
        ));


        $service = new Text('service');
        $this->add($service);
        $filter->add(array(
            'name' => 'service',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập dịch vụ'
                        )
                    )
                ),
            ),
        ));

        $title = new Text('title');
        $this->add($title);
        $filter->add(array(
            'name' => 'title',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập tiêu đề'
                        )
                    )
                ),
            ),
        ));

        $note = new Text('note');
        $this->add($note);
        $filter->add(array(
            'name' => 'note',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập note'
                        )
                    )
                ),
            ),
        ));

        $utm_source = new Text('utm_source');
        $this->add($utm_source);
        $filter->add(array(
            'name' => 'utm_source',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập utm_source'
                        )
                    )
                ),
            ),
        ));

        $utm_medium = new Text('utm_medium');
        $this->add($utm_medium);
        $filter->add(array(
            'name' => 'utm_medium',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập utm_medium'
                        )
                    )
                ),
            ),
        ));

        $utm_term = new Text('utm_term');
        $this->add($utm_term);
        $filter->add(array(
            'name' => 'utm_term',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập utm_term'
                        )
                    )
                ),
            ),
        ));


        $utm_content = new Text('utm_content');
        $this->add($utm_content);
        $filter->add(array(
            'name' => 'utm_content',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập utm_content'
                        )
                    )
                ),
            ),
        ));

        $utm_campaign = new Text('utm_campaign');
        $this->add($utm_campaign);
        $filter->add(array(
            'name' => 'utm_campaign',
            'required' => false,
            'filters'   => array(
                array('name' => 'StringTrim'),
            	new HTMLPurifier()
            ),
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập utm_campaign'
                        )
                    )
                ),
            ),
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Zend\Form\Form::isValid()
     */
    public function isValid(){
        $isValid = parent::isValid();
        if($isValid){
            $data = parent::getData();
            if(!$data['mobile'] && !$data['mobile2'] && !$data['phone'] && !$data['phone2'] && !$data['email']){
                $isValid =  false;
                $this->get('name')->setMessages(['Bạn phải điền ít nhất số điện thoại hoặc email']);
                return false;
            }
            $token = \Home\Model\Consts::KEY_API_NHANH_ADDLEAD.json_encode(array(
            	0 => isset($data['email'])?$data['email']:'',
                2 => isset($data['mobile'])?$data['mobile']:'',
                3 => isset($data['nhanhStoreId'])?$data['nhanhStoreId']:'',
            ));
            $token = md5($token);
            if(!isset($data['token']) || $data['token'] != $token){
                $this->get('token')->setMessages(['Mã bảo mật không chính xác']);
                $isValid = false;
            }
        }
        return $isValid;
    }
}