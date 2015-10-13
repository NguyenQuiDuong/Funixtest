<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace User\Model;

use Home\Model\Base;
use Home\Model\DateBase;

class User extends Base
{
    //IMAGE
    const ROLE_SUPERADMIN  	= 1;
    const ROLE_ADMIN  		= 2;
    const ROLE_MENTOR		= 5;

    const ROLE_MEMBER		= 200;
    const ROLE_GUEST 		= 255;

    protected $roles = array(
        self::ROLE_SUPERADMIN 	=> 'Super Admin',
        self::ROLE_ADMIN 		=> 'Admin',
        self::ROLE_MENTOR 		=> 'Mentor',
        self::ROLE_MEMBER		=> 'Member',
        self::ROLE_GUEST 		=> 'Guest',

    );

    protected $roleDisplays = array(
        self::ROLE_SUPERADMIN 	=> 'Super Admin',
        self::ROLE_ADMIN 		=> 'Admin',
        self::ROLE_MENTOR		=> 'Mentor',
    );

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $role;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $salt;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $fullName;

    /**
     * @var string
     */
    protected $avatar;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $mobile;

    /**
     * @var string
     */
    protected $gender;

    /**
     * @var string
     */
    protected $birthdate;

    /**
     * @var int
     */
    protected $cityId;

    /**
     * @var \Address\Model\City
     */
    protected $city;

    /**
     * @var \Address\Model\District
     */
    protected $district;

    /**
     * @var int
     */
    protected $districtId;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $lastAccess;

    /**
     *
     * @var string
     */
    protected $activeLink;

    /**
     * @var string
     */
    protected $activeKey;

    /**
     * @var string
     */
    protected $resetKey;

    /**
     * @var string
     */
    protected $locked;


    const STATUS_ACTIVE = 1;
    /**
     * @var string
     */
    protected $active;

    /**
     * @var string
     */
    protected $rememberMe;

    /**
     * @var string
     */
    protected $registeredDate;

    /**
     * @var string
     */
    protected $registeredFrom;

    /**
     * @var string
     */
    protected $createdById;

    /**
     * @var string
     */
    protected $createdDate;

    /**
     * @var string
     */
    protected $createdDateTime;


    /**
     * @return the $createdById
     */
    public function getCreatedById() {
        return $this->createdById;
    }

    /**
     * @return the $createdDate
     */
    public function getCreatedDate() {
        return $this->createdDate;
    }

    /**
     * @return the $createdDateTime
     */
    public function getCreatedDateTime() {
        return $this->createdDateTime;
    }

    /**
     * @param string $createdById
     */
    public function setCreatedById($createdById) {
        $this->createdById = $createdById;
    }

