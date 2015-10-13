<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Model;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Media implements ServiceLocatorAwareInterface{
	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	const TYPE_CRM_LEAD_TEMPLATE_EXCEL = 1;
	const TYPE_CRM_CONTRACT_FILE = 2;
	const TYPE_WORK_PROJECT_FILE = 3;
	const TYPE_CRM_ACITITY_PHONECALL = 4;
	const TYPE_WORK_TASK_FILE = 5;
	const TYPE_DOCUMENT_DOCUMENT_FILE = 6;
	const TYPE_COMPANY_ANNOUNCEMENT_FILE =7;
	const TYPE_WORK_MEETING_FILE   =   8;
	const TYPE_HRM_TEST_TEMPLATE_EXCEL   =   9;
	const TYPE_IDEA_FILE = 10;

	static  function getUploadTemploraryFolderPath(){
	    return BASE_PATH . '/public/media/templorary/';
	}
	/**
	 *
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function __construct($serviceLocator){
		$this->setServiceLocator($serviceLocator);
	}

	/**
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		$this->serviceLocator = $serviceLocator;
	}

	/**
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	public function getServiceLocator()
	{
		return $this->serviceLocator;
	}

	public function uploadContractFile($contractId){
		if(!$contractId){
			return array(
				'code' => 0,
				'messages' => 'Dữ liệu không hợp lệ'
			);
		}
		$contract = new \Crm\Model\Contract();
		$contract->setId($contractId);
		$contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
		if(!$contractMapper->get($contract)){
			return array(
				'code' => 0,
				'messages' => 'Không tìm thấy hợp đồng'
			);
		}

		$adapter = new \Zend\File\Transfer\Adapter\Http();
		$targetFolder = BASE_PATH.'/public/media/contract/'.$contract->getId();
		if (!file_exists($targetFolder)) {
			$oldmask = umask(0);
			mkdir($targetFolder, 0777, true);
			umask($oldmask);
		}
		$adapter->setDestination($targetFolder);

	}

    static function getFileIconClass($fileName){
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        if($ext){
            switch ($ext){
            	case 'gif':
            	case 'png':
            	case 'jpg':
            	case 'jpeg':
            	    return 'fa-file-image-o';
            	case 'pdf':
            	    return 'fa-file-pdf-o';
            	case 'doc':
            	case 'docx':
            	    return 'fa-file-word-o';
            	case 'xls':
            	case 'xlsx':
            	case 'csv':
            	    return 'fa-file-excel-o';
            	case 'ppt':
            	case 'pptx':
            	    return 'fa-file-powerpoint-o';
            	case 'mp4':
            	case 'mkv':
            	case 'flv':
            	case 'avi':
            	    return 'fa-file-movie-o';
            	case 'mp3':
            	    return 'fa-file-audio-o';
            	default: return 'fa-file';
            }
        }
        return 'fa-file';
    }

    static function getFileType($fileName){
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        if($ext){
            switch ($ext){
                case 'gif':
                case 'png':
                case 'jpg':
                case 'jpeg':
                    return 'image';
                case 'pdf':
                    return 'pdf';
                case 'doc':
                case 'docx':
                    return 'msword';
                case 'xls':
                case 'xlsx':
                case 'csv':
                    return 'msexcel';
                case 'ppt':
                case 'pptx':
                    return 'mspowerpoint';
                case 'mp4':
                case 'mkv':
                case 'flv':
                case 'avi':
                    return 'video';
                case 'mp3':
                    return 'audio';
                default: return '';
            }
        }
        return '';
    }
}