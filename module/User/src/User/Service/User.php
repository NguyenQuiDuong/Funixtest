<?php


namespace User\Service;

use Home\Model\DateBase;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp as Smtp;
use Zend\Mail\Transport\SmtpOptions;

class User implements ServiceLocatorAwareInterface
{

    const DEFAULT_EMAIL_SENDER = '';
    const USERNAME_REQUIRED = 'Tên đăng ký phải có từ 6 ký tự';
    const PASSWORD_REQUIRED = 'Mật khẩu đăng ký phải có từ 6 ký tự ';
    const INVALID_EMAIL_FORMAT = 'Địa chỉ email không đúng định dạng';
    const DULICATE_USERNAME = 'Tên đăng ký đã được sử dụng';
    const DULICATE_USEREMAIL = 'Địa chỉ email đăng ký đã được sử dụng';
    const USERNAME_NOT_EXISTS = 'Tài khoản đăng nhập không tồn tại';
    const WRONG_PASSWORD = 'Mật khẩu đăng nhập không chính xác';
    const AUTHENTICATE_NOT_VALID = 'Tên đăng nhập hoặc mật khẩu không chính xác';

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var AuthenticationService
     */
    protected $authService;


    /**
     * @var boolean
     */
    protected $loadedUser = false;

    /**
     * @var \User\Model\User
     */
    protected $user;

    /**
     *
     * @var String
     */
    protected $companyRole;

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (!$this->authService) {
            $this->authService = $this->getServiceLocator()->get('User\Service\Auth');
        }
        return $this->authService;
    }

    /**
     * @param \Zend\Authentication\AuthenticationService $authService
     */
    public function setAuthService($authService)
    {
        $this->authService = $authService;
    }

    /**
     * @return bool
     */
    public function getLoadedUser()
    {
        return $this->loadedUser;
    }

    /**
     * @param bool $loadedUser
     */
    public function setLoadedUser($loadedUser)
    {
        $this->loadedUser = $loadedUser;
    }

    /**
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function authenticate($username, $password)
    {
        /* @var $sl \Zend\ServiceManager\ServiceManager */
        $sl = $this->getServiceLocator();

        $authAdapter = new CredentialTreatmentAdapter($sl->get('dbAdapter'),
            'users', 'username', 'password', 'MD5(CONCAT(salt,?))');
        $authAdapter->setIdentity($username);
        $authAdapter->setCredential($password);
        /* @var $result \Zend\Authentication\Result */
        $result = $this->getAuthService()->authenticate($authAdapter);
        if ($result->getCode() == \Zend\Authentication\Result::SUCCESS) {
            /* @var $userMapper \User\Model\UserMapper */
            $userMapper = $sl->get('User\Model\UserMapper');
            /* @var $user \User\Model\User */
            $user = $userMapper->get(null, $username);
            $this->getAuthService()->getStorage()->write($user->getId());
            return true;
        }
        return false;
    }

    /**
     * @param \User\Model\User $user
     * @return boolean
     */
    public function validateChangeInfo(\User\Model\User $user)
    {
        if (!$user->getId() && !$user->getUsername() && !$user->getEmail()) {
            return false;
        }
        $sl = $this->getServiceLocator();
        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $sl->get('User\Model\UserMapper');
        /* @var $adapter \Zend\Db\Adapter\Adapter */
        $adapter = $sl->get('dbAdapter');
        /* @var $sql \Zend\Db\Sql\Sql */
        $sql = $sl->get('dbSql');
        $select = $sql->select()->from('users')->columns(array('id'));
        if ($user->getId()) {
            $select->where(array('id' => $user->getId()));
        }
        if ($user->getUsername()) {
            $select->where(array('username' => $user->getUsername()));
        }
        if ($user->getEmail()) {
            $select->where(array('email' => $user->getEmail()));
        }
        $select->where(array('password' => new \Zend\Db\Sql\Expression('MD5(CONCAT(salt,"' . $user->getPassword() . '"))')));
        $query = $sql->buildSqlString($select);
        $result = $adapter->query($query, $adapter::QUERY_MODE_EXECUTE);
        if ($result->count()) {
            return true;
        } else {
            return false;
        }
    }

    public function isAvailableEmail($item){

    }

    /**
     * @param \User\Model\User $user
     * @return array
     */
    public function validateSigninInfo(\User\Model\User $user)
    {
        $error = array();
        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
        if (!$this->authenticate($user->getUsername(), $user->getPassword())) {
            $error['username'] = self::AUTHENTICATE_NOT_VALID;
        }
        return $error;
    }

    /**
     * @param \User\Model\User $user
     * @return array
     */
    public function validateSignupInfo(\User\Model\User $user)
    {
        $error = array();
        $validateRequire = new \Zend\Validator\StringLength(array('min' => 6));
        $validateEmail = new \Zend\Validator\EmailAddress();
        $validateUsername = new \Zend\Validator\Db\NoRecordExists(array('table' => 'users', 'field' => 'username', 'adapter' => $this->getServiceLocator()->get('dbAdapter')));
        $validateUserEmail = new \Zend\Validator\Db\NoRecordExists(array('table' => 'users', 'field' => 'email', 'adapter' => $this->getServiceLocator()->get('dbAdapter')));
        if (!$validateUsername->isValid($user->getUsername())) {
            $error['username'] = self::DULICATE_USERNAME;
        }
        if (!$validateRequire->isValid($user->getUsername())) {
            $error['username'] = self::USERNAME_REQUIRED;
        }
        if (!$validateRequire->isValid($user->getPassword())) {
            $error['password'] = self::PASSWORD_REQUIRED;
        }
        if (!$validateUserEmail->isValid($user->getEmail())) {
            $error['email'] = self::DULICATE_USEREMAIL;
        }
        if (!$validateEmail->isValid($user->getEmail())) {
            $error['email'] = self::INVALID_EMAIL_FORMAT;
        }
        return $error;
    }

    /**
     * @param \User\Model\User $user
     */
    public function signup(\User\Model\User $user)
    {
        $user->setSalt(substr(md5(time() . rand(2000, 5000)), 0, 20));
        $user->setCreatedDate(DateBase::getCurrentDate());
        $user->setCreatedDateTime(DateBase::getCurrentDateTime());
        $user->setPassword(md5($user->getSalt() . $user->getPassword()));
        $user->setRegisteredDate(date('Y-m-d'));
        $user->setRegisteredFrom(str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])));
        $user->setActiveKey((md5($user->getUsername() . $user->getPassword() . time())));
        $user->setActiveLink('http://' . $_SERVER['HTTP_HOST'] . '/user/active?u=' . $user->getUsername() . '&c=' . $user->getActiveKey());
        $user->setRole(\User\Model\User::ROLE_MEMBER);

        $sl = $this->getServiceLocator();
        $translator = $this->getServiceLocator()->get('translator');
        /** @var $mapper \User\Model\UserMapper */
        $mapper = $sl->get('User\Model\Usermapper');
        $mapper->save($user);

    }

    /**
     *
     * @param \User\Model\User $user
     */
