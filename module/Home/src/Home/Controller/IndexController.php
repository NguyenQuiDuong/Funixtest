<?php
/**
 * Home\Controller
 *
 */

namespace Home\Controller;


class IndexController extends ControllerBase
{
    public function indexAction()
    {
        /** @var $subjectMapper Subject/Model/SubjectMapper */
        $subjectMapper = $this->getServiceLocator()->get('Subject/Model/SubjectMapper');
        $subjects = $subjectMapper->featchAll('category');
        $this->layout()->setVariables(['subjectCategories' => $subjects]);
    	return $this->getViewModel();
    }

    public function addAction()
    {

    }

    public function editAction()
    {

    }

    public function deleteAction()
    {

    }

    public function introAction()
    {

    }

    public function searchAction()
    {
        echo 'aaaaaaaaaaaaa';
    }
}