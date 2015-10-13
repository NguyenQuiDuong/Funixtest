<?php
/**
 * @copyright   http://erp.nhanh.vn
 * @license		http://erp.nhanh.vn/license
 */

namespace Home\Service;

use Home\Model\DateBase;

class Uri
{
	const MULTIMEDIA_SERVER = "http://erp.nhanh.vn";

    /**
     * @param Object $obj
     * @return string
     */
    public static function getSavePath($obj, $options = null)
    {
        switch ($obj) {
            case $obj instanceof \Work\Model\TaskFile:
                $path = DateBase::toFormat($obj->getCreatedDateTime(), 'Ymd');
                return MEDIA_PATH . '/work/attachfile/'.$path.'/'.$obj->getTaskId();
                break;
            case $obj instanceof \Work\Model\MeetingFile:
                $path = DateBase::toFormat($obj->getCreatedDateTime(), 'Ymd');
                return MEDIA_PATH . '/projects/meetings/'.$path.'/'.$obj->getMeetingId();
                break;
            case $obj instanceof \Hrm\Model\Recruitment\Candidate:
                $path = DateBase::toFormat($obj->getCreatedDate(), 'Ymd', 'Y-m-d');
                if($obj->getOptions()['temp'] == 1){
                    return MEDIA_PATH . '/hrm/candidate/'.$path;
                }
                return MEDIA_PATH . '/hrm/candidate/'.$path;
                break;
            case $obj instanceof \Document\Model\DocumentFile:
                if(!$obj->getDocumentId()){
                    return MEDIA_PATH . '/document/documents/temp';
                }

                return MEDIA_PATH . '/document/documents/'
                    .DateBase::toFormat($obj->getCreatedDateTime(), 'Ymd', DateBase::COMMON_DATETIME_FORMAT).'/'.$obj->getDocumentId();
                break;
            case $obj instanceof \Crm\Model\Contract\File:
            	return BASE_PATH.'/public/media/contracts/'.$obj->getContractId();
            case $obj instanceof \Company\Model\AnnouncementFile:
            	/* if($obj->getOption('companyId')){
            		return MEDIA_PATH.'/company/announcement/'.$obj->getOption('companyId').'/'.$obj->getAnnouncementId();
            	} elseif ($options) {
            		return MEDIA_PATH.'/company/announcement/'.$options.'/'.$obj->getAnnouncementId();
            	} else {
            		return MEDIA_PATH.'/company/announcement/temp/'.$obj->getAnnouncementId();
            	} */


            	if(!$obj->getFilePath()){
            		return MEDIA_PATH.'/announcement/temp/'.$obj->getAnnouncementId();
            	}
            	return MEDIA_PATH.'/announcement/'.$obj->getFilePath().'/'.$obj->getAnnouncementId();

            case $obj instanceof \Idea\Model\File:
               	return MEDIA_PATH.'/idea/'.$obj->getFilePath().'/'.$obj->getIdeaId();
            case $obj instanceof \User\Model\User:
                if (!$obj->getCreatedDateTime()){
                    return MEDIA_PATH.'/users/default/'.$obj->getId().'/';
                }
                $datePath = DateBase::toFormat($obj->getCreatedDateTime(), 'Ymd');
                return MEDIA_PATH.'/user/'.$datePath.'/'.$obj->getId().'/';
            default: MEDIA_PATH.'/temp/';
        }
        return '';
    }

