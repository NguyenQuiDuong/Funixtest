<?php

namespace System\Form\Tool;

use Home\Form\FormBase;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\Select;

class MergerProduct extends FormBase{
    /**
     * @param null|string $productName
     */
    public function __construct($serviceLocator, $options=null){
        parent::__construct('crmContract');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $mainBasic = new DisplayGroup('mainBasic');
        $mainBasic->setLabel('Sản phẩm mẫu');
        $this->add($mainBasic);

        $todeleteBasic = new DisplayGroup('todeleteBasic');
        $todeleteBasic->setLabel('Sản phẩm sẽ xóa');
        $this->add($todeleteBasic);

        $mainCompanyId = $this->addElementCompany('mainCompanyId', $mainBasic, ['required' => true]);

        $mainProductId = new Select('mainProductId');
        $mainProductId->setLabel('Sản phẩm mẫu:');
        $this->add($mainProductId);
        $mainBasic->addElement($mainProductId);
        $this->loadProducts($mainProductId, $mainCompanyId, $options);
        $filter->add(array(
            'name' => 'mainProductId',
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
                            'isEmpty' => 'Bạn chưa nhập sản phẩm'
                        )
                    )
                ),
                array(
                    'name'    => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Digits::INVALID => 'Dữ liệu sản phẩm không hợp lệ'
                        )
                    )
                ),
            ),
        ));

        $toDeleteCompanyId = $this->addElementCompany('toDeleteCompanyId', $todeleteBasic, ['required' => true]);

        $toDeleteProductId = new Select('toDeleteProductId');
        $toDeleteProductId->setLabel('Sản phẩm sẽ xóa:');
        $this->add($toDeleteProductId);
        $todeleteBasic->addElement($toDeleteProductId);
        $this->loadProducts($toDeleteProductId, $toDeleteCompanyId, $options);
        $filter->add(array(
            'name' => 'toDeleteProductId',
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
                            'isEmpty' => 'Bạn chưa nhập sản phẩm'
                        )
                    )
                ),
                array(
                    'name'    => 'Digits',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            \Zend\Validator\Digits::INVALID => 'Dữ liệu sản phẩm không hợp lệ'
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
                'value' => 'Hợp nhất',
                'id' => 'btnSaveCrmContract',
                'class' => 'btn btn-primary btnSaveCrmContract'
            ),
        ));
    }

    public function isValid(){
        $isValid = parent::isValid();
        if($isValid){
            $data = parent::getData();
            if($data['mainCompanyId'] == $data['toDeleteCompanyId']){
                $this->get('mainCompanyId')->setMessages(['Không thể hợp nhất 2 sp cùng cty']);
                $this->get('toDeleteCompanyId')->setMessages(['Không thể hợp nhất 2 sp cùng cty']);
                $isValid = false;
            }
            if($data['mainProductId'] == $data['toDeleteProductId']){
                $this->get('mainProductId')->setMessages(['Sp trùng']);
                $this->get('toDeleteProductId')->setMessages(['Sp trùng']);
                $isValid = false;
            }

        }
        return $isValid;
    }
}