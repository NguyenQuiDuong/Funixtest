<?php

namespace Home\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZendX\Paginator\Paginator as PaginatorBase;

abstract class BaseMapper implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\Db\Sql\Select The select object used
     */
    protected $select = null;

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @return \Zend\Db\Sql\Sql
     */
    public function getDbSql()
    {
        return $this->getServiceLocator()->get('dbSql');
    }

    /**
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        return $this->getServiceLocator()->get('dbAdapter');
    }

	/**
	 * @author VanCK
	 * @param \Base\Model\Base $model
	 * @param array $data
	 */
	protected function saveToDb($model, $data)
	{
		if (!($id = $model->getId())) {
			$insert = $this->getDbSql()->insert(static::TABLE_NAME);
			$insert->values($data);
			$query = $this->getDbSql()->buildSqlString($insert);
			$this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
			$model->setId($this->getDbAdapter()->getDriver()->getLastGeneratedValue());
			return $model->getId();
		} else {
			$update = $this->getDbSql()->update(static::TABLE_NAME);
			$update->set($data);
			$update->where(array('id' => (int)$model->getId()));
			$selectString = $this->getDbSql()->buildSqlString($update);
			return $this->getDbAdapter()->query($selectString, Adapter::QUERY_MODE_EXECUTE);
		}
	}

    /**
     * @author VanCK
     * @param array $data
     * @param array|\Home\Model\Bases $obj
     */
    public function updateColumns($data, $obj)
    {
    	if(!$obj instanceof \Home\Model\Base && !is_array($obj)) {
    		throw new \Exception('parameter obj must be instance of Home\Model\Base or an array');
    	}

		$update = $this->getDbSql()->update(static::TABLE_NAME);
		$update->set($data);
		if($obj instanceof \Home\Model\Base) {
			$update->where(array('id' => (int)$obj->getId()));
		} else if(is_array($obj)) {
			$update->where($obj);
		}

		$query = $this->getDbSql()->buildSqlString($update);
		return $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
    }

    /**
     * @author VanCK
     * @param Zend\Db\Sql\Select $select
     * @param array $paging
     * @param Object $objectPrototype
     * @return \Zend\Paginator\Paginator
     */
    protected function preparePaginator($select, $paging, $objectPrototype = null)
    {
    	$resultSetPrototype = null;
    	if($objectPrototype){
    		$resultSetPrototype = new ResultSet();
    		$resultSetPrototype->buffer();
    		$resultSetPrototype->setArrayObjectPrototype($objectPrototype);
    	}
    	$paginatorAdapter = new DbSelect($select, $this->getDbAdapter(), $resultSetPrototype);
        $paginator = new PaginatorBase($paginatorAdapter);

        if(isset($paging['icpp'])) {
        	$paginator->setItemCountPerPage($paging['icpp']);
        }
        if(isset($paging['page'])) {
        	$paginator->setCurrentPageNumber($paging['page']);
        }

        return $paginator;
    }
}