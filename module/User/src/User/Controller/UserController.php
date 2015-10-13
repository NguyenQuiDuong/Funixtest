<?php
/**
 * User\Controller
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace User\Controller;

use User\Model\User;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Home\Model\DateBase;
use Home\Service\Uri;

class UserController extends AbstractActionController
{

    public function indexAction()
    {

    }

    /**
     * signin
     */
    public function signinAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $redirect = trim($request->getQuery('redirect'));
        if ($this->user()->hasIdentity()) {
            if (!$redirect) {
                $authorize = $this->getServiceLocator()->get('\Authorize\Service\Authorize');
                if(!$authorize->isAllowed('company:announcement', 'index')){
                    return $this->redirect()->toRoute('home');
                }else{
                    return $this->redirect()->toRoute('company');
                }
            }
            return $this->redirect()->toUrl($redirect);
        }
        $sl = $this->getServiceLocator();

        $form = new \User\Form\Signin($this->getServiceLocator());
        //$form = new \User\Form\Signin();
        $failNumber = isset($_SESSION['failNumber']) ? $_SESSION['failNumber'] : 0;
        if($failNumber < 2){
            $form->removeCaptcha();
        }
        if ($request->isPost()) {

            $form->setData($request->getPost());
            if ($form->isValid()) {
                $_SESSION['failNumber'] = 0;
                $userService = $this->getServiceLocator()->get('User\Service\User');
                /* if($userService->getIdentity() && $userService->getUser()){
                    if(!$userService->getUser()->getEmployeeCode()){
                        return $this->redirect()->toUrl(Uri::build('/user/user/updatecode', ['redirect'=>$redirect]));
                    }
                } */
                if (!$redirect) {
                    $authorize = $this->getServiceLocator()->get('\Authorize\Service\Authorize');
                    if(!$authorize->isAllowed('company:announcement', 'index')){
                        return $this->redirect()->toRoute('home');
                    }else{
                        return $this->redirect()->toRoute('company');
                    }
                }
                return $this->redirect()->toUrl($redirect);
//                 $username = $form->getInputFilter()->getValue('username');
//                 $password = $form->getInputFilter()->getValue('password');
//                 /* @var $serviceUser \User\Service\User */
//                 $serviceUser = $this->getServiceLocator()->get('User\Service\User');
//                 // @todo show captcha after signing 3 times failed
//                 if(!$serviceUser->authenticate($username, $password)) {
//                     $form->showInvalidMessage();
//                 } else {
//                     /* @var $user \User\Model\User */
//                     $user = $serviceUser->getUser();
//                     if(!$user) {
//                         return;
//                     }
//                     if(!$user->getLocked() && $user->getActive()) {
//                         if (!$redirect) {
//                             return $this->redirect()->toRoute('home');
//                         } else {
//                             return $this->redirect()->toUrl($redirect);
//                         }
//                     }
//                     if($user->getLocked()) {
//                         $form->showInvalidMessage(\User\Form\Signin::ERROR_LOCKED);
//                     }
//                     if(!$user->getActive()) {
//                         $form->showInvalidMessage(\User\Form\Signin::ERROR_INACTIVE);
//                     }
//                 }
            } else {
                $_SESSION['failNumber'] = ++$failNumber;
                if($failNumber >= 10){
                    $username = $form->getInputFilter()->getValue('username');
                    $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
                    if(!!($user = $userMapper->get(null, $username))){
                        $userMapper->updateColumns(['locked' => 1], $user);
                        $form->get('username')->setMessages([$form::ERROR_LOCKED]);
                    }
                }
            }
        }
        $viewModel = new ViewModel(array(
            'form' => $form,
            'redirect' => $redirect,
        ));
        if ($this->params()->fromQuery('layout') == 'false') {
            $viewModel->setTerminal(true);
        }
        return $viewModel;
    }

    /**
     * signout
     */
    public function signoutAction()
    {
        $this->user()->signout();
        return $this->redirect()->toRoute('home');
    }

    /**
     * active user
     */
    public function activeAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        $userName = $request->getQuery('u');
        $activeKey = $request->getQuery('c');
        if (!$userName || !$activeKey) {
            $this->redirect()->toUrl('/');
        }
        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
        $user = new User();
        $translator = $this->getServiceLocator()->get('translator');
        $user->setActiveKey($activeKey);
        $user->setUsername($userName);
        if ($userMapper->activeUser($user)) {
            $message = '<p class="success">' . $translator->translate('Chúc mừng bạn đã kích hoạt tài khoản thành công') . '</p>';
        } else {
            $message = '<p class="error">' . $translator->translate('Tài khoản đã được kích hoạt hoặc không tồn tại') . '</p>';
        }

        return new ViewModel(array('message' => $message));
    }

    /**
     * get password
     */
    public function getpasswordAction()
    {
        $viewModel = new ViewModel();
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $sl = $this->getServiceLocator();
        $translator = $sl->get('translator');


        /* @var $userMapper \User\Model\UserMapper() */
        $userMapper = $sl->get('User\Model\UserMapper');
        /* @var $userService \User\Service\User() */
        $userService = $sl->get('User\Service\User');

        /* @var $form \User\Form\GetActiveCode() */
        $form = new \User\Form\Password\Forgot($this->getServiceLocator());

        $message = '';
        $viewModel->setVariable('form', $form);
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $formData = $form->getData();
                $user = $userMapper->get(null, null, $formData['email']);
                $resetKey = md5($user->getEmail().(strtotime(DateBase::getCurrentDateTime())).rand(1, 100));
                $user->setResetKey($resetKey);
                $userMapper->updateColumns(['resetKey' => $resetKey], $user);

                $userService->resetPassword($user);

                $viewModel->setVariable('successMessage', 'Yêu cầu đổi mật khẩu của bạn đã được thực hiện. Vui lòng kiểm tra mail và thực hiện theo các bước hướng dẫn.');
            }
        }
        return $viewModel;

    }

    /**
     * signup
     */
    public function signupAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $sl = $this->getServiceLocator();

        if ($this->user()->hasIdentity()) {
            return $this->redirect()->toRoute('home');
        }

        $user = new User();
        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $sl->get('User\Model\UserMapper');

        /* @var $cityMapper \Address\Model\CityMapper */
        $cityMapper = $sl->get('Address\Model\CityMapper');
        $city = new \Address\Model\City();

        /* @var $districtMapper \Address\Model\DistrictMapper */
        $districtMapper = $sl->get('Address\Model\DistrictMapper');
        $district = new \Address\Model\District();
        $districts = array();
        if (!!($cityId = $request->getPost('cityId'))) {
            $district->setCityId($cityId);
            $districts = $districtMapper->fetchAll($district);
        }


        /* @var $form \User\Form\Signup */
        $form = $sl->get('User\Form\Signup');
