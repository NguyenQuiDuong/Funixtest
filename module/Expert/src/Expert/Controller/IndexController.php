<?php

namespace Expert\Controller;

use Expert\Form\Apply;
use Home\Controller\ControllerBase;
use Subject\Model\Subject;
use Subject\Model\Subject\Category;

class IndexController extends ControllerBase
{
    public function indexAction()
    {
        /** @var  $subjectMapper \Subject\Model\SubjectMapper */
        $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');
        $this->getViewModel()->setVariables(['subjects'=>$subjectMapper->featchAll('category')]);
        return $this->getViewModel();
    }

    public function applyAction(){
        $form = new Apply($this->getServiceLocator());

        $this->getViewModel()->setVariables(['form' => $form]);
        return $this->getViewModel();

    }

    public function listAction(){
        $categoryId = $this->params()->fromQuery('id');
        /** @var \Subject\Model\Subject\CategoryMapper $categoryMapper */
        $categoryMapper = $this->getServiceLocator()->get('Subject\Model\Subject\CategoryMapper');
        /** @var \Subject\Model\Subject\Category $category */
        $category = new Category();
        $category->setId($categoryId);
        if(!$category->getId() || !$categoryMapper->get($category)){
            return $this->page404();
        }
        /** @var  \Subject\Model\Subject $subject */
        $subject = new Subject();
        $subject->setCategoryId($category->getId());
        /** @var  \Subject\Model\SubjectMapper $subjectMapper */
        $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');
        $subjects = $subjectMapper->featchAll($subject);
        $this->getViewModel()->setVariables(['subjects' => $subjects]);
        return $this->getViewModel();

    }

    public function viewAction(){
        $subjectId = $this->params()->fromQuery('id');
        /** @var \Subject\Model\SubjectMapper $subjectMapper */
        $subjectMapper = $this->getServiceLocator()->get('Subject\Model\SubjectMapper');
        /** @var  \Subject\Model\Subject $subject */
        $subjectExpert = new \Expert\Model\Expert\Subject();
        if(!$subjectId){
            return $this->page404();
        }
        if(count(explode(',',$subjectId))>1){
            $count = 0;
            foreach(explode(',',$subjectId) as $sId){
                $subject = new Subject();
                $subject->setId($sId);
                if(!$subjectMapper->get($subject)){
                    $count++;
                }else{
                    $subjectIds[] = $sId;
                }
            }
            if($count == count(explode(',',$subjectId))){
                return $this->page404();
            }
            $subjectExpert->addOption('subjectIds',$subjectIds);
        }else{
            $subject = new Subject();
            $subject->setId($subjectId);

            if(!$subjectMapper->get($subject)){
                return $this->page404();
            }
            $subjectExpert->setSubjectId($subject->getId());
        }


        /** @var \Expert\Model\Expert\SubjectMapper $subjectExpertMapper */
        $subjectExpertMapper = $this->getServiceLocator()->get('Expert\Model\Expert\SubjectMapper');
        $this->getViewModel()->setVariables(['experts'=>$subjectExpertMapper->featchAll($subjectExpert)]);
        $this->getViewModel()->setVariables(['subject'=>$subject]);
        return $this->getViewModel();
    }




}