//    public function sendActiveLink(\User\Model\User $user)
//    {
//        $sl = $this->getServiceLocator();
//        /* @var $userMapper \User\Model\UserMapper */
//        $userMapper = $sl->get('User\Model\UserMapper');
//        $translator = $this->getServiceLocator()->get('translator');
//        /* @var $storeMailMapper \Store\Model\StoreEmailMapper */
//        $storeMailMapper = $sl->get('Store\Model\StoreEmailMapper');
//        /* @var $storeMail \Store\Model\StoreEmail */
//        $storeMail = $storeMailMapper->getByStoreId($this->getServiceLocator()->get('Store\Service\Store')->getStoreId());
//        $us = $userMapper->get($user->getId(), $user->getUsername(), $user->getEmail());
//        $us->setActiveLink('http://' . $_SERVER['HTTP_HOST'] . '/user/active?u=' . $us->getUsername() . '&c=' . $us->getActiveKey());
//        $message = new Message();
//        $message->addTo($us->getEmail());
//        if ($storeMail->getEmail()) {
//            $message->addFrom($storeMail->getEmail(), $_SERVER['HTTP_HOST']);
//        } else {
//            $message->addFrom(self::DEFAULT_EMAIL_SENDER, $_SERVER['HTTP_HOST']);
//        }
//        $message->setSubject('Welcome to ' . $_SERVER['HTTP_HOST']);
//        $body = sprintf($translator->translate("Xin chào %s"), $us->getFullName());
//
//        // @todo fixed mail template for kitchenArt
//        if($this->getServiceLocator()->get('Store\Service\Store')->getStoreId() == 68){
//            $this->renderer = $this->getServiceLocator()->get('ViewRenderer');
//            $body .= "<br><br>";
//            $body .= $translator->translate('Để hoàn thành quá trình đăng kí, xin nhấp vào đường dẫn bên dưới để kích hoạt tài khoản của bạn');
//            $body .= "<br><br>";
//            $body .= "<a href='{$us->getActiveLink()}'>{$us->getActiveLink()}</a>";
//            $body .= $this->renderer->render('user/user/getactivecode');
//            $body .= "<br><br>";
//        }else{
//            $body .= "<br><br>";
//            $body .= $translator->translate('Để hoàn thành quá trình đăng kí, xin nhấp vào đường dẫn bên dưới để kích hoạt tài khoản của bạn');
//            $body .= "<br><br>";
//            $body .= "<a href='{$us->getActiveLink()}'>{$us->getActiveLink()}</a>";
//            $body .= "<br><br>";
//            $body .= $translator->translate('Xin cảm ơn!');
//            $body .= "<br>";
//        }
//        $html = new MimePart($body);
//        $html->type = 'text/html';
//        $content = new MimeMessage();
//        $content->setParts(array($html));
//        $message->setBody($content);
//        $smtp = new Smtp();
//        $smtp->setOptions(new SmtpOptions($sl->get('Store\Service\Store')->getStoreSmtpOptions()));
//        $smtp->send($message);
//    }

    /**
     * @param \User\Model\User $user
     */
    public function resetPassword(\User\Model\User $user)
    {
        $sl = $this->getServiceLocator();
        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $sl->get('User\Model\UserMapper');
        $translator = $this->getServiceLocator()->get('translator');

        //==============================
        $message = new Message();
        $message->addTo($user->getEmail());
        $message->addFrom(self::DEFAULT_EMAIL_SENDER, $_SERVER['HTTP_HOST']);
        $message->setSubject('Welcome to ' . $_SERVER['HTTP_HOST']);
        $link =  $_SERVER['HTTP_HOST'].'/user/user/resetpassword?email='.$user->getEmail().'&resetKey='.$user->getResetKey();
        $body = sprintf($translator->translate("Xin chào %s"), $user->getFullName());
        $body .= "<br/><br/>";
        $body .= "Hệ thống đã nhận được yêu cầu đổi mật khẩu của bạn. Xin vui lòng nhấn vào đường link phía dưới để thay đổi mật khẩu.";
        $body .= '<a href="'.$link.'">'.$link.'</a>';
        $body .= "<br/><br/>";
        $body .= 'Xin cảm ơn!';
        $body .= "<br/>";
        $body .= "<a href='http://erp.nhanh.vn'>http://erp.nhanh.vn</a>";
        $html = new MimePart($body);
        $html->type = 'text/html';
        $content = new MimeMessage();
        $content->setParts(array($html));
        $message->setBody($content);
        $smtp = new Smtp();
        $config = $sl->get('Config');
        $smtp->setOptions(new SmtpOptions($config['smtpOptions']));
        $smtp->send($message);
    }

    /**
     * @param \User\Model\User $user
     * @return boolean
     */
    public function updateUser(\User\Model\User $user)
    {
        if (!$user->getId() && !$user->getEmail() && !$user->getUsername()) {
            return false;
        }
        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
        if ($user->getPassword()) {
            $user->setSalt(substr(md5(rand(2000, 5000) . time() . rand(2000, 5000)), 0, 20));
            $user->setPassword(md5($user->getSalt() . $user->getPassword()));
        }
        return $userMapper->updateUser($user);
    }

    /**
     * clear identity
     */
    public function signout()
    {
        $this->getAuthService()->clearIdentity();
    }

    /**
     * @return bool
     */
    public function hasIdentity()
    {
        return $this->getAuthService()->hasIdentity();
    }

    /**
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getAuthService()->getIdentity();
    }

    /**
     * @param int|null $id
     * @return \User\Model\User
     */
    public function getUser($id = null)
    {
        if (!$this->getLoadedUser()) {
            /* @var \User\Model\UserMapper $userMapper */
            $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
            if ($id) {
                $this->user = $userMapper->get($id);
            } else {
                $this->user = $userMapper->get($this->getIdentity());
            }
            $this->setLoadedUser(true);
        }
        return $this->user;
    }

    /**
     * @return string
     */
    public function getRoleName()
    {
        if (!$this->hasIdentity()) {
            return "Guest";
        }
        return $this->getUser()->getRoleName();
    }

    /**
     * @author KienNN
     */
    public function getCompanyRole(){
        if($this->companyRole){
            return $this->companyRole;
        }
        $result = $this->getRoleName();
        $user = new \User\Model\User();
        if($this->getRoleName() && !in_array($this->getRoleName(), array(
                $user->getRoleName(\User\Model\User::ROLE_ADMIN),
                $user->getRoleName(\User\Model\User::ROLE_SUPERADMIN),
                $user->getRoleName(\User\Model\User::ROLE_GUEST),
            ))){
        }
        $this->companyRole = $result;
        return $this->companyRole;
    }

    /**
     *
     */
    public function isAdmin(){
        $user = $this->getUser();
        if($user && $user instanceof \User\Model\User && in_array($user->getRoleName(), array(
                $user->getRoleName($user::ROLE_ADMIN),
                $user->getRoleName($user::ROLE_SUPERADMIN)
            ))){
            return true;
        }
        return false;
    }

    /**
     * Hàm này đăng nhập người dùng dựa trên 1 SignInToken
     *
     * @param string $token SignInToken được truyền sang từ id.vatgia.com
     */
    function signInByToken($token)
    {
        $config = $this->getServiceLocator()->get('Config');
        $secretKey = $config['sso']['id.vatgia.com_IKI']['secretKey'];
        $publicKey = $config['oauths']['id.vatgia.com']['publicKey'];
        $s = new SignInToken(array(), $secretKey, $publicKey);
        if (SSOHelper::isRefererValid() && ($signinErr = $s->decrypt($token)) == SignInToken::ERROR_NONE) {
            // Header này chỉ định dành cho IE, để cho phép cross domain cookie
            header('P3P: CP="CAO PSA OUR"');

            $data = $s->getData(); // dữ liệu của SignInToken

            $info = $data['info'];

            // Vì id.vatgia.com chỉ cung cấp Họ Đệm và Tên, bạn có thể lấy tên đầy đủ như dưới đây
            $info['name'] = trim($info['first_name'] . ' ' . $info['last_name']);
            /* @var $userMapper \User\Model\UserMapper */
            $userMapper = $this->getServiceLocator()->get('User\Model\UserMapper');
            if (($user = $userMapper->get(null, null, $info['email'])) == null) {
                $user = new \User\Model\User();
                $user->setEmail($info['email']);
                $user->setFullName($info['name']);
                $user->setAddress($info['address']);
                $user->setBirthday($info['dob']);
                $user->setMobile($info['phone']);
                $user->setActive(1);
                $user->setRegisteredDate(date('Y-m-d H:i:s'));
                $userMapper->save($user);
            }

            // TODO lưu lại thông tin OAuth2 Access Token dùng để xác thực tài khoản trước khi lấy thông tin bảo kim...
            //$this->setState('oauth2_access_token', $data['oauth2_access_token']);
            // hoặc : $_SESSION['oauth2_access_token'] = $data['oauth2_access_token'];

            // Lưu lại gsn vào cookie
            SSOHelper::saveGSN($data['gsn'], $data['expired_time']);
            return $user;
        } else {
            echo $signinErr;
        }
        return null;
    }

    /**
     * @param string $email
     * @return boolean
     */
    public function authenticateFacebook($email)
    {
        /* @var $sl \Zend\ServiceManager\ServiceManager */
        $sl = $this->getServiceLocator();

        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $sl->get('User\Model\UserMapper');
        /* @var $user \User\Model\User */
        $user = $userMapper->get(null, null, $email);
        if($user)
        {
            $this->getAuthService()->getStorage()->write($user->getId());
            return $user;
        }
        return null;
    }

    /**
     * @param string $email
     * @return boolean
     */
    public function authenticateGoogle($email)
    {
        /* @var $sl \Zend\ServiceManager\ServiceManager */
        $sl = $this->getServiceLocator();

        /* @var $userMapper \User\Model\UserMapper */
        $userMapper = $sl->get('User\Model\UserMapper');
        /* @var $user \User\Model\User */
        $user = $userMapper->get(null, null, $email);
        if($user)
        {
            $this->getAuthService()->getStorage()->write($user->getId());
            return $user;
        }
        return null;
    }

    public function rendermenu(){
        /** @var $subjectMapper Subject/Model/SubjectMapper */
        $subjectMapper = $this->getServiceLocator()->get('Subject/Model/SubjectMapper');
        $subjects = $subjectMapper->featchAll('category');
        return $subjects;
    }



}