<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace User\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;
use User\Model\User;
use Zend\Db\Sql\Expression;
use Work\Model\ProjectUserMapper;
use Work\Model\ProjectUser;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Predicate\In;
use Zend\Db\Sql\Predicate\Operator;
use Hrm\Model\EmployeeMapper;
use Address\Model\CityMapper;
use Address\Model\DistrictMapper;
use Company\Model\User\FeatureMapper;
use Company\Model\DepartmentMapper;

class UserMapper extends BaseMapper
{
    CONST TABLE_NAME = 'users';

    /**
     * @param \User\Model\User $user
     * @return true|false
     */
    public function save($user)
    {
        $data = array(
            'role' => $user->getRole(),
            'username' => $user->getUsername()?:null,
            'email' => $user->getEmail()?:null,
            'password' => $user->getPassword()?:null,
            'salt' => $user->getSalt()?:null,
            'fullName' => $user->getFullName()?:null,
            'avatar' => $user->getAvatar()?:null,
            'gender' => $user->getGender()?:null,
            'birthdate' => $user->getBirthdate()?:null,
            'cityId' => $user->getCityId()?:null,
            'districtId' => $user->getDistrictId()?:null,
            'address' => $user->getAddress()?:null,
            'mobile' => $user->getMobile()?:null,
            'lastAccess' => $user->getLastAccess()?:null,
            'active' => $user->getActive()?:null,
            'createdById' => $user->getCreatedById()?:null,
            'createdDate' => $user->getCreatedDate(),
            'createdDateTime' => $user->getCreatedDateTime(),
        );

        $results = false;
        if (!($id = $user->getId())) {
            $insert = $this->getDbSql()->insert(self::TABLE_NAME);
            $insert->values($data);
            $query = $this->getDbSql()->buildSqlString($insert);
            $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
            $user->setId($results->getGeneratedValue());
        } else {
            $update = $this->getDbSql()->update(self::TABLE_NAME);
            $update->set($data);
            $update->where(array("id"=> (int)$user->getId()));
            $query = $this->getDbSql()->buildSqlString($update);
            $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        }
        return $results;
    }

    public function updateUser(User $user)
    {
        $updateArray = array(
            'username' => $user->getUsername() ?: null,
            'role' => $user->getRole() ?: null,
            'salt' => $user->getSalt() ?: null,
            'password' => $user->getPassword() ?: null,
            'fullName' => $user->getFullName() ?: null,
            'gender' => $user->getGender() ?: null,
            'birthdate' => $user->getBirthdate() ?: null,
            'email' => $user->getEmail() ?: null,
            'mobile' => $user->getMobile() ?: null,
            'cityId' => ((int)$user->getCityId()) ?: null,
            'districtId' => ((int)$user->getDistrictId()) ?: null,
            'address' => $user->getAddress() ?: null,
            'rememberMe' => $user->getRememberMe() ?: null,
            'lastAccess' => $user->getLastAccess() ?: null,
            'activeKey' => $user->getActiveKey() ?: null,
            'resetKey' => $user->getResetKey() ?: null,
            'active' => $user->getActive(),
            'registeredDate' => $user->getRegisteredDate() ?: null,
        );
        $updateArray = array_filter($updateArray,'strlen');
        $updateArray = array_filter($updateArray);
        $update = $this->getDbSql()->update(self::TABLE_NAME);
        if($user->getId()) {
            $update->where(array('id' => $user->getId()));
        }

        $update->set($updateArray);
        $query = $this->getDbSql()->buildSqlString($update);

        $result = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        return $result;
    }

    /**
     * @return array|null
     * @param \User\Model\User $item
     */
    public function fetchAll($item)
    {
        $dbAdapter = $this->getDbAdapter();

        $select = $this->getDbSql()->select(['user' => self::TABLE_NAME]);
        if($item->getId()){
            $select->where(['id' => $item->getId()]);
        } elseif ($item->getOption('ids')){
            $select->where(['id' => $item->getOption('ids')]);
        }
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($results->count()) {
            $users = array();
            $userIds = [];
            foreach ($results as $row) {
                $user = new User();
                $user->exchangeArray((array) $row);
                $users[] = $user;
                $userIds[$user->getId()] = $user->getId();
            }
            return $users;
        }
        return null;
    }

