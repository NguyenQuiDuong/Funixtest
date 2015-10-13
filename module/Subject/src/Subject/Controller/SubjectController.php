<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/Subject for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Subject\Controller;

use Subject\Model\Subject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class SubjectController extends AbstractActionController
{
    public function indexAction()
    {
        die;
        return array();
    }
    public function suggestAction(){
        $q = $this->getRequest()->getPost('q');
        $subject = new Subject();
        $subject->setName($q);
        $jsonModel = New JsonModel();
        if(!$q){
            $jsonModel->setVariables([
                'code' => 1,
                'data' => []
            ]);
            return $jsonModel;
        }
        /** @var \Subject\Model\SubjectMapper $subjectMapper */
        $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');
        $jsonModel->setVariables([
            'code' => 1,
            'data' => $subjectMapper->suggest($subject)
        ]);
        return $jsonModel;
    }
}
