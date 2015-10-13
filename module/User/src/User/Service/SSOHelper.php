<?php
namespace User\Service;
/**
* Class này cho phép các website tham gia vào quá trình SSO của VatgiaID
*
* Các có thể thay đổi các tham số sau đây :
* <code>
* 	$gsnCookieName: tên của cookie sẽ lưu trữ _gsn, không khuyến khích thay đổi.
* 	$gsnSalt: giá trị ngẫu nhiên, gây nhiễu cho GSN, nên thay đổi.
* 	$validTimestamp: thời gian chênh lệch giữa 2 server (SSO và Website) + thời gian trên đường truyền. Không nên thay đổi.
* </code>
*
* @author Tarzan <hocdt85@gmail.com>
*/
class SSOHelper
{
    static public $gsnCookieName = '_gsn';
    static public $gsnCookiePath = '/';
    static public $gsnCookieDomain = null;
    static public $gsnSalt = 'rand-rts:*&^%$#@!';

	static public $validReferer = '{^https?://id.vatgia.com/}i';

	/**
	* Kiểm tra referer của request hiện tại
	*
	* @return boolean
	*/
	static function isRefererValid()
	{
		return true;
        if (!isset($_SERVER['HTTP_REFERER'])) return false;

        return preg_match(self::$validReferer, $_SERVER['HTTP_REFERER']);
	}

	/**
	* Lưu lại GSN vào cookie để so sánh sau này.
	*
	* @param string $gsn gsn sẽ được lưu
	* @param string $expiredTime thời điểm mà cookie _gsn sẽ bị hủy
	*/
    static function saveGSN($gsn, $expiredTime)
    {
    	$gsn = hash_hmac('md5', $gsn, self::$gsnSalt);
    	setcookie(self::$gsnCookieName, $gsn, $expiredTime, self::$gsnCookiePath, self::$gsnCookieDomain); // 1 day
    }

    static function clearGSN()
    {
		setcookie(self::$gsnCookieName, null, 946659600, self::$gsnCookiePath, self::$gsnCookieDomain); # 946659600 = 2000/01/01 00:00:00
    }

    /**
    * Kiểm tra xem GSN có trùng với GSN đã đc lưu trên cookie hay không?
    *
    * @param string $gsn GSN để kiểm tra
    *
    * @return boolean
    */
	static function checkGSN($gsn)
	{
    	if (!isset($_COOKIE[self::$gsnCookieName]) || empty($_COOKIE[self::$gsnCookieName])) return true;

    	$cGsn = $_COOKIE[self::$gsnCookieName];
    	$gsn = hash_hmac('md5', $gsn, self::$gsnSalt);

    	return $gsn == $cGsn;
	}

    static public function returnImage()
    {
        header('Content-Type: image/gif');

        # this is an image with 1pixel x 1pixel
        $img = base64_decode('R0lGODdhAQABAPAAAL6+vgAAACwAAAAAAQABAAACAkQBADs=');
        echo $img;
    }
}