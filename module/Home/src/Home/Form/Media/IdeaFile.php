<?php

namespace Home\Form\Media;

use Home\Form\FormBase;
use Zend\Form\Element\Text;
use Zend\Form\Element\File;
use Zend\Form\Element\Hidden;
use Zend\Stdlib\ErrorHandler;

class IdeaFile extends FormBase{
    /**
     * @param null|string $name
     */
    public function __construct($serviceLocator, $options=null){
        parent::__construct('activityPhonecall');
        $this->setServiceLocator($serviceLocator);
        $this->setAttribute('method', 'post');

        $filter = $this->getInputFilter();
        $ideaId = new Text('ideaId');
        $this->add($ideaId);
        $filter->add(array(
            'name' => 'ideaId',
            'required' => true,
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
            'required' => true,
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
                    'name'    => 'File\Extension',
                    'break_chain_on_failure' => true,
                    'options' => array(
                        'extension' => array('jpg', 'jpeg', 'gif', 'png', 'doc', 'docx', 'xml', 'csv', 'xls', 'xlsx'),
                        'messages' => array(
                            \Zend\Validator\File\Extension::FALSE_EXTENSION => 'File upload phải là file ảnh, excel, hoặc msword'
                        )
                    )
                ),
            ),
        ));
        $this->addFileUploadRenameFilter();
    }

    public function addFileUploadRenameFilter(){
        $targetFolder = MEDIA_PATH.'/idea/temp';

        if (!file_exists($targetFolder)) {
            $oldmask = umask(0);
            mkdir($targetFolder, 0777, true);
            umask($oldmask);
        }
        $this->getInputFilter()->get('fileUpload')
        ->getFilterChain()
        ->attach(new \Zend\Filter\File\RenameUpload(array(
            'target' => $targetFolder,
            //'use_upload_name' => true,
            'overwrite' => true
        )));
    }

    public function isValid(){
        $isValid = parent::isValid();
        if($isValid){
            $data = parent::getData();
            $file = new \Idea\Model\File();
            $file->setFileName($data['fileUpload']['name']);
            $file->setIdeaId($data['ideaId']);
            $date = new \DateTime();
            $file->setFilePath($date->format('dmY'));

            $fileMapper = $this->getServiceLocator()->get('\Idea\Model\FileMapper');
            if($fileMapper->isExisted($file)){
                $this->get('fileUpload')->setMessages(['file đã tồn tại trên hệ thống, không thể ghi đè']);
                $isValid = false;
            }
        }
        return $isValid;
    }

    public function getData($flag=null){
        $data = parent::getData($flag);
        $date = new \DateTime();
        $data['filePath'] = $date->format('dmY');
        $data['fileName'] = $data['fileUpload']['name'];
        $data['fileSize'] = $data['fileUpload']['size'];
        return $data;
    }
}