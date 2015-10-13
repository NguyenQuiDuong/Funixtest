<?php
namespace User\Service;

/**
 * Class này sử dụng mcrypt để mã hóa 1 khối dữ liệu thông qua thuật toán AES (Rijndael-128)
 *
 * Ticket mã hóa dữ liệu theo nguyên tắc sau :
 *     1. Dữ liệu đương serialize thông qua JSON Encode => plain_text
 *     2. Sử dụng {@link $secretKey} để mã hóa plain_text => cipher_text
 *
 * @author HocĐT <hocdt85@gmail.com>
 */
class SymetricTicket
{
    protected $_data = array();

    /**
     * Key bí mật dùng chung để mã hóa
     *
     * @var string có format theo dạng key|iv trong đó key và id là 2 chuỗi nhị phân đã được encode
     * theo dạng hexa, có độ dài tương ứng với key và iv của thuật toán {@link SYMETRIC_CRYPTO_ALGORITHM}. Với giá trị mặc định là (32, 16) bytes tương ứng với (64, 32) ký tự hexa
     */
    public $secretKey = '7b674ca3487e2afdfeaf201cf03e704a8845be5a705c0b35a8723ac495cb05ed|01e6d28a6328a13c501fe7127b282d74';

    const SYMETRIC_CRYPTO_ALGORITHM = 'rijndael-128';

    public function __construct($data=array(), $secretKey=null)
    {
        $this->_data = $data;
        //$this->secretKey = $secretKey;
    }

    protected function serialize($data)
    {
        $json = json_encode($data);
        $crc = hash_hmac('md5', $json, $this->secretKey);
        return $crc.$json;
    }

    protected function unserialize($data)
    {
        $crc = substr($data, 0, 32); // md5 has 32 hex characters
        $json = substr($data, 32);
        if (hash_hmac('md5', $json, $this->secretKey) != $crc) return null;

        $data = json_decode($json, true);
        if ($data === false || is_null($data)) return null;

        return $data;
    }

    /**
     * Lấy dữ liệu hiện tại
     *
     * @return mixed
     */
    function getData() { return $this->_data; }

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
        $plainText = $this->serialize($this->_data);
        list($key, $iv) = self::unserializeKeyAndIV($this->secretKey);
        if (empty($key) || empty($iv)) return null;

        $cipherText = self::symetricEncrypt($plainText, $key, $iv);

        return base64_encode($cipherText);
    }

    /**
     * Decrypt 1 token để có dữ liệu
     *
     * @param string $token xâu cần giải mã
     *
     * @return boolean thành công hay không?
     */
    function decrypt($token)
    {
        $cipherText = base64_decode($token, true);

        list($key, $iv) = self::unserializeKeyAndIV($this->secretKey);

        $plainText = self::symetricDecrypt($cipherText, $key, $iv);
        if (empty($plainText)) return false;

        $this->_data = $this->unserialize($plainText);

        return !is_null($this->_data);
    }
}