<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Model;
use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class CountryMapper extends BaseMapper
{
    CONST TABLE_NAME = 'countries';

    /**
     * @return array|null
     */
    public function fetchAll()
    {
        $dbAdapter = $this->getDbAdapter();

        $select = $this->getDbSql()->select(['ct' => self::TABLE_NAME]);

        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($results) {
            $countries = array();
            foreach ($results as $row) {
                $ct = new Country();
                $ct->exchangeArray((array)$row);
                $countries[] = $ct;
            }
            return $countries;
        }
        return null;
    }

    public function get($id){
    	$dbAdapter = $this->getDbAdapter();
    	$dbSql = $this->getDbSql();

    	$select = $dbSql->select(array('ctr' => self::TABLE_NAME));
    	$select->where(array('ctr.id'=>$id));
    	$query = $dbSql->buildSqlString($select);
    	$results = $dbAdapter->query($query,$dbAdapter::QUERY_MODE_EXECUTE);

    	if($results){
    		$model = new Country();
    		$model->exchangeArray((array)$results->current());
    		return $model;
    	}
    	return null;
    }

    /**
     * @author KienNN
     * @param \Address\Model\Country $item
     */
    public function isExisted($item){
    	if(!$item->getName()){
    		return null;
    	}
    	$select = $this->getDbSql()->select(array('ctr' => self::TABLE_NAME));
    	$select->where(array('name' => $item->getName()));
    	$query = $this->getDbSql()->buildSqlString($select);
    	$results = $this->getDbAdapter()->query($query,Adapter::QUERY_MODE_EXECUTE);
    	if($results->count()){
    		$item->exchangeArray((array) $results->current());
    		return true;
    	}
    	return false;
    }
}