<?php
/**
 * User\Controller
 *
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace User\Controller;

use Home\Model\DateBase;
use User\Model\User;
use User\Model\UserMapper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;

class SigninController extends AbstractActionController
{

    /**
     * index
     */
    public function indexAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $redirect = trim($request->getQuery('redirect'));
        if ($this->user()->hasIdentity()) {
            if (!$redirect) {
                return $this->redirect()->toRoute('home');
            }
            return $this->redirect()->toUrl($redirect);
        }
        /* @var $sl \Zend\ServiceManager\ServiceLocatorInterface */
        $sl = $this->getServiceLocator();

        /* @var $form \User\Form\Signin */
       /*  $form = $sl->get('User\Form\Signin');
        $form->setInputFilter($sl->get('User\Form\SigninFilter')); */
        $form = new \User\Form\Signin($this->getServiceLocator());
		$view = new ViewModel();
		$view->setVariable('form', $form);
		$failNumber = isset($_SESSION['failNumber']) ? $_SESSION['failNumber'] : 0;
		if($failNumber < 2){
			$form->removeCaptcha();
		}
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
            	$_SESSION['failNumber'] = 0;
            	if (!$redirect) {
            		return $this->redirect()->toRoute('home');
            	}
            	return $this->redirect()->toUrl($redirect);
            } else {
            	$_SESSION['failNumber'] = ++$failNumber;
            	if($failNumber >= 10){
            		$username = $form->getInputFilter()->getValue('username');
            		$userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
            		if(!!($user = $userMapper->get(null, $username))){
            			$userMapper->updateColumns(['locked' => 1], $user);
            		}
            	}
            }
			return $view;
        }

        // Google authentication
        /* @var $googleLogin \User\Service\GoogleLogin */
        $googleLogin = $this->getServiceLocator()->get('User\Service\GoogleLogin');

        return new ViewModel(array(
            'form'       => $form,
            'googlelink' => $googleLogin->getAuthenticationUrl(),
            'redirect' => $redirect,
        ));
    }

    public function facebookAction()
    {
        $request = $this->getRequest();
        if(trim($request->getQuery('redirect')))
        {
            $_SESSION['redirect'] = trim($request->getQuery('redirect'));
        }


        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $response = $this->getResponse();

        require_once 'Facebook/FacebookSession.php';
        require_once 'Facebook/Entities/AccessToken.php';
        require_once 'Facebook/FacebookSDKException.php';
        require_once 'Facebook/FacebookRequestException.php';
        require_once 'Facebook/FacebookAuthorizationException.php';
        require_once 'Facebook/FacebookResponse.php';
        require_once 'Facebook/HttpClients/FacebookCurl.php';
        require_once 'Facebook/HttpClients/FacebookHttpable.php';
        require_once 'Facebook/HttpClients/FacebookCurlHttpClient.php';
        require_once 'Facebook/FacebookSDKException.php';
        require_once 'Facebook/FacebookRequestException.php';
        require_once 'Facebook/FacebookRedirectLoginHelper.php';
        require_once 'Facebook/FacebookRequest.php';
        require_once 'Facebook/GraphObject.php';
        require_once 'Facebook/GraphUser.php';

        $config = $this->getServiceLocator()->get('Config');
        $appId 			= $config['login']['facebook']['appId'];
        $appSecret  	= $config['login']['facebook']['appSecret'];

        FacebookSession::setDefaultApplication($appId, $appSecret);

        $url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'] . '/user/signin/facebook';

        $facebook = new \Facebook\FacebookRedirectLoginHelper($url);

        try {
            $session = $facebook->getSessionFromRedirect();
        } catch (\Facebook\FacebookRequestException $ex) {
            // When Facebook returns an error
            echo $ex->getMessage();
        } catch (\Exception $ex) {
            // When validation fails or other local issues
            echo $ex->getMessage();
        }
        if (isset($session)) {
            $user_profile 	= (new \Facebook\FacebookRequest($session, 'GET', '/me'))->execute()->getGraphObject(\Facebook\GraphUser::className());
            $arrResponse	= $user_profile->asArray();
            $email			= $user_profile->getProperty('email');

            if(!$email) {
                echo '<script>window.opener.alert("Không tồn tại thông tin Email của tài khoản trên");window.close()</script>';
            }

            $user = new User();
            $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
            $serviceUser = $this->getServiceLocator()->get('User\Service\User');

            if (!$serviceUser->authenticateFacebook($email)){
                $user->setActive('1');
                $user->setEmail($email);
                $user->setRole(\User\Model\User::ROLE_MEMBER);
                $user->setFullName($arrResponse['name']);
                $user->setCreatedDate(DateBase::getCurrentDate());
                $user->setCreatedDateTime(DateBase::getCurrentDateTime());
                $userMapper->save($user);

                // check nếu user đó đã dc mời tham gia dự án sẽ gắn luôn nó với dự án
                $projectUser = new \Work\Model\ProjectUser();
                $projectUser->setUserEmail($user->getEmail());
                $projectUser->setUserId($user->getId());

                $projectUserMapper = $this->getServiceLocator()->get('\Work\Model\ProjectUserMapper');
                $projectUserMapper->updateUserId($projectUser);

                $this->redirect()->toUrl($url);
            } else {
            	// check nếu user đó đã dc mời tham gia dự án sẽ gắn luôn nó với dự án
            	$projectUser = new \Work\Model\ProjectUser();
            	$projectUser->setUserEmail($serviceUser->getUser()->getEmail());
            	$projectUser->setUserId($serviceUser->getUser()->getId());

            	$projectUserMapper = $this->getServiceLocator()->get('\Work\Model\ProjectUserMapper');
            	$projectUserMapper->updateUserId($projectUser);

                if (!$_SESSION['redirect']) {
                    return $this->redirect()->toUrl('/');
                } else {
                    $redirect = $_SESSION['redirect'];
                    unset($_SESSION['redirect']);
                    return $this->redirect()->toUrl($redirect);
                }
            }
        }else {
            $this->redirect()->toUrl($facebook->getLoginUrl(['email', 'user_birthday', 'user_about_me']));
        }

        return $response;
    }

    public function googleAction()
    {
        $request = $this->getRequest();
        if(trim($request->getQuery('redirect')))
        {
            $_SESSION['redirect'] = trim($request->getQuery('redirect'));
        }

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        $response = $this->getResponse();

        /* @var $googleLogin \User\Service\GoogleLogin */
        require_once 'Google/Client.php';
        require_once 'Google/Service/Oauth2.php';
        require_once 'Google/Http/REST.php';
        require_once 'Google/Http/Request.php';
        require_once 'Google/Service/Resource.php';
        require_once 'Google/Auth/OAuth2.php';

        $config = $this->getServiceLocator()->get('Config');
        $clientId 			= $config['login']['google']['clientId'];
        $clientSecret  	= $config['login']['google']['clientSecret'];

        $url  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
        $url .= $_SERVER['HTTP_HOST'] . '/user/signin/google';


        $client = new \Google_Client();
        $client->setApplicationName('MyFirstTest');
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($url);
        $client->setScopes(array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/plus.me'));

        $error = $this->getRequest()->getQuery('error');
        if($error){
            echo '<script>window.close()</script>';
        }

        $code = $this->getRequest()->getQuery('code');
        if($code){
            $client->authenticate($code);
            $_SESSION['access_token'] = $client->getAccessToken();
            $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
        } else {
        	$authUrl = $client->createAuthUrl();
        	return $this->redirect()->toUrl(filter_var($authUrl, FILTER_SANITIZE_URL));
        }

        //TODO: xoa vi cam giac dau co can
        if (isset($_SESSION['access_token'])){
            $client->setAccessToken($_SESSION['access_token']);
        }
        if($client->isAccessTokenExpired()) {
			$authUrl = $client->createAuthUrl();
			return $this->redirect()->toUrl(filter_var($authUrl, FILTER_SANITIZE_URL));
        }
        //End TODO
        if ($client->getAccessToken()){

            $oauth2 = new \Google_Service_Oauth2($client);
            $userInfoPlus				= $oauth2->userinfo->get();
            $_SESSION['access_token'] 	= $client->getAccessToken();
            if(!$userInfoPlus->getEmail()) {
                echo '<script>window.opener.alert("Không tồn tại thông tin Email của tài khoản trên");window.close()</script>';
            }
            $arrResponse 	= array(
                'email' 		=> $userInfoPlus->getEmail(),
                'name' 	=> $userInfoPlus->getName(),
            );


            $user = new User();
            $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
            $serviceUser = $this->getServiceLocator()->get('User\Service\User');

            if (!$serviceUser->authenticateGoogle($arrResponse['email'])){
                $user->setActive('1');
                $user->setEmail($arrResponse['email']);
                $user->setRole(\User\Model\User::ROLE_MEMBER);
                $user->setFullName($arrResponse['name']);
                $user->setCreatedDate(DateBase::getCurrentDate());
                $user->setCreatedDateTime(DateBase::getCurrentDateTime());
                $userMapper->save($user);

                // check nếu user đó đã dc mời tham gia dự án sẽ gắn luôn nó với dự án
                $projectUser = new \Work\Model\ProjectUser();
                $projectUser->setUserEmail($user->getEmail());
               	$projectUser->setUserId($user->getId());

                $projectUserMapper = $this->getServiceLocator()->get('\Work\Model\ProjectUserMapper');
                $projectUserMapper->updateUserId($projectUser);

                $this->redirect()->toUrl($url);
            } else
            {
            	// check nếu user đó đã dc mời tham gia dự án sẽ gắn luôn nó với dự án
            	$projectUser = new \Work\Model\ProjectUser();
            	$projectUser->setUserEmail($serviceUser->getUser()->getEmail());
            	$projectUser->setUserId($serviceUser->getUser()->getId());

            	$projectUserMapper = $this->getServiceLocator()->get('\Work\Model\ProjectUserMapper');
            	$projectUserMapper->updateUserId($projectUser);

                if (!$_SESSION['redirect']) {
                    return $this->redirect()->toUrl('/');
                } else {
                    $redirect = $_SESSION['redirect'];
                    unset($_SESSION['redirect']);
                    return $this->redirect()->toUrl($redirect);
                }
            }
        } else {
            $this->redirect()->toUrl($client->createAuthUrl());
        }

        return $response;
    }

    public function oauthAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        /* @var $userService \User\Service\User */
        $userService = $this->getServiceLocator()->get('User\Service\User');
        $viewModel = new ViewModel();
        if ($userService->hasIdentity()) {
            $viewModel->setVariable('redirect', '/');
            return $viewModel;
        }
        $config = $this->getServiceLocator()->get('Config');
        $clientId = $config['oauths']['id.vatgia.com']['clientId'];
        $secretKey = $config['oauths']['id.vatgia.com']['secretKey'];

        if ($request->getQuery('access_code')) {
            $access_code = $request->getQuery('access_code');
            $url = 'https://id.vatgia.com/oauth2/accessCode/' . $access_code . '?with=acc';
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            curl_setopt($curl, CURLOPT_USERPWD, "$clientId:$secretKey");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
            $response = curl_exec($curl);
            curl_close($curl);
            $response = json_decode($response);

            if ($response->meta->total_count == 1 && is_array($response->objects) && count($response->objects)) {
                $acc = array_shift($response->objects)->acc;
                $email = $acc->email;
                $fullName = $acc->first_name . ' ' . $acc->last_name;
                $phone = str_replace('+84', '0', $acc->phone);
                $birthday = $acc->dob;
                $address = $acc->address;

                /* @var $userMapper \User\Model\UserMapper */
                $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
                if (($user = $userMapper->get(null, null, $email)) != null) {
                    $userId = $user->getId();
                    $us = new \User\Model\User();
                    $us->setId($user->getId());
                    $us->setFullName($fullName);
                    $us->setMobile($phone);
                    $us->setBirthday($birthday);
                    $us->setAddress($address);
                    $userService->updateUser($us);
                } else {
                    $user = new \User\Model\User();
                    $user->setEmail($email);
                    $user->setFullName($fullName);
                    $user->setMobile($phone);
                    $user->setBirthday($birthday);
                    $user->setAddress($address);
                    $user->setActive(1);
                    $user->setRegisteredDate(date('Y-m-d H:i:s'));
                    $user->setRegisteredFrom($_SERVER['HTTP_HOST']);
                    $user->setRole(\User\Model\User::ROLE_MEMBER);
                    $userId = $userMapper->save($user);
                }
                $userService->getAuthService()->getStorage()->write($userId);
                $attach = $request->getQuery('attach');

                $attach = json_decode(base64_decode($attach));

                if (isset($attach->redirectUri)) {
                    $viewModel->setVariable('redirect', $attach->redirectUri);
                    if (isset($attach->target)) {
                        $viewModel->setVariable('target', $attach->target);
                    }
                    return $viewModel;
                }
            }
            $viewModel->setVariable('redirect', '/');
            return $viewModel;
        } else {
            $ui_mode = $request->getQuery('ui_mode') ? '&ui_mode=' . $request->getQuery('ui_mode') : '';
            $service = $request->getQuery('service') ? '&service=' . $request->getQuery('service') : '';
            $username = $request->getQuery('username');
            $password = $request->getQuery('password');
            $ticket = '';
            if ($username && $password) {
                $s = new \User\Service\SymetricTicket(array(
                    'username'  => base64_decode($username),
                    'password'  => base64_decode($password),
                    'timestamp' => time(),
                ));

                $ticket = '&signInTicket=' . urlencode($s->encrypt());
            }
            if ($social = $request->getQuery('social')) {
                $social .= '/';
            } else {
                $social = '';
            }
            $attach = ['redirectUri' => $request->getQuery('redirectUri'), 'target' => $request->getQuery('target')];
            $attach = '?attach=' . base64_encode(json_encode($attach));
            $url = 'https://id.vatgia.com/dang-nhap/' . $social . 'oauth?_cont=http://' . $_SERVER['HTTP_HOST'] .
                '/signin/oauth' . $attach . '&client_id=' . $clientId . $ui_mode . $service . $ticket;
            $this->redirect()->toUrl($url);
        }
        return $this->response;
    }

    public function vatgiaAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        if ($request->getQuery('token')) {
            $token = $request->getQuery('token');
            /* @var $userService \User\Service\User */
            $userService = $this->getServiceLocator()->get('User\Service\User');
            if (($user = $userService->signInByToken($token)) != null) {
                $userService->getAuthService()->getStorage()->write($user->getId());
            }
            $viewModel = new ViewModel();
            if ($request->getQuery('redirectUri')) {
                $viewModel->setVariable('redirect', $request->getQuery('redirectUri'));
            }
            return $viewModel;
        } else {
            $ui_mode = $request->getQuery('ui_mode');
            $service = $request->getQuery('service');
            if (isset($service)) {
                $this->redirect()->toUrl('https://id.vatgia.com/dang-nhap/?_cont=http://' . $_SERVER['HTTP_HOST'] . '/signin/vatgia?redirectUri=' . $request->getQuery('redirectUri') . ($ui_mode ? '&ui_mode=' . $ui_mode : '') . ($service ? '&service=' . $service : ''));
            }
            return false;
        }
    }
}