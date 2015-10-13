<?php
/**
 * Home\Controller
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Home\Controller;

use Home\Controller\ControllerBase;
use Home\Model\Media;
use Zend\View\Model\JsonModel;
use Home\Model\DateBase;
use Home\Service\Uri;
use Home\Model\Format;

class MediaController extends ControllerBase{

	public function indexAction(){
	}

	public function downloadAction(){
		$type = $this->getRequest()->getQuery('type');
		$f = $this->getRequest()->getQuery('f');
		if(!$type || !$f){
			return $this->page404();
		}
		$file = '';
		$fileName = '';
		switch ($type){
			case Media::TYPE_CRM_LEAD_TEMPLATE_EXCEL:
				$file = BASE_PATH . '/public/media/leads/'.$f;
				$fileName = $f;
				break;
			case Media::TYPE_CRM_CONTRACT_FILE:
				$fileContract = new \Crm\Model\Contract\File();
				$fileContract->setId($f);
				$fileMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\FileMapper');
				if(!$fileMapper->get($fileContract)){
					return $this->page404();
				}
				$file = BASE_PATH.'/public/media/contracts/'.$fileContract->getContractId().'/'.$fileContract->getFileName();
				$fileName = $fileContract->getFileName();
				break;
			case Media::TYPE_WORK_PROJECT_FILE:
				$d = $this->getRequest()->getQuery('d');
				$document = new \Work\Model\Document();
				$documentFile = new \Work\Model\DocumentFile();
				$documentFile->setId($f);
				$document->setId($d);
				$documentMapper = $this->getServiceLocator()->get('\Work\Model\DocumentMapper');
				$documentFileMapper = $this->getServiceLocator()->get('\Work\Model\DocumentFileMapper');
				if(!$documentMapper->get($document) || !$documentFileMapper->get($documentFile)){
						return $this->page404();
						}

				$createdDate = $documentFileMapper->get($documentFile)->getCreatedDateTime();
				$createdDateFolder = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $createdDate)->format('Ymd');
				$file = MEDIA_PATH.'/projects/documents/'.$createdDateFolder.'/'.$documentFile->getFileName();
				$fileName = $documentFile->getFileName();
				break;
			case Media::TYPE_CRM_ACITITY_PHONECALL:
				$phoneCall = new \Crm\Model\Activity();
				$phoneCall->setId($f);
				$activityMapper = $this->getServiceLocator()->get('\Crm\Model\ActivityMapper');
				if(!$activityMapper->get($phoneCall)
					|| $phoneCall->getType() != \Crm\Model\Activity::TYPE_PHONECALL
					|| !$phoneCall->getFileName()){
					return $this->page404();
				}
				$file = BASE_PATH.'/public/media/phonecall/'.$phoneCall->getFilePath().'/'.$phoneCall->getFileName();
				$fileName = $phoneCall->getFileName();
				break;
			case Media::TYPE_WORK_TASK_FILE:
				$attachFile = new \Work\Model\TaskFile();
				$attachFile->setId($f);
				$attachFileMapper = $this->getServiceLocator()->get('\Work\Model\TaskFileMapper');
				if(!$attachFileMapper->get($attachFile)){
					return $this->page404();
				}

				//Check oldfile
				$file = Uri::getSavePath($attachFile).'/'.$attachFile->getFileName();
				$fileName = $attachFile->getFileName();
				break;
			case Media::TYPE_DOCUMENT_DOCUMENT_FILE:
				$documentFile = new \Document\Model\DocumentFile();
				$documentFile->setId($f);
				$documentFileMapper = $this->getServiceLocator()->get('\Document\Model\DocumentFileMapper');
				if(!$documentFile->getId() || !$documentFileMapper->get($documentFile)){
					return $this->page404();
				}

				$file = Uri::getSavePath($documentFile).'/'.$documentFile->getFileName();
				$fileName = $documentFile->getFileName();
				break;
			case Media::TYPE_COMPANY_ANNOUNCEMENT_FILE:{
				$d = $this->getRequest()->getQuery('d');
				$announcementFile=new \Company\Model\AnnouncementFile();
				$announcement=new \Company\Model\Announcement();
				$announcementFile->setId($f);
				$announcement->setId($d);
				$announcementMapper = $this->getServiceLocator()->get('\Company\Model\AnnouncementMapper');
				$announcementFileMapper = $this->getServiceLocator()->get('\Company\Model\AnnouncementFileMapper');
				if (!$announcementMapper->get($announcement) || !$announcementFileMapper->get($announcementFile)) {
					return null;
				}
				$createdDate=$announcementFileMapper->get($announcementFile)->getCreatedDateTime();
				$createdDateFolder= DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $createdDate)->format('Ymd');
				$file = Uri::getSavePath($announcementFile).'/'.$announcementFile->getFileName();
				$fileName= $announcementFile->getFileName();
				break;
			}
			case Media::TYPE_WORK_MEETING_FILE:
			    $d = $this->getRequest()->getQuery('d');
			    $meeting = new \Work\Model\Meeting();
			    $meetingFile = new \Work\Model\MeetingFile();
			    $meetingFile->setId($f);
			    $meeting->setId($d);
			    $meetingMapper = $this->getServiceLocator()->get('\Work\Model\MeetingMapper');
			    $meetingFileMapper = $this->getServiceLocator()->get('\Work\Model\MeetingFileMapper');
			    if(!$meetingMapper->get($meeting) || !$meetingFileMapper->get($meetingFile)){
			        return $this->page404();
			    }
                $meetingFile->setMeetingId($meeting->getId());
			    $createdDate = $meetingFileMapper->get($meetingFile)->getCreatedDateTime();
			    $createdDateFolder = DateBase::createFromFormat(DateBase::COMMON_DATETIME_FORMAT, $createdDate)->format('Ymd');
			    $file = Uri::getSavePath($meetingFile).'/'.$meetingFile->getFileName();
			    $fileName = $meetingFile->getFileName();
			    break;
		    case Media::TYPE_HRM_TEST_TEMPLATE_EXCEL:
		        $file = BASE_PATH . '/public/media/test/'.$f;
		        $fileName = $f;
		        break;
		    case Media::TYPE_IDEA_FILE:
		        $fileModel = new \Idea\Model\File();
		        $fileModel->setId($f);
		        $fileMapper = $this->getServiceLocator()->get('\Idea\Model\FileMapper');
		        if(!$fileMapper->get($fileModel)){
		            return $this->page404();
		        }
		        $file = \Home\Service\Uri::getSavePath($fileModel);
		        $fileName = $fileModel->getFileName();
		        break;

		}
		if ($file && !file_exists($file)) {
			die("File not found.");
		}
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		switch ($ext) {
			case "pdf": $ctype = "application/pdf";
			break;
			case "exe": $ctype = "application/octet-stream";
			break;
			case "zip": $ctype = "application/zip";
			break;
			case "doc":
			case "docx": $ctype = "application/msword";
			break;
			case "xls": $ctype = "application/vnd.ms-excel";
			break;
			case "xlsx": $ctype = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
			break;
			case "ppt":
			case "pptx": $ctype = "application/vnd.ms-powerpoint";
			break;
			case "gif": $ctype = "image/gif";
			break;
			case "png": $ctype = "image/png";
			break;
			case "jpe":
			case "jpeg":
			case "jpg": $ctype = "image/jpg";

			break;
			default: $ctype = "application/force-download";
		}

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: $ctype");
		//echo $file;die;
		header("Content-Disposition: attachment; filename=\"" .$fileName . "\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: " . filesize($file));

		readfile("$file") or die("File not found!");
		exit();
	}

	public function uploadAction(){
		$type = null;
		$jsonModel = New JsonModel();
		if($this->getRequest()->getPost('type')){
			$type = $this->getRequest()->getPost('type');
		} elseif ($this->getRequest()->getQuery('type')){
			$type = $this->getRequest()->getQuery('type');
		}
		if(!$type){
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['Dữ liệu không hợp lệ1']
					]);
			return $jsonModel;
		}
		switch ($type){
			case Media::TYPE_CRM_CONTRACT_FILE:
				return $this->uploadContractFile();
			case Media::TYPE_COMPANY_ANNOUNCEMENT_FILE:
			    return $this->uploadAnnouncementFile();
			case Media::TYPE_IDEA_FILE:
			    return $this->uploadIdeaFile();
			case Media::TYPE_WORK_TASK_FILE:
			    return $this->uploadTaskRequirementFileAction();
			case Media::TYPE_DOCUMENT_DOCUMENT_FILE;
			    return $this->uploadDocumentFile();
			default:
				$jsonModel->setVariables([
						'code' => 0,
						'messages' => ['Dữ liệu không hợp lệ']
						]);
				return $jsonModel;
		}
	}

	private function uploadContractFile(){
		$form = new \Home\Form\Media\ContentFile($this->getServiceLocator());
		if($this->getRequest()->getPost('contractId')){
			$form->addFileUploadRenameFilter($this->getRequest()->getPost('contractId'));
		}elseif ($this->getRequest()->getQuery('contractId')){
			$form->addFileUploadRenameFilter($this->getRequest()->getQuery('contractId'));
		} else {
			$form->addFileUploadRenameFilter('temp');
		}
		$jsonModel = New JsonModel();
		if($this->getRequest()->isPost()){
			$dataPopulate = array_merge_recursive(
					$this->getRequest()->getPost()->toArray(),
					$this->getRequest()->getQuery()->toArray(),
					$this->getRequest()->getFiles()->toArray());
			$form->setData($dataPopulate);
			if($form->isValid()){
				$contractFile = new \Crm\Model\Contract\File();
				$contractFile->exchangeArray($form->getData());

				$contractFileMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\FileMapper');
				if(!$contractFileMapper->isExisted($contractFile)){
					$contractFile->setCreatedById($this->user()->getIdentity());
					$contractFile->setCreatedDateTime(DateBase::getCurrentDateTime());
					$contractFileMapper->save($contractFile);
				}

				$jsonModel->setVariables([
						'code' => 1,
						'data' => ['id' => $contractFile->getId()]
				]);
			}else{
				$jsonModel->setVariables([
						'code' => 0,
						'messages' => $form->getMessagesForUpload()
				]);
			}

		} else {
			$jsonModel->setVariables([
					'code' => 0,
					'messages' => ['phải là request post']
					]);
		}
		return $jsonModel;
	}

	private function uploadAnnouncementFile(){
	    $form = new \Company\Form\AnnouncementFile($this->getServiceLocator());
	    $jsonModel = New JsonModel();
	    if($this->getRequest()->isPost()){
	        $dataPopulate = array_merge_recursive(
	            $this->getRequest()->getPost()->toArray(),
	            $this->getRequest()->getQuery()->toArray(),
	            $this->getRequest()->getFiles()->toArray());
	        $form->setData($dataPopulate);
	        if($form->isValid()){
	            if($this->getRequest()->getPost('announcementId') && $this->getRequest()->getPost('companyId')){
	                $form->addFileUploadRenameFilter($this->getRequest()->getPost('announcementId'),$this->getRequest()->getPost('companyId'));
	            }elseif ($this->getRequest()->getQuery('announcementId') && $this->getRequest()->getQuery('companyId')){
	                $form->addFileUploadRenameFilter($this->getRequest()->getQuery('announcementId'),$this->getRequest()->getQuery('companyId'));
	            } else {
	                $form->addFileUploadRenameFilter('temp');
	            }
	            $announcementFile = new \Company\Model\AnnouncementFile();
	            $announcementFile->exchangeArray($form->getData());

	            $announcementFileMapper = $this->getServiceLocator()->get('\Company\Model\AnnouncementFileMapper');
	            if(!$announcementFileMapper->isExisted($announcementFile)){
	                $announcementFile->setCreatedById($this->user()->getIdentity());
	                $oldname = Uri::getSavePath($announcementFile);
	                $announcementFile->setFilePath(DateBase::toFormat(DateBase::getCurrentDateTime(), 'Ymd'));
	                $announcementFile->setCreatedDateTime(DateBase::getCurrentDateTime());
	                $newname = Uri::getSavePath($announcementFile);
	                if (!file_exists($newname)) {
	                    $oldmask = umask(0);
	                    mkdir($newname,0777, true);
	                    umask($oldmask);
	                }
	                rename($oldname.'/'.$announcementFile->getFileName(), $newname.'/'.$announcementFile->getFileName());
	                $announcementFileMapper->save($announcementFile);
	            }

	            $jsonModel->setVariables([
	                'code' => 1,
	                'data' => ['id' => $announcementFile->getId()]
	            ]);
	        }else{
	            $jsonModel->setVariables([
	                'code' => 0,
	                'messages' => $form->getMessagesForUpload()
	            ]);
	        }

	    } else {
	        $jsonModel->setVariables([
	            'code' => 0,
	            'messages' => [
                    'phải là request post'
                ]
            ]);
        }
        return $jsonModel;
    }

    /**
     *
     * @author AnhNV
     *         delete Folder
     */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir")
                        $this->rrmdir($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    private function uploadIdeaFile(){
        $form = new \Home\Form\Media\IdeaFile($this->getServiceLocator());
        if($this->getRequest()->isPost()){
            $dataPopulate = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getQuery()->toArray(),
                $this->getRequest()->getFiles()->toArray());
            $form->setData($dataPopulate);
            if($form->isValid()){
                $formData = $form->getData();
                $file = new \Idea\Model\File();
                $file->exchangeArray($form->getData());
                $file->setCreatedById($this->user()->getIdentity());
                $file->setCreatedDateTime(DateBase::getCurrentDateTime());
                $fileMapper = $this->getServiceLocator()->get('\Idea\Model\FileMapper');
                $fileMapper->save($file);

                //=======rename file upload============
                $targetFolder = \Home\Service\Uri::getSavePath($file);

                if (!file_exists($targetFolder)) {
                    $oldmask = umask(0);
                    mkdir($targetFolder, 0777, true);
                    umask($oldmask);
                }


                rename($formData['fileUpload']['tmp_name'], $targetFolder.'/'.$file->getFileName());

                return $this->getJsonModel()->setVariables(array(
                	'code' => 1,
                    'data' => $form->getData()
                ));
            } else {
                $formData = $form->getData();
                if(isset($formData['fileUpload']['tmp_name'])){
                    @unlink($formData['fileUpload']['tmp_name']);
                }

                return $this->getJsonModel()->setVariables(array(
                	'code' => 0,
                    'messages' => $form->getErrorMessagesList(),
                ));
            }
        } else {
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => ['Dữ liệu không hợp lệ']
            ));
        }

    }

    public function uploadTaskRequirementFileAction(){
        $taskId = $this->getRequest()->getPost('taskId');
        if(!$taskId){
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => ['Dữ liệu không hợp lệ']
            ));
        }
        $task = new \Work\Model\Task();
        $task->setId($taskId);
        $taskMapper = $this->getServiceLocator()->get('\Work\Model\TaskMapper');
        if(!$taskMapper->get($task)){
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => ['Dữ liệu không hợp lệ']
            ));
        }
        $form = new \Home\Form\Media\TaskFile($this->getServiceLocator(), ['id' => $task->getId()]);
        $dataPopulate = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getQuery()->toArray(),
            $this->getRequest()->getFiles()->toArray());
        $dataPopulate['fileUpload']['name'] = Format::removeSigns($dataPopulate['fileUpload']['name']);
        $form->setData($dataPopulate);
        if($form->isValid()){
            $formData = $form->getData();
            $file = new \Work\Model\TaskFile();
            $file->setFileName($formData['fileUpload']['name']);
            $file->setFileSize($formData['fileUpload']['size']);
            $file->setTaskId($task->getId());
            $file->setCreatedById($this->user()->getIdentity());
            $file->setCreatedDateTime(DateBase::getCurrentDateTime());
            $fileMapper = $this->getServiceLocator()->get('\Work\Model\TaskFileMapper');
            $fileMapper->save($file);

            return $this->getJsonModel()->setVariables(array(
                'code' => 1,
                'messages' => $formData
            ));
        } else {
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => $form->getErrorMessagesList()
            ));
        }
    }

    private function uploadDocumentFile(){
        $documentId = $this->getRequest()->getPost('documentId');
        if(!$documentId){
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => ['Dữ liệu không hợp lệ']
            ));
        }
        $document = new \Document\Model\Document();
        $document->setId($documentId);
        $documentMapper = $this->getServiceLocator()->get('\Document\Model\DocumentMapper');
        /* @var $documentMapper \Document\Model\DocumentMapper */
        if(!$documentMapper->get($document)){
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => ['Dữ liệu không hợp lệ']
            ));
        }
        $form = new \Home\Form\Media\DocumentFile($this->getServiceLocator(), ['id' => $document->getId()]);
        $dataPopulate = array_merge_recursive(
            $this->getRequest()->getPost()->toArray(),
            $this->getRequest()->getQuery()->toArray(),
            $this->getRequest()->getFiles()->toArray());
        $dataPopulate['fileUpload']['name'] = Format::removeSigns($dataPopulate['fileUpload']['name']);
        $form->setData($dataPopulate);
        if($form->isValid()){
            $formData = $form->getData();
            $file = new \Document\Model\DocumentFile();
            $file->setFileName($formData['fileUpload']['name']);
            $file->setFileSize($formData['fileUpload']['size']);
            $file->setDocumentId($document->getId());
            $file->setCreatedById($this->user()->getIdentity());
            $file->setCreatedDateTime(DateBase::getCurrentDateTime());
            $fileMapper = $this->getServiceLocator()->get('\Document\Model\DocumentFileMapper');
            /* @var $fileMapper \Document\Model\DocumentFileMapper */
            $fileMapper->save($file);

            return $this->getJsonModel()->setVariables(array(
                'code' => 1,
                'messages' => $formData
            ));
        } else {
            return $this->getJsonModel()->setVariables(array(
                'code' => 0,
                'messages' => $form->getErrorMessagesList()
            ));
        }
    }
}