//        $form->setInputFilter($sl->get('User\Form\SignupFilter'));
//        $form->setCities($city->toSelectBoxArray($cityMapper->fetchAll()));
//        $form->setDistricts($district->toSelectBoxArray($districts));
        $form->bind($user);

        $viewModel = new ViewModel();
        if ($this->params()->fromQuery('layout') == 'false') {
            $viewModel->setTerminal(true);
        }
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
//                $user->exchangeArray($form->getData());

                if($userMapper->isEmailAvailable($user)){
                    $user->exchangeArray($form->getData());
                    /* @var $serviceUser \User\Service\User */
                    $serviceUser = $sl->get('User\Service\User');
                    $serviceUser->signup($user);
                    $viewModel->setVariable('success', true);
                    $this->redirect()->toUrl('/');
                }else{
                    $mes = 'Email đã được sử dụng! Bạn hãy thử lại với Email khác.';
                    echo "<script type='text/javascript'>alert('$mes');</script>";
                }
                /* @var $serviceUser \User\Service\User */
            } else {
                $viewModel->setVariable('success', false);
            }
        }
        $viewModel->setVariable('form', $form);
        return $viewModel;
    }

    public function ajaxsignupAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $translator = $this->getServiceLocator()->get('translator');
            $user = new User();
            /* @var $serviceUser \User\Service\User */
            $serviceUser = $this->getServiceLocator()->get('User\Service\User');

            $data = $request->getPost();
            $user->exchangeArray((array)$data);
            if (!isset($data['rePassword']) || $data['password'] != $data['rePassword']) {
                $error = array('rePassword' => $translator->translate('Gõ lại mật khẩu không chính xác'));
                return new JsonModel(array('code' => 0, 'message' => $error));
            }
            if (count($error = $serviceUser->validateSignupInfo($user))) {
                return new JsonModel(array('code' => 0, 'message' => $error));
            }

            $serviceUser->signup($user);
            $message = $translator->translate('Chúc mừng bạn đăng ký tài khoản thành công, vui lòng kiểm tra lại email đăng ký để lấy link kích hoạt tài khoản!');
            return new JsonModel(array('code' => 1, 'message' => $message));
        }
        return null;

    }

    public function ajaxsigninAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {
            $user = new User();
            /* @var $userMapper \User\Model\UserMapper */
            $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
            /* @var $serviceUser \User\Service\User */
            $serviceUser = $this->getServiceLocator()->get('User\Service\User');
            $data = $request->getPost();
            $user->exchangeArray((array)$data);
            if (count($error = $serviceUser->validateSigninInfo($user))) {
                return new JsonModel(array('code' => 0, 'message' => $error));
            }
            return new JsonModel(array('code' => 1, 'user' => $serviceUser->getUser()->toStd()));
        }
        return null;
    }

    /**
     *
     */
    public function getactivecodeAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $sl = $this->getServiceLocator();

        /* @var $userMapper \User\Model\UserMapper() */
        $userMapper = $sl->get('User\Model\UserMapper');
        /* @var $userService \User\Service\User() */
        $userService = $sl->get('User\Service\User');
        /* @var $form \User\Form\GetActiveCode() */

        $form = $sl->get('User\Form\GetActiveCode');
        $form->setInputFilter($sl->get('User\Form\GetActiveCodeFilter'));
        $message = '';

        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $validator = new \Zend\Validator\EmailAddress();
                $user = new User();
                $translator = $sl->get('translator');
                if ($validator->isValid($request->getPost('inputStr')) && $request->getPost('captcha')) {
                    $user->setEmail($request->getPost('inputStr'));
                } else {
                    $user->setUsername($request->getPost('inputStr'));
                }
                $us = $userMapper->get(null, $user->getUsername(), $user->getEmail());
                if (!$us) {
                    $message = '<p class="error">' . $translator->translate('Địa chỉ email hoặc tên đăng nhập không chính xác') . '</p>';
                } else if ($us->getActive() != null) {
                    $message = '<p class="error">' . $translator->translate('Tài khoản của bạn đã được kích hoạt') . '</p>';
                } else {
                    $userService->sendActiveLink($user);
                    $message = '<p>' . $translator->translate('Xác nhận gửi lại link kích hoạt tài khoản thành công, vui lòng kiểm tra lại địa chỉ email của bạn để nhận link kích hoạt tài khoản') . '</p>';
                }
            }
        }

        $viewModel = new ViewModel(array(
            'form' => $form,
            'message' => $message
        ));
        return $viewModel;
    }

    public function resetpasswordAction(){
        $viewModel = new ViewModel();
        $resetKey = $this->getRequest()->getQuery('resetKey');
        $email = $this->getRequest()->getQuery('email');
        if(!$resetKey || !$email){
            $viewModel->setVariable('errorMessage', 'Dữ liệu không hợp lệ');
            return $viewModel;
        }
        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        if(!($user = $userMapper->get(null, null, $email))){
            $viewModel->setVariable('errorMessage', 'Email không tồn tại trong hệ thống');
            return $viewModel;
        } elseif ($user->getResetKey() != $resetKey){
            $viewModel->setVariable('errorMessage', 'Reset key không chính xác!!!');
            return $viewModel;
        }
        $form = new \User\Form\Password\Reset($this->getServiceLocator());
        $viewModel->setVariable('form', $form);
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $user->setPassword($formData['password']);
                $user->setSalt($user->generateSalt());
                $user->setPassword($user->createPassword());
                $user->setResetKey(null);
                $userMapper->save($user);
                $viewModel->setVariable('successMessage', 'Mật khẩu đã được đặt lại thành công!!!');
                return $viewModel;
            }
        }
        return $viewModel;

    }


}