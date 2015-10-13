<?php
namespace User\Service;
/**
* Class này cho phép mã hóa 1 signin token của SSO
*
* Quá trình mã hóa được tiến hành như sau:
*   1. Dữ liệu được serialize thành string (plain_text)
*   2. plain_text sẽ được mã hóa bằng thuật toán AES-256 (Rijndael-128) thông qua {@link $secretKey} => cipher_text
*   3. Ký dựa trên cipher_text bằng thuật toán RSA + SHA1 (sử dụng {@link $rsaPrivateKey}) => signature
*   4. Ghép signature và cipher_text lại thành 1 xâu, encode = base64 => token
*
* Quá trình giải mã như sau:
*   1. Từ token, decode = base64, tách ra được signature và ciphter_text
*   2. Kiểm tra chữ ký trên cipher_text theo thuật toán RSA + SHA1 (sử dụng {@link $rsaPublicKey}): if failed => failed
*   3. Giải mã cipher_text được plain_text (sử dụng {@link $secretKey})
*   4. Unserialize plain_text được data
*
* Để sử dụng cho service:
* <code>
*   $token = new SignInToken();
*   $token->secretKey = '...'; # secret key đã thống nhất với id.vatgia.com
*   $token->rsaPublicKey = '....'; # mã công khai do id.vatgia.com cung cấp
*   if ($token->decrypt() == SignInToken::ERROR_NONE) {
*       $data = $token->getData();
*   }
* </code>
*
* @author Tarzan <hocdt85@gmail.com>
*/
class SignInToken
{
    const ERROR_NONE        =   0x00;
    const ERROR_EXPIRED     =   0xF1;
    const ERROR_INVALID     =   0xF2;
    const ERROR_CORRUPTED   =   0xF4;

    const SYMETRIC_CRYPTO_ALGORITHM = MCRYPT_RIJNDAEL_128;
    const SIGNATURE_ALGORITHM = OPENSSL_ALGO_SHA1;

    /**
    * Thời gian tồn tại của SignInToken này, in second
    *
    * @var integer
    */
    public $lifetime = 20;

    /**
    * Mã bí mật để ký
    *
    * @var string PEM formatted
    */
    public $rsaPrivateKey;

    /**
    * Mã công khai để ktra chữ ký
    *
    * @var string PEM formatted
    */
    public $rsaPublicKey;

    /**
    * Key bí mật dùng chung để mã hóa
    *
    * @var string có format theo dạng key|iv trong đó key và id là 2 chuỗi nhị phân đã được encode
    * theo dạng hexa, có độ dài tương ứng với key và iv của thuật toán {@link SYMETRIC_CRYPTO_ALGORITHM}. Với giá trị mặc định là (32, 16) bytes tương ứng với (64, 32) ký tự hexa
    */
    public $secretKey;

    public $_data;

    public function __construct($data=array(), $secretKey=null, $rsaPublicKey=null, $rsaPrivateKey=null)
    {
        $this->_data = $data;
        $this->secretKey = $secretKey;
        $this->rsaPublicKey = $rsaPublicKey;
        $this->rsaPrivateKey = $rsaPrivateKey;
    }

    protected static function serialize($data)
    {
        return json_encode($data);
    }

    protected static function unserialize($data)
    {
        $data = json_decode($data, true);
        if ($data === false || is_null($data)) return false;

        return $data;
    }

    /**
    * Tách key & init vector từ 1 string để sử dụng cho AES
    *
    * @param string $str xâu combine
    *
    * @return array array($key, $iv)
    */
    protected static function unserializeKeyAndIV($str)
    {
        $algo = mcrypt_module_open(self::SYMETRIC_CRYPTO_ALGORITHM, '', MCRYPT_MODE_CBC, '');

        $keySize = 2*mcrypt_enc_get_key_size($algo);
        $ivSize = 2*mcrypt_enc_get_iv_size($algo);

        $pattern = '/^([0-9a-f]{'.$keySize.'}).*([0-9a-f]{'.$ivSize.'})$/i';

        if (preg_match($pattern, $str, $m)) {
            list(,$key,$iv) = $m;
            $key = hex2bin($key);
            $iv = hex2bin($iv);
            return array($key, $iv);
        }

        user_error('Invalid secret key', E_USER_ERROR);
        return array(null, null);
    }

    /**
    * Mã hóa 1 chuỗi thông tin bằng thuật toán {@link self::SYMETRIC_CRYPTO_ALGORITHM}
    *
    * @param string $plainText dữ liệu cần mã hóa
    * @param string $key key sẽ dùng để mã hóa
    * @param string $iv init vector sẽ dùng để mã hóa
    *
    * @return string xâu đã mã hóa, NULL nếu không thành công
    */
    static protected function symetricEncrypt($plainText, $key, $iv)
    {
        $algo = mcrypt_module_open(self::SYMETRIC_CRYPTO_ALGORITHM, '', MCRYPT_MODE_CBC, '');
        assert('$algo !== false');

        $x = mcrypt_generic_init($algo, $key, $iv);
        assert('$x !== false && $x >= 0');
        if ($x === false || $x < 0) return null;


        $cipherText = mcrypt_generic($algo, $plainText);
        mcrypt_generic_deinit($algo);
        mcrypt_module_close($algo);

        return $cipherText;
    }

