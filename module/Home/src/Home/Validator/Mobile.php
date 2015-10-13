<?php
/**
 * Home\Validator\Mobile
 *
 * @author      KienNN
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Validator;
use Zend\Validator\AbstractValidator;

class Mobile extends AbstractValidator
{
    const MSG_INVALID = 'inValid';

    public $min = 10;
    public $max = 11;

    protected $messageTemplates = array(
        self::MSG_INVALID => "'%value%' không phải số điện thoại hợp lệ"
    );

    public function isValid($value)
    {
        $this->setValue($value);

        $mobileFilter = new \Home\Filter\Mobile();
        $value = $mobileFilter->filter($value);
        if(!$value){
            $this->error(self::MSG_INVALID);
            return false;
        }
        if(strlen($value) < $this->min){
            $this->error(self::MSG_INVALID);
            return false;
        }
        if(strlen($value) < $this->max){
            $this->error(self::MSG_INVALID);
            return false;
        }

        return true;
    }
}