    /**
     * @param Object $obj
     * @param string|null $thumbnail
     * @return string
     */
    public static function getViewPath($obj, $thumbnail = null)
    {
        switch ($obj) {
            case $obj instanceof \Crm\Model\Contract\File:
            	if ($obj->getFileName()) {
            		return '/media/contracts/'.$obj->getContractId().'/'.$obj->getFileName();
            	}
            	break;
            case $obj instanceof \Hrm\Model\Recruitment\Candidate:
                $filePath = DateBase::createFromFormat(DateBase::COMMON_DATE_FORMAT, $obj->getCreatedDate())->format('Ymd');
                return '/media/hrm/candidate/'.$filePath.'/'.$obj->getFileName();
                break;

            case $obj instanceof \Work\Model\TaskFile:
            	$filePath = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $obj->getCreatedDateTime())->format('Ymd');
            	return '/media/work/attachfile/'. $filePath . '/' . $obj->getTaskId() . '/' . $obj->getFileName();
            	break;
            case $obj instanceof \Work\Model\MeetingFile:
           	    $filePath = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $obj->getCreatedDateTime())->format('Ymd');
           	    return '/media/projects/meetings/'. $filePath .'/'.$obj->getMeetingId().'/'  . $obj->getFileName();
           	    break;
           	case $obj instanceof \Company\Model\AnnouncementFile:
           	    //return '/media/company/announcement/'.$obj->getOption('companyId').'/'.$obj->getAnnouncementId().'/'.$obj->getFileName();
           	    if(!$obj->getFilePath()){
           	    	return '/media/announcement/temp/'.$obj->getAnnouncementId();
           	    } else {
           	    	return '/media/announcement/'.$obj->getFilePath().'/'.$obj->getAnnouncementId();
           	    }
           		break;
           	case $obj instanceof \Document\Model\DocumentFile:
           	    $filePath = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $obj->getCreatedDateTime())->format('Ymd');
           	    return '/media/document/documents/'.$filePath.'/'.$obj->getDocumentId().'/'.$obj->getFileName();
           	    break;
           	case $obj instanceof \Idea\Model\File:
           	    $filePath = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $obj->getCreatedDateTime())->format('Ymd');
           	    return '/media/idea/'.$obj->getFilePath().'/'. $obj->getIdeaId().'/'.$obj->getFileName();
           	    break;
           	case $obj instanceof \User\Model\User:
           	    if (!$obj->getCreatedDateTime()){
           	        return '/media/users/default/'.$obj->getId().'/';
           	    }
           	    $datePath = DateBase::toFormat($obj->getCreatedDateTime(), 'Ymd');
           		return '/media/user/'.$datePath.'/'.$obj->getId().'/'.$obj->getAvatar();
           		break;
        }
        return '';
    }


    /**
     * @author AnhNV
     * @param $str
     * @return mixed
     * make link from string
     */
    public static function makeLinks($str) {
    	$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
    	$urls = array();
    	$urlsToReplace = array();
    	if(preg_match_all($reg_exUrl, $str, $urls)) {
    		$numOfMatches = count($urls[0]);
    		$numOfUrlsToReplace = 0;
    		for($i=0; $i<$numOfMatches; $i++) {
    			$alreadyAdded = false;
    			$numOfUrlsToReplace = count($urlsToReplace);
    			for($j=0; $j<$numOfUrlsToReplace; $j++) {
    				if($urlsToReplace[$j] == $urls[0][$i]) {
    					$alreadyAdded = true;
    				}
    			}
    			if(!$alreadyAdded) {
    				array_push($urlsToReplace, $urls[0][$i]);
    			}
    		}
    		$numOfUrlsToReplace = count($urlsToReplace);
    		for($i=0; $i<$numOfUrlsToReplace; $i++) {
    			$str = str_replace($urlsToReplace[$i], "<a href=\"".$urlsToReplace[$i]."\" target='_blank' >".$urlsToReplace[$i]."</a> ", $str);
    		}
    		return $str;
    	} else {
    		return $str;
    	}
    }

    public static function buildAutoHttp($uri, $params = null) {
    	// $locale = Zend_Registry::get('locale');

        if(in_array(getenv('APPLICATION_ENV'), ['development', 'localhost'])){
            $result = 'http://'. $_SERVER['HTTP_HOST'] . $uri;
        } else {
            $result = 'https://'. $_SERVER['HTTP_HOST'] . $uri;
        }

    	// append params to uri
    	if(is_array($params)) {
    		foreach ($params as $param => $value) {
    			if(strpos($result, '?')) {
    				$result .= "&$param=$value";
    			} else {
    				$result .= "?$param=$value";
    			}
    		}
    	}
    	return $result;
    }

    /**
     * @param string $link
     * @param array $options
     */
    public static function autoLink($uri, $options = null)
    {
    	if(!is_array($options)) {
    		$options = [];
    	}
    	$curl = curl_init(self::buildAutoHttp($uri, $options));
    	if(getenv('APPLICATION_ENV') != 'production') {
    		//@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    	} else {
    		@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    	}

    	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 1);
    	curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    	curl_exec($curl);
    	curl_close($curl);
    }

    public static function build($uri, $params = null){
        $request = \Zend\Uri\UriFactory::factory($uri);
        if($params && is_array($params)){
            foreach ($request->getQueryAsArray() as $param=>$value){
                if(!isset($params[$param])){
                    $params[$param] = $value;
                }
            }
        }
        $request->setQuery($params);
        return $request->toString();


    }
}