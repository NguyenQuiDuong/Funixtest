<?php
/**
 * @category    Shop99 library
 * @copyright   http://nhanh.vn
 * @license     http://nhanh.vn/license
 */
namespace Home\Model;

class Format
{
	const CHARSET_NUMERIC 		= "0123456789";
	const CHARSET_ALPHABET 		= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	const CHARSET_ALPHANUMERIC 	= "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	/**
	 * replace Vietnamese characters
	 *
	 * @author	vanCK
	 * @param string $text
	 * @return string
	 */
	public static function removeSigns($text)
	{
		if(!$text) {
			return "";
		}
		$vnSigns = array(
			'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
			'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
			'd' => 'đ',
			'D' => 'Đ',
			'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
			'E' => 'É|É|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
			'i' => 'í|ì|ỉ|ĩ|ị',
			'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
			'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
			'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
			'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
			'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
			'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
			'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
		);
		foreach($vnSigns as $unsign => $signs){
			$text = preg_replace("/($signs)/", $unsign, $text);
		}
		return $text;
	}

	/**
	 * @author	vanCK
	 * @param string $text
	 * @param boolean $toLower
	 * @return string
	 */
	static public function slugify($text, $toLower = true)
	{
		if (empty($text)) {
			return '';
		}
		$text = trim(self::removeSigns($text));
		$text = preg_replace('/[^a-zA-Z0-9\s.?!]/', '', $text);
		$text = str_replace(array(' - ', ' ', '&', '--'), '-', $text);

		if($toLower) {
			$text = strtolower($text);
		}
		return $text;
	}

	/**
	 * @author	vanCK
	 *
	 * @param string $charset
	 * @param int $length
	 * @param string $prefix
	 * @param string $suffix
	 *
	 * @return string
	 */
	public static function generateRandom($charset, $length = 6, $prefix = null, $suffix = null)
	{
		$code = "";
		for ($i=0; $i<$length; $i++) {
			$code .= $charset[rand(0, strlen($charset) - 1)];
		}
		if($prefix) {
			$code = $prefix . $code;
		}
		if($suffix) {
			$code = $code . $suffix;
		}
		return $code;
	}

	/**
	 * @author KienNN
	 * copy from Logistics project
	 * convert to number
	 */
	public static function toNumber($number, $options = null) {
		$number = round($number, 2);
		if(!$number) {
			return '';
		}
		$decimal = '';
		if(strpos($number, ".")) {
			list($number, $decimal) = explode(".", $number);
		}
		$result = '';
		$sign = '';
		if($number < 0) {
			$sign = '-';
			$number = $number + ($number * (-2));
		}
		while(strlen($number) > 3) {
			$result = '.' . substr($number, strlen($number)-3, 3) . $result;
			$number = substr($number, 0, strlen($number)-3);
		}
		$return = $sign . $number . $result;
		if($decimal) {
			$return .= ','. $decimal;
		}
		return $return;
	}
	
	/**
	 * @author Hungpx
	 * @param number Eg: 30,400,4000, $option = float,int,...
	 * convert to number
	 */
	public static function convertToNumber($string, $options = null) {
	    if (!$string){
	    	return null;
	    }
	    $numberArray = explode(',', $string );
	    $numberStr = implode('', $numberArray);
	    return $numberStr;
	    
	}

	public function toNumberFormat ( $number , $decimals = 0 , $dec_point = ',' , $thousands_sep = '.' ){
		if(!$number){
			return  '';
		}
		return number_format($number, $decimals, $dec_point, $thousands_sep);
	}

	public static function skipInvalidXmlCharacter($string){
		return trim(str_replace(['"', "'", '&', '>', '<'], '', $string));
	}
	/**
	 * @author DuongNQ
	 */
	public static function toDisplayFileSize($filesize){
	    if ($filesize == 0){
	        return '';
	    }
	    if ($filesize < 1024) {
	        return  $filesize . ' Byte';
	    } elseif ($filesize < 1048576) {
	        return round($filesize / 1024, 2) . ' KB';
	    } elseif ($filesize < 1073741824) {
	        return round($filesize / 1048576, 2) . ' MB';
	    } elseif ($filesize < 1099511627776) {
	        return round($filesize / 1073741824, 2) . ' GB';
	    } elseif ($filesize < 1125899906842624) {
	        return round($filesize / 1099511627776, 2) . ' TB';
	    }
	}

	/**
	 * @author KienNN
	 * @param unknown $string
	 * @param unknown $word_limit
	 * @return string
	 */
	function limit_words($string, $word_limit = 15, $suffix = '...')
	{
	    $words = explode(" ",$string);
	    $result = implode(" ",array_splice($words,0,$word_limit));
	    if(count($words) > $word_limit){
	        $result .= $suffix;
	    }
        unset($words);
	    return $result;
	}

	static function displaySetItems($items, $suffix = null, $limit = null){
	    if(!$items || !is_array($items) || !count($items)){
	        return '';
	    }
	    $suffix = $suffix?:' - ';
	    $arr = [];
	    $index = 0;
	    foreach ($items as $item){
	        if($item){
	            $arr[] = $item;
	            $index++;
	        }
	        if($limit && $index >= $limit){
	            break;
	        }
	    }
	    if(!count($arr)){
	        return '';
	    }
	    return implode($suffix, $arr);
	}

	static function toComparedArrays($array1, $array2){
	    $before = $array1;
        $after = $array2;
        if($before && is_array($before) && count($before)){
            if($after && is_array($after) && COUNT($after)) {
                foreach ($before as $kb => $b) {
                    foreach ($after as $ka => $a) {
                        if($kb == $ka) {
                            if(!is_array($a) && !is_array($b)){
                                if($b == $a) {
                                    unset($before[$kb]);
                                    unset($after[$ka]);
                                }
                            } else {
                                list($b, $a) = self::toComparedArrays($b, $a);
                                $before[$kb] = $b;
                                $after[$ka] = $a;
                            }

                        }
                    }
                }
                $beforeconvert	= $before;
                $afterconvert	= $after;
            } else {
                $afterconvert 	= $after;
                $beforeconvert	= $before;
            }
        } else {
            $beforeconvert 	= $before;
            $afterconvert	= $after;
        }
        return array($beforeconvert, $afterconvert);
	}

	static function splitFullName($fullName){
	    if(!is_string($fullName)){
	        return null;
	    }
	    $names = explode(' ', trim($fullName));
	    $lastName = array_shift($names);
	    $firstName = array_pop($names);
	    $middleName = null;
	    if(count($names)){
	        $middleName = trim(implode(' ', $names));
	    }
	    return array($lastName, $middleName, $firstName);
	}
}