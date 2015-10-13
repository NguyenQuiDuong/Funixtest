<?php
/**
 * @category    ERP library
 * @copyright   http://nhanh.vn
 * @license     http://nhanh.vn/license
 */

namespace Home\Model;
use DateTime;

class DateBase extends DateTime
{
	// define based on locale later
	const COMMON_DATE_FORMAT = 'Y-m-d';
	const COMMON_TIME_FORMAT = 'H:i:s';
	const DISPLAY_DATE_FORMAT = 'd/m/Y';
	const COMMON_DATETIME_FORMAT = 'Y-m-d H:i:s';
	const DISPLAY_DATETIME_FORMAT = 'd/m/Y H:i:s';


	const FILEPATH_DATE_FORMAT = 'Ymd';

	/**
	 * @author VanCK
	 * @return string
	 */
	public static function getDisplayDateFormat()
	{
		return self::DISPLAY_DATE_FORMAT;
	}

	/**
	 * @author VanCK
	 * @return string
	 */
	public static function getDisplayDateTimeFormat()
	{
		return self::DISPLAY_DATETIME_FORMAT;
	}

	/**
	 * @author VanCK
	 * @return string
	 */
	public static function getCurrentDate()
	{
		return date(self::COMMON_DATE_FORMAT);
	}

	/**
	 * @author HungPX
	 * @return string
	 */
	public static function getCurrentTime()
	{
	    return date(self::COMMON_TIME_FORMAT);
	}

	/**
	 * @author VanCK
	 * @return string
	 */
	public static function getCurrentDateTime()
	{
		return date(self::COMMON_DATETIME_FORMAT);
	}

	/**
	 * @author VanCK
	 * convert display date to common date format
	 * @param string $d
	 */
	public static function toCommonDate($d)
	{
		if($d){
			$date = DateTime::createFromFormat(self::DISPLAY_DATE_FORMAT, $d);
			if($date){
			    return $date->format(self::COMMON_DATE_FORMAT);
			}

		}
		return '';
	}

	/**
	 * @author VanCK
	 * convert display date to common datetime format
	 * @param string $d
	 */
	public static function toCommonDateTime($d)
	{
		if($d){
			$date = DateTime::createFromFormat(self::DISPLAY_DATETIME_FORMAT, $d);
			if($date){
			    return $date->format(self::COMMON_DATETIME_FORMAT);
			}
		}
		return '';
	}

	/**
	 * @author VanCK
	 * convert common date to display date format
	 * @param string $d
	 */
	public static function toDisplayDate($d)
	{
		if($d){
			$date = DateTime::createFromFormat(self::COMMON_DATE_FORMAT, $d);
			if($date){
			    return $date->format(self::DISPLAY_DATE_FORMAT);
			}
		}
		return '';
	}

	/**
	 * @author VanCK
	 * convert common datetime to display datetime format
	 * @param string $d
	 */
	public static function toDisplayDateTime($d)
	{
		if($d){
			$date = DateTime::createFromFormat(self::COMMON_DATETIME_FORMAT, $d);
			if($date){
			    return $date->format(self::DISPLAY_DATETIME_FORMAT);
			}

		}
		return '';
	}

	/**
	 * @author Hungpx
	 * convert common datetime to display time format
	 * @param string $d
	 */
	public static function toDisplayTime($d)
	{
	    if($d){
	        $date = DateTime::createFromFormat(self::COMMON_DATETIME_FORMAT, $d);
	        if($date){
	            return $date->format(self::COMMON_TIME_FORMAT);
	        }
	    }
	    return '';
	}

	/**
	 * @author KienNN
	 * get month different beween two date
	 * @param string $d
	 */
	public static function monthDiff($d1, $d2)
	{
		$ts1 = strtotime($d1);
		$ts2 = strtotime($d2);

		$year1 = date('Y', $ts1);
		$year2 = date('Y', $ts2);

		$month1 = date('m', $ts1);
		$month2 = date('m', $ts2);

		return (($year2 - $year1) * 12) + ($month2 - $month1);
	}

	/**
	 * @author KienNN
	 * get day different beween two date
	 * @param string $d
	 */
	public static function dayDiff($d1, $d2)
	{
		$ts1 = strtotime($d1);
		$ts2 = strtotime($d2);

		return ($ts2 - $ts1) / (60*60*24);
	}

	/**
	 * @author KienNN
	 * @param string $date
	 * @param string $format
	 * @return boolean
	 */
	public static function validateDate($date, $format = 'Y-m-d H:i:s'){
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
	}

	/**
	 * @author AnhNV
	 * add Second
	 */
	public static function addSec($time, $sec, $format = 'Y-m-d H:i:s'){
		$date = new DateTime($time);
		$date->add(new \DateInterval('PT'. $sec .'S'));
		return $date->format($format);
	}

	/**
	 * @author KienNN
	 * @param string $date
	 * @param string $toFormat
	 * @param string $fromFormat
	 * @return boolean
	 */
	static function toFormat($date, $toFormat, $fromFormat = 'Y-m-d H:i:s'){
		$d = DateTime::createFromFormat($fromFormat, $date);
		return $d->format($toFormat);
	}

	/**
	 * @author KienNN
	 * @param String $date
	 */
	static function getWeekOfMonth($dateString){
	    return date('W', strtotime($dateString)) - date('W', strtotime(date('01-m-Y', $dateString))) + 1;
	}

	static function displayDayOfWeek($date, $minimumDisplay=true){
	    $date = date_create_from_format(DateBase::COMMON_DATE_FORMAT, $date);
	    if($date){
	        if($minimumDisplay){
	            switch ($date->format('w')){
	            	case 0: return 'CN';
	            	case 1: return 'T2';
	            	case 2: return 'T3';
	            	case 3: return 'T4';
	            	case 4: return 'T5';
	            	case 5: return 'T6';
	            	case 6: return 'T7';
	            	default: return 'N/A';
	            }
	        } else {
	            switch ($date->format('w')){
	            	case 0: return 'Chủ nhật';
	            	case 1: return 'Thứ 2';
	            	case 2: return 'Thứ 3';
	            	case 3: return 'Thứ 4';
	            	case 4: return 'Thứ 5';
	            	case 5: return 'Thứ 6';
	            	case 6: return 'Thứ 7';
	            	default: return 'Không xác định';
	            }
	        }
	    } else {
	        return 'Không xác định';
	    }


	}
}