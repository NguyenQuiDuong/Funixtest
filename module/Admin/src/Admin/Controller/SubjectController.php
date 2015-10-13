<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Home\Model\Base;
use Home\Controller\ControllerBase;
use Subject\Model\Subject;
use Home\Model\DateBase;

class SubjectController extends ControllerBase
{
    public function indexAction()
    {
        $form = new \Admin\Form\Subject\CategoryFilter($this->getServiceLocator());
        $form->setData($this->params()->fromQuery());

        $this->getViewModel()->setVariable('form', $form);

        if ($form->isValid()) {
            $subject = new Subject();
            $subject->exchangeArray($form->getData());

            $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');
            /** @var $categoryMapper \Subject\Model\Subject\CategoryMapper */
            $paginator = $subjectMapper->search($subject);
            $this->getViewModel()->setVariable('paginator', $paginator);
        }
        return $this->getViewModel();
        
    }
    
    public function addAction(){
        $form = new \Admin\Form\Subject\Subject($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $subject = new Subject();
                $subject->exchangeArray($data);
                $subject->setCreatedById($this->user()
                    ->getIdentity());
                $subject->setCreatedDateTime(DateBase::getCurrentDateTime());
                $subject->setStatus(Subject::STATUS_ACTIVE);
                $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');
                $subjectMapper->save($subject);
                if ($form->get('afterSubmit')->getValue()) {
                    return $this->redirect()->toUrl($form->get('afterSubmit')
                        ->getValue());
                }
            }
        }
        $this->getViewModel()->setVariable('form', $form);
        
        return $this->getViewModel();
    }
    
    public function addcategoryAction(){
        $form = new \Admin\Form\Subject\Category($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                /*@var $category \Subject\Model\Subject\Category */
                $category = new \Subject\Model\Subject\Category();
                $category->exchangeArray($data);
                $category->setCreatedById($this->user()
                    ->getIdentity());
                $category->setCreatedDateTime(DateBase::getCurrentDateTime());
                $category->setStatus(Subject::STATUS_ACTIVE);
                $categoryMapper = $this->getServiceLocator()->get('Subject\Model\Subject\CategoryMapper');
                $categoryMapper->save($category);
                if ($form->get('afterSubmit')->getValue()) {
                    return $this->redirect()->toUrl($form->get('afterSubmit')
                        ->getValue());
                }
            }
        }
        $this->getViewModel()->setVariable('form', $form);
    
        return $this->getViewModel();
    }

    public function categoryAction()
    {
        $form = new \Admin\Form\Subject\CategoryFilter($this->getServiceLocator());
        $form->setData($this->params()->fromQuery());

        $this->getViewModel()->setVariable('form', $form);

        if ($form->isValid()) {
            $category = new Subject\Category();
            $category->exchangeArray($form->getData());

            $categoryMapper = $this->getServiceLocator()->get('Subject\Model\Subject\CategoryMapper');
            /*@var $categoryMapper \Subject\Model\Subject\CategoryMapper */
            $paginator = $categoryMapper->search($category);
            $this->getViewModel()->setVariable('paginator', $paginator);
        }
        return $this->getViewModel();
    }

}