    /**
     * @param string $createdDate
     */
    public function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;
    }

    /**
     * @param string $createdDateTime
     */
    public function setCreatedDateTime($createdDateTime) {
        $this->createdDateTime = $createdDateTime;
    }

    /**
     * @param string $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $activeKey
     */
    public function setActiveKey($activeKey)
    {
        $this->activeKey = $activeKey;
    }

    /**
     * @return string
     */
    public function getActiveKey()
    {
        return $this->activeKey;
    }

    /**
     * @param string $activeLink
     */
    public function setActiveLink($activeLink)
    {
        $this->activeLink = $activeLink;
    }

    /**
     * @return string
     */
    public function getActiveLink()
    {
        return $this->activeLink;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }


    /**
     * @param \Address\Model\City $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return \Address\Model\City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param int $cityId
     */
    public function setCityId($cityId)
    {
        $this->cityId = $cityId;
    }

    /**
     * @return int
     */
    public function getCityId()
    {
        return $this->cityId;
    }

    /**
     * @param \Address\Model\District $district
     */
    public function setDistrict($district)
    {
        $this->district = $district;
    }

    /**
     * @return \Address\Model\District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    /**
     * @param int $districtId
     */
    public function setDistrictId($districtId)
    {
        $this->districtId = $districtId;
    }

    /**
     * @return int
     */
    public function getDistrictId()
    {
        return $this->districtId;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return the $birthdate
     */
    public function getBirthdate() {
        return $this->birthdate;
    }

    /**
     * @param string $birthdate
     */
    public function setBirthdate($birthdate) {
        $this->birthdate = $birthdate;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $fullName
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * @param string $avatar
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;
    }

    /**
     * @return string
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $lastAccess
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;
    }

    /**
     * @return string
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * @param string $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return string
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param string $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return string
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $registeredDate
     */
    public function setRegisteredDate($registeredDate)
    {
        $this->registeredDate = $registeredDate;
    }

    /**
     * @return string
     */
    public function getRegisteredDate()
    {
        return $this->registeredDate;
    }

    /**
     * @return the $registeredFrom
     */
    public function getRegisteredFrom()
    {
        return $this->registeredFrom;
    }

    /**
     * @param string $registeredFrom
     */
    public function setRegisteredFrom($registeredFrom)
    {
        $this->registeredFrom = $registeredFrom;
    }

    /**
     * @param string $rememberMe
     */
    public function setRememberMe($rememberMe)
    {
        $this->rememberMe = $rememberMe;
    }

    /**
     * @return string
     */
    public function getRememberMe()
    {
        return $this->rememberMe;
    }

    /**
     * @param string $resetKey
     */
    public function setResetKey($resetKey)
    {
        $this->resetKey = $resetKey;
    }

    /**
     * @return string
     */
    public function getResetKey()
    {
        return $this->resetKey;
    }

    /**
     * @param string $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getActiveUri()
    {
        return "/user/active/?username={$this->getUsername()}&activeKey={$this->getActiveKey()}";
    }

    /**
     * convert model to stdClass
     */
    public function toStd()
    {
        $o = new \stdClass();
        $o->username = $this->getUsername();
        $o->fullname = $this->getFullName();
        $o->birthdate = $this->getBirthdate();
        $o->address = $this->getAddress();
        $o->email = $this->getEmail();
        $o->mobile = $this->getMobile();
        $o->gender = $this->getGender();
        $o->city = $this->getCityId();
        $o->district = $this->getDistrictId();
        return $o;
    }
    /**
     * @return the $roles
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @return the $roleDisplays
     */
    public function getRoleDisplays() {
        return $this->roleDisplays;
    }

    public function getRoleName($role = null){
        $role = $role?:$this->getRole();
        if(isset($this->roles[$role])){
            return $this->roles[$role];
        }
        return '';
    }

    public function getRoleDisplayName($role = null){
        $role = $role?:$this->getRole();
        if(isset($this->roleDisplays[$role])){
            return $this->roleDisplays[$role];
        }
        return '';
    }

    public function getDisplayNameFromRoleName($name = null){
        $name = $name?:$this->getRoleName();
        $role = array_search($name, $this->roles);
        if($role !== false){
            if(isset($this->roleDisplays[$role])){
                return $this->roleDisplays[$role];
            }
        }
        return '';
    }

    /**
     * generate salt
     */
    public function generateSalt() {
        $salt = $this->getUsername() . time() . rand(0, 100);
        return substr(md5($salt), 0, 20);
    }

    /**
     * @author VanCK
     * create password
     */
    public function createPassword ($password = null) {
        $pass = $password ? $password : $this->getPassword();
        $salt = $this->getSalt();
        return md5($salt . $pass);
    }


    public function isAdmin() {
        return in_array($this->getRole(), [self::ROLE_ADMIN, self::ROLE_SUPERADMIN]);
    }


    public function toFormValues(){
        $data = array(
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'fullName' => $this->getFullName(),
            'gender' => $this->getGender(),
            'cityId' => $this->getCityId(),
            'districtId' => $this->getDistrictId(),
            'address' => $this->getAddress(),
            'mobile' => $this->getMobile(),
            'role' => $this->getRole()
        );
        if($this->getBirthdate()){
            $data['birthdate'] = DateBase::toDisplayDate($this->getBirthdate());
        }
        return $data;
    }
    public function prepairSuggest($q=null){
        /* $htmlPurifiler = new \Home\Filter\HTMLPurifier();
            $q = $htmlPurifiler->filter($q); */
        $this->setUsername($q?:$this->getUsername());
    }

    public function getSelectableRole(){
        return array(
            self::ROLE_MENTOR							=> 'Mentor',
        );
    }

}