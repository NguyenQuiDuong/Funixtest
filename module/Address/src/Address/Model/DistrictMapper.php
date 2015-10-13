<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class DistrictMapper extends BaseMapper
{
    CONST TABLE_NAME = 'districts';

    /**
     * @param District $district
     * @return array|null
     */
    public function fetchAll($district)
    {
        $dbAdapter = $this->getDbAdapter();

        $select = $this->getDbSql()->select(array("d" => self::TABLE_NAME));
        if ($district->getCityId()) {
            $select->where(array('d.cityId' => $district->getCityId()));
        }

        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($results) {
            $districts = array();
            foreach ($results as $row) {
                $d = new District();
                $d->exchangeArray((array)$row);
                $districts[] = $d;
            }
            return $districts;
        }
        return null;
    }

    /**
     * @param int $id
     * @return District|null
     */
    public function get($id)
    {
        $dbAdapter = $this->getDbAdapter();

        $select = $this->getDbSql()->select(array("d" => self::TABLE_NAME));
        $select->where(array('d.id' => $id));

        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        if ($results) {
            $d = new District();
            $d->exchangeArray((array)$results->current());
            return $d;
        }
        return null;
    }

    /**
     * @author KienNN
     * @param \Address\Model\District $item
     */
    public function isExisted($item){
    	if(!$item->getName() || !$item->getCityId()){
    		return null;
    	}
    	$select = $this->getDbSql()->select(array("d" => self::TABLE_NAME));
    	$select->where(['name' => $item->getName()]);
    	$select->where(['cityId' => $item->getCityId()]);

    	$query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
    	if($results->count()){
    		$item->exchangeArray((array) $results->current());
    		return true;
    	}
    	return false;
    }
}