    /**
     * @param int|null $id
     * @param string|null $username
     * @param string|null $email
     * @return User
     */
    public function get($id = null, $username = null , $email = null , $option = null)
    {
        if(!$id && !$username &&!$email) {
            return null;
        }

        $select = $this->getDbSql()->select(['u' => self::TABLE_NAME]);
        if($id) {
            $select->where(array('u.id' => $id));
        }
        if ($username) {
            $select->where(array('u.username' => $username));
        }
        if($email)
        {
            $select->where(array('u.email' => $email));
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($results->count()) {
            /* @var $dtMapper \Address\Model\DistrictMapper */
            $dtMapper = $this->getServiceLocator()->get('Address\Model\DistrictMapper');
            /* @var $ctMapper \Address\Model\CityMapper */
            $ctMapper = $this->getServiceLocator()->get('Address\Model\CityMapper');
            $user = new User();
            $row = (array)$results->current();
            $user->exchangeArray($row);
            if ($option){
                $user->addOption('departmentName', $row['name']);
            }
            $user->setCity($ctMapper->get($row['cityId']));
            $user->setDistrict($dtMapper->get($row['districtId']));
            return $user;
        }
        return null;
    }

    /**
     * @return array|null
     * @param \User\Model\User $user
     */
    public function getUser($user){
        if (! $user->getId()) {
            return null;
        }
        $select = $this->getDbSql()->select(array(
            'u' => self::TABLE_NAME
        ));

        if ($user->getId()) {
            $select->where([
                'u.id' => $user->getId()
            ]);
        }


        $select->limit(1);


        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $data = $results->current();
            $user->exchangeArray((array) $results->current());
            $select1 = clone $select;
            if($user->getCityId()){
                $select1->join(array('c' => CityMapper::TABLE_NAME), 'u.cityId = c.id', ['cityName'=>'nativeName'],'left');
            }
            if($user->getDistrictId()){
                $select1->join(array('d' => DistrictMapper::TABLE_NAME), 'u.districtId = d.id', ['districtName'=>'name'],'left');
            }
            $query1 = $this->getDbSql()->buildSqlString($select1);
            $results1 = $this->getDbAdapter()->query($query1, Adapter::QUERY_MODE_EXECUTE);
            $data1 = $results1->current();
            if(isset($data1['cityName']) && $data1['cityName']){
                $user->addOption('cityName', $data1['cityName']);
            }
            if(isset($data1['districtName']) && $data1['districtName'] ){
                $user->addOption('districtName', $data1['districtName']);
            }
            return $user;
        }

        return null;
    }

    /**
     * @param \User\Model\User $user
     * @return true|false
     */
    public function checkExistsUserActive($user)
    {
        if(!$user->getActiveKey() || !$user->getUsername()) {
            return false;
        }
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        $select->where([
            'username' => $user->getUsername(),
            'activeKey' => $user->getActiveKey()
        ]);
        $select->where('active IS NULL');
        $query = $this->getDbSql()->buildSqlString($select);
        $result = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($result->count() == 1) {
            return true;
        }
        return false;
    }

