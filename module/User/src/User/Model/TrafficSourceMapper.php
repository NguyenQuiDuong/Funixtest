<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace User\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class TrafficSourceMapper extends BaseMapper
{

    /**
     * @var string
     */
    protected $tableName = 'store_traffic_sources';

    CONST TABLE_NAME = 'store_traffic_sources';

    /**
     * @author by Chautm
     * @param TrafficSource $trafficSource
     * @return null|\Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function save(TrafficSource $trafficSource)
    {
        $data = array(
            'storeId'         => $trafficSource->getStoreId(),
            'type'            => $trafficSource->getType() ? : null,
            'name'            => $trafficSource->getName() ? : null,
            'createdById'     => $trafficSource->getCreatedById() ? : null,
            'createdDateTime' => $trafficSource->getCreatedDateTime() ? : date('Y-m-d H:i:s')
        );
        if ($this->checkExistsTrafficSource($trafficSource)) {
            return null;
        }
        $insert = $this->getDbSql()->insert(self::TABLE_NAME);
        $insert->values($data);
        $query = $this->getDbSql()->buildSqlString($insert);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $trafficSource->setId($this->getDbAdapter()->getDriver()->getLastGeneratedValue());
        return $results;
    }

    /**
     * @author by Chautm
     * @param TrafficSource $trafficSource
     * @return bool
     */
    public function checkExistsTrafficSource(TrafficSource $trafficSource)
    {
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        $select->where(array('name = ?' => $trafficSource->getName(), 'storeId = ?' => $trafficSource->getStoreId()));
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $trafficSource->exchangeArray((array)$results->current());
            return true;
        }
        return false;
    }

}