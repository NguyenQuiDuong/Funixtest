<?php
/**
 * @author KienNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 **/
namespace Accounting\Form\Transaction;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\Hidden;

class ItemValidate extends FormBase
{
    public function __construct($serviceLocator, $options = null)
    {
        parent::__construct('transactionItem');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        $id = new Hidden('id');
        $this->add($id);
        $filter->add(array(
            'name' => 'id',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập id'
                        )
                    )
                ),
            )
        ));

        $transactionId = new Hidden('transactionId');
        $this->add($transactionId);
        $filter->add(array(
            'name' => 'transactionId',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập mã code'
                        )
                    )
                ),
            )
        ));

        $accountId = new Text('accountId');
        $this->add($accountId);
        $filter->add(array(
            'name' => 'accountId',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập quỹ'
                        )
                    )
                ),
            )
        ));
        if($options && isset($options['accountIds']) && is_array($options['accountIds'])){
            $filter->get('accountId')->getValidatorChain()
            ->attach(new \Zend\Validator\InArray(array('haystack' => $options['accountIds'])));
        }

        $expenseCategoryId = new Text('expenseCategoryId');
        $this->add($expenseCategoryId);
        $filter->add(array(
            'name' => 'expenseCategoryId',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập khoản mục chi tiêu'
                        )
                    )
                ),
            )
        ));
        if($options && isset($options['expenseCategoryIds']) && is_array($options['expenseCategoryIds'])){
            $filter->get('expenseCategoryId')->getValidatorChain()
            ->attach(new \Zend\Validator\InArray(array('haystack' => $options['expenseCategoryIds'])));
        }

        $amount = new Text('amount');
        $this->add($amount);
        $filter->add(array(
            'name' => 'amount',
            'required' => true,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập giá trị'
                        )
                    )
                ),
            )
        ));

        $vat = new Text('vat');
        $this->add($vat);
        $filter->add(array(
            'name' => 'vat',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
                array('name' => 'Digits')
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập giá trị'
                        )
                    )
                ),
            )
        ));

        $description = new Text('description');
        $this->add($description);
        $filter->add(array(
            'name' => 'description',
            'required' => false,
            'filters' => array(
                array('name' => 'StringTrim'),
            ),
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập giá trị'
                        )
                    )
                ),
            )
        ));
    }
}