<?php

namespace User\Form;

use Home\Form\ProvidesEventsForm;
use ZendX\Form\Element\DisplayGroup;
use Zend\Form\Element\File;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter;
use User\Model\User;

class ProfileFile extends ProvidesEventsForm
{
public function getAllowedExts() {
		return ['png','jpeg','jpg','gif'];
	}

	public function __construct($name = null)
	{
		parent::__construct($name);
	$this->setAttribute('method', 'post');
		$this->addElements();
		$this->setAttributes(['class'=>'dropzone col-md-12']);
		$this->setAttributes(['action'=>'/user/profile/avatar']);

	}

	public function addElements()
	{
		$file = new File('fileUpload');
		$this->add($file);

	}

	public function addInputFilter($user = null)
	{
		$inputFilter = new InputFilter\InputFilter();
		$factory = new InputFactory();

		$inputFilter->add($factory->createInput(array(
				'name' => 'fileUpload',
				'required' => true,
				'validators' => array(
						array(
								'name' => 'Zend\Validator\File\Extension',
								'break_chain_on_failure' => true,
								'options' => $this->getAllowedExts(),
										'messages' => array(
												\Zend\Validator\File\Extension::FALSE_EXTENSION => 'File upload phải là file png,jpeg,jpg,gif'
										)
								)
						)
				)
		));

		// File Path
		
		$uri = new \Home\Service\Uri();
		
		$targetFolder = $uri->getSavePath($user);
 
		if (!file_exists($targetFolder))
		{
			$oldmask = umask(0);
			mkdir($targetFolder, 0777, true);
			umask($oldmask);
		}
		
		$fileInput = new InputFilter\FileInput('fileUpload');
		$fileInput->setRequired(true);
		$fileInput->getFilterChain()->attachByName('filerenameupload', array(
				'target' => $targetFolder,
				'use_upload_name' => true,
				'overwrite' => true
		));
		$inputFilter->add($fileInput);

		$this->setInputFilter($inputFilter);
	}
}