    public function isEmailAvailable($user){
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        $select->where([
            'email'  =>  $user->getEmail()
        ]);
        $query = $this->getDbSql()->buildSqlString($select);
        $result = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $resultData = $result->toArray();
        if($resultData){
            foreach($resultData as $r){
                if(!$r['username']){
                    unset($r['role']);
                    unset($r['salt']);
                    $r['password'] = $user->getPassword();
                    $r['username'] = $user->getUsername();
                    $user->exchangeArray($r);
                    return true;
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     *
     * @param \User\Model\User $user
     * @return true|false
     */
    public function activeUser($user)
    {
        if(!$this->checkExistsUserActive($user)) {
            return false;
        } else {
            $update = $this->getDbSql()->update(self::TABLE_NAME);
            $update->set(array('active' => 1));
            $update->where(['username' => $user->getUsername(), 'activeKey' => $user->getActiveKey()]);
            $query = $this->getDbSql()->buildSqlString($update);
            $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
            return true;
        }
    }

    /**
     *
     * @param \User\Model\User $item
     */
    public function isExisted($item){
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        if($item->getUsername()){
            $select->where(['username' => $item->getUsername()]);
        }
        if($item->getEmail()){
            $select->where(['email' => $item->getEmail()]);
        }
        if($item->getId()){
            $select->where(['id != ?' => $item->getId()]);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $result = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($result->count() == 1) {
            $item->exchangeArray((array) $result->current());
            return true;
        }
        return false;
    }
    public function isExistedEmail($item){
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        if($item->getEmail()){
            $select->where(['email' => $item->getEmail()]);
        }
        if($item->getId()){
            $select->where(['id != ?' => $item->getId()]);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $result = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($result->count() == 1) {
            $item->exchangeArray((array) $result->current());
            return true;
        }
        return false;
    }
    public function isExistedUserName($item){
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        if($item->getUsername()){
            $select->where(['username' => $item->getUsername()]);
        }
        if($item->getId()){
            $select->where(['id != ?' => $item->getId()]);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $result = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($result->count() == 1) {
            $item->exchangeArray((array) $result->current());
            return true;
        }
        return false;
    }

    /**
     *
     * @param \User\Model\User $item
     * @param unknown $options
     */
    public function search($item, $options){
        $select = $this->getDbSql()->select(array('u' => self::TABLE_NAME));
        if($item->getId()){
            $select->where(['u.id'=>$item->getId()]);
        }
        if($item->getUsername()){
            $select->where([
                '(u.username LIKE ? OR u.fullName LIKE ?)' => ['%'. $item->getUsername().'%', '%'. $item->getUsername().'%']
            ]);
        }
        if($item->getEmail()){
            $select->where([
                '(u.email LIKE ?)' => '%'. $item->getEmail().'%'
            ]);
        }

        if($item->getActive()){
            if($item->getActive() > 0){
                $select->where(['u.active' => 1]);
            } else {
                $select->where(['(u.active IS NULL OR u.active != ?)' => 1]);
            }

        }

        if($item->getRole()){
            $select->where(['u.role'=>$item->getRole()]);
        }
        $select->order(['u.id' => 'DESC']);
        /* $query = $this->getDbSql()->buildSqlString($select);
        vdump($query);die; */
        $paginator = $this->preparePaginator($select, $options, new User());

        $userIds = [];
        $districIds = [];
        $cityIds = [];
        $ids = [];

        foreach ($paginator as $user){
            /*@var $user \User\Model\User */
            if($user->getCreatedById()){
                $userIds[$user->getCreatedById()] = $user->getCreatedById();
            }
            if($user->getDistrictId()){
                $districIds[$user->getDistrictId()] = $user->getDistrictId();
            }
            if($user->getCityId()){
                $cityIds[$user->getCityId()] = $user->getCityId();
            }
            $ids[] = $user->getId();
        }
        $dbAdapter = $this->getDbAdapter();
        $users = [];
        if(count($userIds)){
            $select = $this->getDbSql()->select(array('u' => self::TABLE_NAME));
            $select->where(['u.id' => $userIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows as $row){
                    $createdBy = new User();
                    $createdBy->exchangeArray((array)$row);
                    $users[$createdBy->getId()] = $createdBy;
                }
            }
        }


        $cities = [];
        if(count($cityIds)){
            $select = $this->getDbSql()->select(array('c' => \Address\Model\CityMapper::TABLE_NAME));
            $select->where(['id' => $cityIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows as $row){
                    $city = new \Address\Model\City();
                    $city->exchangeArray((array)$row);
                    $cities[$city->getId()] = $city;
                }
            }
        }
        $districs = [];
        if(count($districIds)){
            $select = $this->getDbSql()->select(array('c' => \Address\Model\DistrictMapper::TABLE_NAME));
            $select->where(['id' => $districIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $rows = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows as $row){
                    $distric = new \Address\Model\District();
                    $distric->exchangeArray((array)$row);
                    $districs[$distric->getId()] = $distric;
                }
            }
        }
        if(count($paginator->getCurrentModels())){
            foreach ($paginator->getCurrentModels() as $user){
                if($user->getCreatedById() && isset($users[$user->getCreatedById()])){
                    $user->addOption('createdBy', $users[$user->getCreatedById()]);
                }
                if($user->getCityId() && isset($cities[$user->getCityId()])){
                    $user->setCity($cities[$user->getCityId()]);
                }
                if($user->getDistrictId() && isset($districs[$user->getDistrictId()])){
                    $user->setDistrict($districs[$user->getDistrictId()]);
                }
            }
        }
        return $paginator;
    }

    /**
     * @param \User\Model\User $item
     */
    public function suggest($item){
        $item->prepairSuggest();
        $select = $this->getDbSql()->select(array('u' => self::TABLE_NAME), array(
            'id', 'username', 'fullName', 'email', 'role'
        ));
        $select->columns(['id', 'username', 'fullName', 'email', 'role']);
        $select->where([
            '(u.username LIKE ? OR u.fullName LIKE ? OR u.email LIKE ?)' =>
                ['%'.$item->getUsername().'%', '%'.$item->getUsername().'%', '%'.$item->getUsername().'%']
        ]);
        $select->where(['role  != ?'=> User::ROLE_MENTOR]);
//        $select->where(['role  != ?'=> User::ROLE_SUPERADMIN]);
        if($item->getOption('ids')){
            $select->where(['u.id' => $item->getOption('ids')]);
        }
        if($item->getRole()){
            $select->where(['u.role' => $item->getRole()]);
        }

        $select->limit(20);
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $result = [];
        $userIds = [];
        if($rows->count()){
            foreach ($rows as $row){
                $row = (array) $row;
                $row['label'] = $row['username'].' - '.($row['fullName'] ?: $row['email']);
                $result[] = $row;
                $userIds[$row['id']] = $row['id'];
            }
        }

        return $result;
    }

    /**
     * @param \User\Model\User $item
     */
    public function loadIds($item){
        $select = $this->getDbSql()->select(array('u' => self::TABLE_NAME));
        $select->columns(['id']);
        if($item->getUsername()){
            $select->where([
                '(u.username LIKE ? OR u.fullName LIKE ? OR u.email LIKE ?)' =>
                    ['%'.$item->getUsername().'%', '%'.$item->getUsername().'%', '%'.$item->getUsername().'%']
            ]);
        }
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $result = [];
        if($rows->count()){
            foreach ($rows as $row){
                $row = (array) $row;
                $result[$row['id']] = $row['id'];
            }
        }
        if(count($result)){
            return $result;
        } else{
            return [-1];
        }
    }


}