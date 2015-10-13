<?php

namespace Home\Form\Media;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\File;
use Zend\Form\Element\Hidden;
use Zend\Stdlib\ErrorHandler;
use Home\Model\DateBase;
use Home\Service\Uri;
use Document\Model\Document;
use Document\Model\Document\Model;
use Work\Model\Task;

class DocumentFile extends FormBase
{
    protected $id;
    /**
     * @param null|string $name
     */
    public function __construct($serviceLocator, $options=null){
        parent::__construct('documentFile');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();

        if($options && isset($options['id']) && $options['id']){
            $this->id = $options['id'];
        }

        $documentFile = new \Document\Model\DocumentFile();
        $documentFile->setDocumentId($this->id);
        $documentFile->setCreatedDateTime(DateBase::getCurrentDateTime());

        $targetFolder = Uri::getSavePath($documentFile);

        if (!file_exists($targetFolder)) {
            $oldmask = umask(0);
            mkdir($targetFolder, 0777, true);
            umask($oldmask);
        }
        $document = new Document();
        $task = new \Work\Model\Task();

        $fileUpload = new File('fileUpload');
        $this->add($fileUpload);
        $filter->add(array(
            'name' => 'fileUpload',
        	'type' => '\Zend\InputFilter\FileInput',
            'required' => true,
            'allowEmpty' => true,
            'filters' => array(
                new \Zend\Filter\File\RenameUpload(array(
                    'target' => sprintf($targetFolder),
                    'use_upload_name' => true,
                    'overwrite' => true
                ))
            ),
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
                        'max' => Task::MAX_FILE_SIZE.'MB',
                        'messages' => array(
                            \Zend\Validator\File\Size::TOO_BIG => 'File upload phải < 100Mb'
                        )
                    )
                ),
                array(
                    'name'    => 'File\Extension',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'extension' => $task->getAllowExtension(),
                        'messages' => array(
                            \Zend\Validator\File\Extension::FALSE_EXTENSION => 'File upload phải là file ảnh, excel, hoặc msword,pds,pdf'
                        )
                    )
                ),
            ),
        ));

    }
}