    /**
    * Giải mã 1 chuỗi thông tin bằng thuật toán {@link self::SYMETRIC_CRYPTO_ALGORITHM}
    *
    * @param string $cipherText dữ liệu cần giải mã
    * @param string $key key sẽ dùng để giải mã
    * @param string $iv init vector sẽ dùng để giải mã
    *
    * @return string dữ liệu sau khi giải mã, NULL là không giải mã được
    */
    static protected function symetricDecrypt($cipherText, $key, $iv)
    {
        $algo = mcrypt_module_open(self::SYMETRIC_CRYPTO_ALGORITHM, '', MCRYPT_MODE_CBC, '');
        assert('$algo !== false');

        $x = mcrypt_generic_init($algo, $key, $iv);
        assert('$x !== false && $x >= 0');
        if ($x === false || $x < 0) return null;

        $plainText = mdecrypt_generic($algo, $cipherText);
        mcrypt_generic_deinit($algo);
        mcrypt_module_close($algo);

        $plainText = rtrim($plainText, "\0");

        return $plainText;
    }

    /**
    * Tạo chữ ký cho 1 xâu sử dụng RSA + SHA1
    *
    * @param string $text dữ liệu cần tạo chữ ký
    * @param string $privateKey key bí mật để ký, {@link http://www.php.net/manual/en/function.openssl-pkey-get-private.php}
    *
    * @return string chữ ký, NULL nếu failed
    */
    static protected function makeSignature($text, $privateKey)
    {
        $privateKey = openssl_pkey_get_private($privateKey);
        assert('$privateKey !== false');
        if ($privateKey === false) return null;

        $x = openssl_sign($text, $signature, $privateKey, self::SIGNATURE_ALGORITHM);
        assert('$x != false');
        if ($x === false) return null;

        return $signature;
    }

    /**
    * Giải mã 1 chuỗi bằng thuật toán RSA
    *
    * @param string $text dữ liệu cần ktra
    * @param string $signature chữ ký cần ktra
    * @param string $publicKey key công khai để ktra chữ ký, {@link http://www.php.net/manual/en/function.openssl-pkey-get-private.php}
    * @return bool TRUE or FALSE
    */
    static protected function verifySignature($text, $signature, $publicKey)
    {
        $publicKey = openssl_pkey_get_public($publicKey);
        assert('$publicKey !== false');
        if ($publicKey === false) return false;

        return openssl_verify($text, $signature, $publicKey, self::SIGNATURE_ALGORITHM) === 1;
    }

    /**
    * Lấy dữ liệu hiện tại
    *
    * @return mixed
    */
    function getData() { return $this->_data; }

    function __toString()
    {
        return $this->encrypt();
    }

    /**
    * Encrypt dữ liệu hiện tại để có string
    *
    * @return string NULL nếu không thành công
    */
    function encrypt()
    {
        $tokenData = array(
            'expired_time'=>time() + $this->lifetime,
            'data'=>$this->getData(),
        );
        $plainText = $this->serialize($tokenData);
        list($key, $iv) = self::unserializeKeyAndIV($this->secretKey);
        if (empty($key) || empty($iv)) return null;

        $cipherText = self::symetricEncrypt($plainText, $key, $iv);
        if (empty($cipherText)) return null;

        $signature = self::makeSignature($cipherText, $this->rsaPrivateKey);
        if (empty($signature)) return null;
        $sLen = strlen($signature);

        $meta = chr(($sLen >> 8) & 0xff) . chr($sLen & 0xff);

        $token = $meta.$signature.$cipherText;

        return base64_encode($token);
    }

    /**
    * Decrypt 1 token để có dữ liệu
    *
    * @param string $token xâu cần giải mã
    *
    * @return integer self::ERROR_XXX
    */
    function decrypt($token)
    {
        $token = base64_decode($token, true);
        $meta = substr($token, 0, 2);
        $token = substr($token, 2);

        $sLen = ord($meta[0]);
        $sLen = ($sLen << 8) | ord($meta[1]);

        $signature = substr($token, 0, $sLen);
        $cipherText = substr($token, $sLen);
        if (!self::verifySignature($cipherText, $signature, $this->rsaPublicKey))
            return self::ERROR_INVALID;
        list($key, $iv) = self::unserializeKeyAndIV($this->secretKey);

        $plainText = self::symetricDecrypt($cipherText, $key, $iv);
        if (empty($plainText)) return self::ERROR_CORRUPTED;
        $tokenData = self::unserialize($plainText);

        $expiredTime = isset($tokenData['expired_time']) ? intval($tokenData['expired_time']) : 0;
        if (time() >= $expiredTime) return self::ERROR_EXPIRED;

        $this->_data = isset($tokenData['data']) ? $tokenData['data'] : array();

        return self::ERROR_NONE;
    }
}
