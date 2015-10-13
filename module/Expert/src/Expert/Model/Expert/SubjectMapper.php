<?php

namespace Expert\Model\Expert;

use Expert\Model\ExpertMapper;
use Home\Model\BaseMapper;
use User\Model\User;
use User\Model\UserMapper;
use Zend\Db\Adapter\Adapter;
class SubjectMapper extends BaseMapper
{

    CONST TABLE_NAME = 'expert_subjects';

    /**
     *
     * @author DuongNQ
     * @return array null
     * @param \Expert\Model\Expert\Subject $exp
     */
    public function save($exp)
    {
        $data = array(
            'expertId'    =>  $exp->getExpertId(),
            'subjectId' => $exp->getSubjectId(),
            'createdById'=>$exp->getCreatedById(),
            'createdDateTime' => $exp->getCreatedDateTime(),

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
        return $results;
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
            $userIds[] = $expert->getId();
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
            $userId = $expert->getId();
            $expert->addOption('user',isset($users[$userId])?$users[$userId]:null);
        }


        return $paginator;
    }

    /**
     * @author DuongNQ
     * @param \Expert\Model\Expert\Subject $exp
     */
    public function featchAll($exp)
    {
        $select = $this->getDbSql()->select(array(
            'es' => self::TABLE_NAME
        ));
        $select->columns(['subjectId']);
        $select->join(['e'=>ExpertMapper::TABLE_NAME],'e.id=es.expertId',array('id'));
        $select->join(['s'=>\Subject\Model\SubjectMapper::TABLE_NAME],'s.id=es.subjectId',['subjectName'=>'name']);
        $select->order([
            'e.id' => 'DESC'
        ]);
        if($exp->getOption('subjectIds')){
            $select->where(['subjectId'=>$exp->getOption('subjectIds')]);
        }else{
            $select->where(['subjectId'=>$exp->getSubjectId()]);
        }
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
        $subjects = [];
        if(count($results)){
            $results = $results->toArray();
            foreach($results as $r){
                $subjects[$r['id']][] = $r['subjectName'];
                $expertIds[] = $r['id'];
            }
        }
        unset($select);
        $select = $this->getDbSql()->select(array(
            'e' =>  ExpertMapper::TABLE_NAME
        ));
        $select->columns(['id','description','rating','rate','extraContent']);
        $select->where(['e.id' => $expertIds]);
        $select->join(['u' => UserMapper::TABLE_NAME],'e.userId = u.id',['username','fullName']);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
        if(count($results)){
            $results = $results->toArray();
            foreach($results as $r){
                if(isset($subjects[$r['id']])){
                    $r['subject'] = $subjects[$r['id']];
                }
                $mentors[] = $r;
            }
        }

        return $mentors;
    }
}