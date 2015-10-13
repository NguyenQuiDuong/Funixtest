<?php

namespace Subject\Model\Subject;

use Subject\Model\Subject;
use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;
// use Home\Model;
class CategoryMapper extends BaseMapper
{

    CONST TABLE_NAME = 'subject_categories';

    /**
     *
     * @author DuongNQ
     * @return array null
     * @param \Subject\Model\Subject\Category $cate
     */
    public function save($cate)
    {
        $data = array(
            'name' => $cate->getName(),
            'description' => $cate->getDescription() ?  : null,
            'createdById'=>$cate->getCreatedById(),
            'createdDateTime' => $cate->getCreatedDateTime(),
            'status'    =>  $cate->getStatus(),
          
        );
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
    if (!$cate->getId()) {
			$insert = $this->getDbSql()->insert(self::TABLE_NAME);
			$insert->values($data);
			$query = $dbSql->buildSqlString($insert);

			/* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
			$cate->setId($results->getGeneratedValue());
		} else {
			$update = $this->getDbSql()->update(self::TABLE_NAME);
			$update->set($data);
			$update->where(['id' => (int)$cate->getId()]);
			$query = $dbSql->buildSqlString($update);
			$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		}
        return $results;
    }

    /**
     *
     * @author DuongNQ
     * @param \Subject\Model\Subject\Category $cate
     */
public function get($cate){
		if(!$cate->getId()){
			return null;
		}
		$select = $this->getDbSql()->select(array('s' => self::TABLE_NAME));
		$select->where(['s.id' => $cate->getId()]);
		$select->limit(1);
		$query = $this->getDbSql()->buildSqlString($select);
		$dbAdapter = $this->getDbAdapter();
		$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
		if($results->count()){
			$cate->exchangeArray((array) $results->current());
			return $cate;
		}
		return null;
	}

    /**
     * @author DuongNQ
     * @param \Subject\Model\Subject\Category $cate
     */
    public function isExisted($cate)
    {
        if (!$cate->getName()) {
            return null;
        }
        $select = $this->getDbSql()->select(array('d' => self::TABLE_NAME));
        $select->where([
            'name' => $cate->getName(),
        ]);
        if ($cate->getId()) {
            $select->where(['id != ?' => $cate->getId()]);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $cate->exchangeArray((array) $results->current());
            return true;
        }
        return false;
    }

    /**
     * @author DuongNQ
     * @param \Subject\Model\Subject\Category $cate
     */
    public function search($cate, $options)
    {
        $select = $this->getDbSql()->select(array(
            's' => self::TABLE_NAME
        ));
        $select->order([
            's.id' => 'DESC'
        ]);
        $paginator = $this->preparePaginator($select, $options, new Subject());
        

        return $paginator;
    }

    /**
     * @return array|null
     */
    public function fetchAll()
    {
        $dbAdapter = $this->getDbAdapter();

        $select = $this->getDbSql()->select(['ca' => self::TABLE_NAME]);
        $select->where(['ca.status' => Subject::STATUS_ACTIVE]);

        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($results) {
            $categories = array();
            foreach ($results as $row) {
                $ct = new Category();
                $ct->exchangeArray((array)$row);
                $categories[] = $ct;
            }
            return $categories;
        }
        return null;
    }
}