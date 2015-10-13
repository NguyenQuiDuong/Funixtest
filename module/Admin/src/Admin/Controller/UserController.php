<?php

namespace Admin\Controller;

use Home\Controller\ControllerBase;
use Subject\Model\Subject;
use Home\Model\DateBase;
use User\Model\User;

class UserController extends ControllerBase
{
    public function indexAction()
    {
        $form = new \Admin\Form\Subject\CategoryFilter($this->getServiceLocator());
        $form->setData($this->params()->fromQuery());

        $this->getViewModel()->setVariable('form', $form);

        if ($form->isValid()) {
            $user = new User();
            $user->exchangeArray($form->getData());

            $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
            /** @var $userMapper \User\Model\UserMapper */
            $paginator = $userMapper->search($user);
            $this->getViewModel()->setVariable('paginator', $paginator);
        }
        return $this->getViewModel();

    }

}
