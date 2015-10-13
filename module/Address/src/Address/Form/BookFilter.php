<?php
namespace Address\Form;

use Home\InputFilter\ProvidesEventsInputFilter;

class BookFilter extends ProvidesEventsInputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'name',
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập tên đầy đủ',
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'name' => 'mobile',
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập số điện thoại',
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'name'       => 'email',
            'validators' => array(
                array(
                    'name'    => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập Email'
                        )
                    )
                ),
                array(
                    'name'    => 'EmailAddress',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'emailAddressInvalidFormat' => 'Địa chỉ email không hợp lệ',
                            'emailAddressInvalidHostname' => 'Tên mở rộng của email không hợp lệ',
                        )
                    )
                )
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa nhập địa chỉ nhận hàng',
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'name' => 'cityId',
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa chọn Tỉnh/ Thành phố',
                        )
                    )
                )
            )
        ));
        $this->add(array(
            'name' => 'districtId',
            'validators' => array(
                array(
                    'name' => 'NotEmpty',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'messages' => array(
                            'isEmpty' => 'Bạn chưa chọn Quận/ Huyện',
                        )
                    )
                )
            )
        ));
        $this->getEventManager()->trigger('init', $this);
    }
}