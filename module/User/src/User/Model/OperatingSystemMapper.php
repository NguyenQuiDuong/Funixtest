<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace User\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class OperatingSystemMapper extends BaseMapper
{

    /**
     * @var string
     */
    protected $tableName = 'operating_systems';

    CONST TABLE_NAME = 'operating_systems';

    /**
     * @param OperatingSystem $operatingSystem
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function save(OperatingSystem $operatingSystem)
    {
        $data = array(
            'name'            => $operatingSystem->getName() ? : null,
            'createdDateTime' => $operatingSystem->getCreatedDateTime() ? : date('Y-m-d H:i:s')
        );
        if ($this->checkExistsOS($operatingSystem)) {
            return null;
        }
        $insert = $this->getDbSql()->insert(self::TABLE_NAME);
        $insert->values($data);
        $query = $this->getDbSql()->buildSqlString($insert);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $operatingSystem->setId($this->getDbAdapter()->getDriver()->getLastGeneratedValue());
        return $results;
    }

    /**
     * @param OperatingSystem $operatingSystem
     * @return bool
     */
    public function checkExistsOS(OperatingSystem $operatingSystem)
    {
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        $select->where(array('name = ?' => $operatingSystem->getName()));
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $operatingSystem->exchangeArray((array)$results->current());
            return true;
        }
        return false;
    }

}