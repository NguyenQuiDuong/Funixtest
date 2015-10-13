<?php
/**
 * @category    ERP library
 * @copyright   http://nhanh.vn
 * @license     http://nhanh.vn/license
 */

namespace Home\Model;

class Consts
{
    const GENDER_MALE 	= 1;
    const GENDER_FEMALE = 2;

    /**
     * @author VanCK
     * @param int $gender
     * @return string
     */
    public static function getGenderName($gender)
    {
		if($gender == self::GENDER_MALE) {
			return 'Nam';
		}
		if($gender == self::GENDER_FEMALE) {
			return 'Nữ';
		}
		return '';
    }

    const KEY_API_NHANH_ADDLEAD = '25bea83fe5673a427c214f48866794ac';

    const ACTIVITY_MESSAGE_CREATE_ACCOUNT_FROM_CONTRACT = 'Tạo thành khách hàng khi phiếu thu {__VALUE__} được duyệt';
    const ACTIVITY_MESSAGE_CREATE_CONTRACT = 'Tạo mới hợp đồng {__VALUE__}';
    const ACTIVITY_MESSAGE_EDIT_CONTRACT = 'Sửa hợp đồng {__VALUE__}';
    const ACTIVITY_MESSAGE_ADD_CONTRACT_PAYMENT = 'Thêm phiếu thu {__VALUE__}';
    const ACTIVITY_MESSAGE_EDIT_CONTRACT_PAYMENT = 'Sửa phiếu thu {__VALUE__}';
    const ACTIVITY_MESSAGE_APPROVE_CONTRACT = 'Duyệt hợp đồng {__VALUE__}';
    const ACTIVITY_MESSAGE_DELETE_CONTRACT = 'Hủy hợp đồng {__VALUE__}';
    const ACTIVITY_MESSAGE_CONFIRM_CONTRACT_PAYMENT = 'Duyệt phiếu thu {__VALUE__}';
    const ACTIVITY_MESSAGE_DELETE_CONTRACT_PAYMENT = 'Hủy phiếu thu {__VALUE__}';
    const ACTIVITY_MESSAGE_ADD_REQUEST_SERVICE = 'Thêm yêu cầu thực hiện dịch vụ {__VALUE__}';

    static function getMessage($message, $value = null){
        if($value){
            return str_replace('{__VALUE__}', $value, $message);
        }
        return $message;
    }

    static function getWorkAllowFileExtention(){
        return array(
                'jpg',
                'jpeg',
                'gif',
                'png',
                'doc',
                'docx',
                'xml',
                'csv',
                'xls',
                'xlsx',
                'rar',
                'zip',
                'pdf',
                'psd',
                'rar',
                'ppt',
                'pptx',
                'txt',
        );
    }

    const PRIVATE_SOURCE_OFFICEVG = 1;
    const PRIVATE_SOURCE_OFFICEBNC = 2;
}