<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace User\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class BrowserMapper extends BaseMapper
{

    /**
     * @var string
     */
    protected $tableName = 'browsers';

    CONST TABLE_NAME = 'browsers';

    /**
     * @param Browser $browser
     * @return null|\Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function save(Browser $browser)
    {
        $data = array(
            'name'            => $browser->getName() ? : null,
            'createdDateTime' => $browser->getCreatedDateTime() ? : date('Y-m-d H:i:s')
        );
        if ($this->checkExistsBrowser($browser)) {
            return null;
        }
        $insert = $this->getDbSql()->insert(self::TABLE_NAME);
        $insert->values($data);
        $query = $this->getDbSql()->buildSqlString($insert);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $browser->setId($this->getDbAdapter()->getDriver()->getLastGeneratedValue());
        return $results;
    }

    /**
     * @param Browser $browser
     * @return bool
     */
    public function checkExistsBrowser(Browser $browser)
    {
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        $select->where(array('name = ?' => $browser->getName()));
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $browser->exchangeArray((array)$results->current());
            return true;
        }
        return false;
    }

}