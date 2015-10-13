<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class CityMapper extends BaseMapper
{
    CONST TABLE_NAME = 'cities';

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
            $cities = array();
            foreach ($results as $row) {
                $ct = new City();
                $ct->exchangeArray((array)$row);
                $cities[] = $ct;
            }
            return $cities;
        }
        return null;
    }

    /**
     * @param int $id
     * @return \Address\Model\City|NULL
     */
    public function get($id)
    {
        $dbAdapter = $this->getDbAdapter();

        $select = $this->getDbSql()->select(['ct' => self::TABLE_NAME]);
        $select->where(array('ct.id' => $id));
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($results) {
            $ct = new City();
            $ct->exchangeArray((array)$results->current());
            return $ct;
        }
        return null;
    }

    /**
     * @author KienNN
     * @param \Address\Model\City $item
     */
    public function isExisted($item){
    	if(!$item->getNativeName()){
    		return null;
    	}
    	$select = $this->getDbSql()->select(['ct' => self::TABLE_NAME]);
    	$select->where(['name' => $item->getNativeName()]);

    	$query = $this->getDbSql()->buildSqlString($select);
    	$results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
    	if($results->count()){
    		$item->exchangeArray((array) $results->current());
    		return true;
    	}
    	return false;
    }
}