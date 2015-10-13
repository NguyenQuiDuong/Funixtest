<?php

namespace Home\Form\Media;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\File;
use Zend\Form\Element\Hidden;
use Zend\Stdlib\ErrorHandler;

class ContentFile extends FormBase{
	/**
	 * @param null|string $name
	 */
	public function __construct($serviceLocator, $options=null){
		parent::__construct('activityPhonecall');
		$this->setServiceLocator($serviceLocator);
		$this->setAttribute('method', 'post');

		$filter = $this->getInputFilter();

		$contractId = new Text('contractId');
		$this->add($contractId);
		$filter->add(array(
			'name' => 'contractId',
			'required' => false,
			'filters'   => array(
				array('name' => 'StringTrim'),
				array('name' => 'Digits'),
			),
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa nhập id'
						)
					)
				),
				array(
					'name'    => 'Digits',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							\Zend\Validator\Digits::INVALID => 'id phải là số'
						)
					)
				),
			),
		));

		$fileUpload = new File('fileUpload');
		$this->add($fileUpload);

		$filter->add(array(
			'name' => 'fileUpload',
			'type' => '\Zend\InputFilter\FileInput',
			'required' => false,
			'allowEmpty' => true,
			'validators' => array(
				array(
					'name'    => 'NotEmpty',
					'break_chain_on_failure' => true,
					'options' => array(
						'messages' => array(
							'isEmpty' => 'Bạn chưa chọn file'
						)
					)
				),
				array(
					'name'    => 'File\Size',
					'break_chain_on_failure' => true,
					'options' => array(
						'max' => '4MB',
						'messages' => array(
							\Zend\Validator\File\Size::TOO_BIG => 'File upload phải < 4Mb'
						)
					)
				),
				array(
					'name'    => 'File\MimeType',
					'break_chain_on_failure' => true,
					'options' => array(
						'mimeType' => array('image', 'application/msword',
							'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							'application/vnd.ms-excel', 'application/vnd.ms-excel.sheet.macroenabled.12',
							'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
							'application/pdf'),
						'messages' => array(
							\Zend\Validator\File\MimeType::FALSE_TYPE => 'File upload phải là file ảnh, excel, hoặc msword'
						)
					)
				),
			),
		));


	}

	public function isValid(){
		$isVaild = parent::isValid();
		if($isVaild){
			$data = $this->getData();
			$contract = new \Crm\Model\Contract();
			$contract->setId($data['contractId']);
			$contractMapper = $this->getServiceLocator()->get('\Crm\Model\ContractMapper');
			if(!$contractMapper->get($contract)){
				$this->get('contractId')->setMessages(['Không tìm thấy hợp đồng']);
				$isVaild = false;
			}
		}
		return $isVaild;
	}

	public function addFileUploadRenameFilter($contractId){
		$file = new \Crm\Model\Contract\File();
		$file->setContractId($contractId);

		$targetFolder = \Home\Service\Uri::getSavePath($file);

		if (!file_exists($targetFolder)) {
			$oldmask = umask(0);
			mkdir($targetFolder, 0777, true);
			umask($oldmask);
		}
		$this->getInputFilter()->get('fileUpload')
			->getFilterChain()
			->attach(new \Zend\Filter\File\RenameUpload(array(
				'target' => $targetFolder,
				'use_upload_name' => true,
				'overwrite' => true
			)));
	}

	/* public function recieverFile(){
		$contractId = $this->get('contractId')->getValue();

		$targetFolder = BASE_PATH.'/public/media/contracts/'.$contractId;

		if (!file_exists($targetFolder)) {
			$oldmask = umask(0);
			mkdir($targetFolder, 0777, true);
			umask($oldmask);
		}
		$adapter = new \Zend\File\Transfer\Adapter\Http();
		$adapter->setDestination($targetFolder);
		$name = '';
		foreach ($adapter->getFileInfo() as $file => $info) {
			$extension = pathinfo($info['name'], PATHINFO_EXTENSION);
			$name =  pathinfo($info['name'], PATHINFO_FILENAME).'.'.$extension;
		}

		$adapter->receive();
		return $this;

	} */

	public function getData($flag=17){
		//$fileName = $this->recieverFile();
		$data = parent::getData();
		$data['fileName'] = $data['fileUpload']['name'];
		return $data;
	}

	public function getMessagesForUpload(){
		$messages = $this->getMessages();
		$result = [];
		if($messages && count($messages)){
			foreach ($messages as $elementMessages){
				foreach ($elementMessages as $message){
					$result[] = $message;
				}
			}
		}
		return $result;
	}
}