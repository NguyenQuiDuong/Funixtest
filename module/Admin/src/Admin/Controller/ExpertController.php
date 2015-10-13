<?php

namespace Admin\Controller;

use Expert\Model\Expert;
use Home\Controller\ControllerBase;
use Home\Model\DateBase;
use Subject\Model\Subject;
use User\Model\User;

class ExpertController extends ControllerBase
{
    public function indexAction()
    {
        $form = new \Admin\Form\Subject\CategoryFilter($this->getServiceLocator());
        $form->setData($this->params()->fromQuery());

        $this->getViewModel()->setVariable('form', $form);

        if ($form->isValid()) {
            $expert = new Expert();
            $expert->exchangeArray($form->getData());

            $expertMapper = $this->getServiceLocator()->get('Expert\Model\ExpertMapper');
            /** @var $expertMapper \Expert\Model\ExpertMapper */
            $paginator = $expertMapper->search($expert,null);
            $this->getViewModel()->setVariable('paginator', $paginator);
        }
        return $this->getViewModel();

    }

    public function addAction(){
        $form = new \Admin\Form\Expert\Expert($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                /** @var \Expert\Model\Expert $expert */
                $expert = new Expert();
                $expert->exchangeArray($data);
                $expert->setCreatedById($this->user()
                    ->getIdentity());
                $expert->setCreatedDateTime(DateBase::getCurrentDateTime());
                $expert->setExtracontent(json_encode($data['subjectName']));
                $user = new User();
                /** @var \User\Model\UserMapper $userMapper */
                $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
                $user = $userMapper->get($expert->getUserId());
                $user->setRole(User::ROLE_MENTOR);
                $userMapper->updateUser($user);
                /** @var \Expert\Model\ExpertMapper $expertMapper */
                $expertMapper = $this->getServiceLocator()->get('Expert\Model\ExpertMapper');
                /** @var \Subject\Model\SubjectMapper $subjectMapper */
                $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');

                $expert = $expertMapper->save($expert);
                $subjectIds = explode(',',$data['subjectId']);
                foreach($subjectIds as $subjectId){
                    $subject = new Subject();
                    $subject->setId($subjectId);
                    if($subjectMapper->get($subject))
                    {
                        $subjectNames[] = $subject->getName();
                        $expertSubject = new Expert\Subject();
                        $expertSubject->setExpertId($expert->getId());
                        $expertSubject->setSubjectId($subjectId);
                        $expertSubject->setCreatedById($this->user()->getIdentity());
                        $expertSubject->setCreatedDateTime(DateBase::getCurrentDateTime());
                        /** @var \Expert\Model\Expert\SubjectMapper $expertSubjectMapper */
                        $expertSubjectMapper = $this->getServiceLocator()->get('Expert\Model\Expert\SubjectMapper');
                        $expertSubjectMapper->save($expertSubject);
                    }
                }
                $expert->setExtracontent(json_encode(implode(',',$subjectNames)));
                $expertMapper->save($expert);
                if ($form->get('afterSubmit')->getValue()) {
                    return $this->redirect()->toUrl($form->get('afterSubmit')
                        ->getValue());
                }
            }
        }
        $this->getViewModel()->setVariable('form', $form);

        return $this->getViewModel();
    }


}
