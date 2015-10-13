<?php

namespace Expert\Model;

use Subject\Model\Subject;
use Home\Model\BaseMapper;
use User\Model\User;
use User\Model\UserMapper;
use Zend\Db\Adapter\Adapter;
class ExpertMapper extends BaseMapper
{

    CONST TABLE_NAME = 'experts';

    /**
     *
     * @author DuongNQ
     * @return array null
     * @param \Expert\Model\Expert $exp
     */
    public function save($exp)
    {
        $data = array(
            'userId'    =>  $exp->getUserId(),
            'description' => $exp->getDescription() ?  : null,
            'rating'    =>  $exp->getRating() ? : null,
            'rate'      =>  $exp->getRate() ? : null,
            'createdById'=>$exp->getCreatedById(),
            'createdDateTime' => $exp->getCreatedDateTime(),
            'extraContent'  =>  $exp->getExtraContent() ? : null,

        );
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        if (!$exp->getId()) {
            $insert = $this->getDbSql()->insert(self::TABLE_NAME);
            $insert->values($data);
            $query = $dbSql->buildSqlString($insert);

            /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            $exp->setId($results->getGeneratedValue());
        } else {
            $update = $this->getDbSql()->update(self::TABLE_NAME);
            $update->set($data);
            $update->where(['id' => (int)$exp->getId()]);
            $query = $dbSql->buildSqlString($update);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        }
        return $exp;
    }

    /**
     *
     * @author DuongNQ
     * @param \Expert\Model\Expert $exp
     */
    public function get($exp){
        if(!$exp->getId()){
            return null;
        }
        $select = $this->getDbSql()->select(array('e' => self::TABLE_NAME));
        $select->where(['id' => $exp->getId()]);
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $dbAdapter = $this->getDbAdapter();
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        if($results->count()){
            $exp->exchangeArray((array) $results->current());
            return $exp;
        }
        return null;
    }



    /**
     * @author DuongNQ
     * @param \Expert\Model\Expert $exp
     */
    public function search($exp, $options)
    {
        $select = $this->getDbSql()->select(array(
            'e' => self::TABLE_NAME
        ));

        $select->order([
            'e.id' => 'DESC'
        ]);
        $paginator = $this->preparePaginator($select, $options, new Expert());
        $userIds = array();
        $users =  array();
        /** @var Expert/Model/Expert $expert */
        foreach($paginator as $expert){
            $userIds[] = $expert->getUserId();
        }

        if($userIds){
            $select = $this->getDbSql()->select(['u'=>UserMapper::TABLE_NAME]);
            $select->where(['u.id'=>$userIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $result = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
            if(count($result)){
                $resultArray = $result->toArray();
                foreach($resultArray as $u){
                    $user = new User();
                    $users[$u['id']] = $user->exchangeArray($u);
                }
            }
        }

        /** @var /Expert/Model/Expert $expert */
        foreach($paginator->getCurrentModels() as $expert){
            $userId = $expert->getUserId();
            $expert->addOption('user',isset($users[$userId])?$users[$userId]:null);
        }


        return $paginator;
    }

    /**
     * @author DuongNQ
     * @param \Expert\Model\Expert $exp
     */
    public function featchAll($exp)
    {
        $select = $this->getDbSql()->select(array(
            'e' => self::TABLE_NAME
        ));
        $select->order([
            'e.id' => 'DESC'
        ]);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
        $subjects = $results->toArray();
        return $subjects;
    }
}