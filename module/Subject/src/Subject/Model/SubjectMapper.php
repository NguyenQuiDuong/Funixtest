<?php

namespace Subject\Model;

use Subject\Model\Subject;
use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use User\Model\User;
use User\Model\UserMapper;
use Zend\Console\Prompt\Select;
// use Home\Model;
class SubjectMapper extends BaseMapper
{

    CONST TABLE_NAME = 'subjects';

    /**
     *
     * @author DuongNQ
     * @return array null
     * @param \Subject\Model\Subject $sub
     */
    public function save($sub)
    {
        $data = array(
            'name' => $sub->getName(),
            'categoryId' => $sub->getCategoryId(),
            'description' => $sub->getDescription() ?  : null,
            'createdById'=>$sub->getCreatedById(),
            'createdDateTime' => $sub->getCreatedDateTime(),
          
        );
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
    if (!$sub->getId()) {
			$insert = $this->getDbSql()->insert(self::TABLE_NAME);
			$insert->values($data);
			$query = $dbSql->buildSqlString($insert);

			/* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
			$sub->setId($results->getGeneratedValue());
		} else {
			$update = $this->getDbSql()->update(self::TABLE_NAME);
			$update->set($data);
			$update->where(['id' => (int)$sub->getId()]);
			$query = $dbSql->buildSqlString($update);
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		}
        return $results;
    }

    /**
     *
     * @author DuongNQ
     * @param \Subject\Model\Subject $sub
     */
public function get($sub){
		if(!$sub->getId()){
			return null;
		}
		$select = $this->getDbSql()->select(array('s' => self::TABLE_NAME));
		$select->where(['id' => $sub->getId()]);
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$dbAdapter = $this->getDbAdapter();
		$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($results->count()){
			$sub->exchangeArray((array) $results->current());
			return $sub;
		}
		return null;
	}

    /**
     * @author DuongNQ
     * @param \Subject\Model\Subject $sub
     */
    public function isExisted($sub)
    {
        if (!$sub->getName()) {
            return null;
        }
        $select = $this->getDbSql()->select(array('d' => self::TABLE_NAME));
        $select->where([
            'name' => $sub->getName(),
        ]);
        if($sub->getCategoryId()){
        	$select->where(['categoryId' => $sub->getCategoryId()]);
        } else {
        	$select->where(['categoryId IS NULL']);
        }
        if ($sub->getId()) {
            $select->where(['id != ?' => $sub->getId()]);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $sub->exchangeArray((array) $results->current());
            return true;
        }
        return false;
    }

    /**
     * @author DuongNQ
     * @param \Subject\Model\Subject $sub
     */
    public function search($sub, $options)
    {
        $select = $this->getDbSql()->select(array(
            's' => self::TABLE_NAME
        ));
        $select->order([
            's.id' => 'DESC'
        ]);
        $paginator = $this->preparePaginator($select, $options, new Subject());
        $categoryIds = array();
        $categories =  array();
        /** @var /Subject/Model/Subject $subject */
        foreach($paginator as $subject){
            $categoryIds[] = $subject->getCategoryId();
        }

        if($categoryIds){
            $select = $this->getDbSql()->select(['ca'=>Subject\CategoryMapper::TABLE_NAME]);
            $select->where(['ca.id'=>$categoryIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $result = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
            if(count($result)){
                $resultArray = $result->toArray();
                foreach($resultArray as $u){
                    $category = new Subject\Category();
                    $categories[$u['id']] = $category->exchangeArray($u);
                }
            }
        }

        /** @var /Subject/Model/Subject $subject */
        foreach($paginator->getCurrentModels() as $subject){
            $categoryId = $subject->getCategoryId();
            $subject->addOption('category',isset($categories[$categoryId])?$categories[$categoryId]:null);
        }

        return $paginator;
    }

    /**
     * @author DuongNQ
     * @param \Subject\Model\Subject $sub
     */
    public function featchAll($options)
    {
        $select = $this->getDbSql()->select(array(
            's' => self::TABLE_NAME
        ));
        if($options == 'category'){
            $select->join(['c'=>Subject\CategoryMapper::TABLE_NAME],'s.categoryId = c.id',['categoryName'=>'name']);
        }
        if($options instanceof Subject && $options->getCategoryId() ){
            $select->where(['s.categoryId' => $options->getCategoryId()]);
        }
        $select->order([
            's.id' => 'DESC'
        ]);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
        $subjects = array();
        if(count($results)){
            foreach($results as $r){
                if($r['categoryName']){
                    $subjects[$r['categoryId'].'-'.$r['categoryName']][] = (array)$r;
                }else{
                    $subject = new Subject();
                    $subjects[] = $subject->exchangeArray((array)$r);
                }
            }
        };
        return $subjects;
    }

    /**
     * @param \Subject\Model\Subject $item
     * @return array
     */
    public function suggest($item){
        $select = $this->getDbSql()->select(array('s' => self::TABLE_NAME), array(
            'id', 'name'
        ));
        $select->where([
            '(s.name LIKE ?)' =>
                ['%'.$item->getName().'%']
        ]);
        $select->limit(20);
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $result = [];
        if($rows->count()){
            foreach ($rows as $row){
                $row = (array) $row;
                $row['label'] = $row['name'];
                $result[] = $row;
            }
        }
        return $result;
    }
}