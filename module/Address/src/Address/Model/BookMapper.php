<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Address\Model;

use Home\Model\BaseMapper;

class BookMapper extends BaseMapper
{
    CONST TABLE_NAME = 'address_book';

    /**
     * @param int $id
     * @return \Address\Model\Book $book
     */
    public function get($id)
    {
        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array("address" => self::TABLE_NAME));
        $select->where(array("address.id = ?" => $id));
        $select->join(array('c' => CityMapper::TABLE_NAME), 'address.cityId = c.id', array('cityName' => 'nativeName'));
        $select->join(array('d' => DistrictMapper::TABLE_NAME), 'address.districtId = d.id', array('districtName' => 'name'));

        $query = $dbSql->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $address = new Book();
            $row = (array)$results->current();
            $address->exchangeArray($row);
            return $address;
        }
        return null;
    }

    /**
     * @param \Address\Model\Book $address
     * @return null|array
     */
    public function searchAddress(Book $address)
    {
        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');
        $select = $dbSql->select(array("address" => self::TABLE_NAME));
        if ($address->getCreatedById()) {
            $select->where(array('address.createdById' => $address->getCreatedById()));
        }
        $select->order(array('address.id' => 'DESC'));
        $select->join(array('c' => CityMapper::TABLE_NAME), 'address.cityId = c.id', array('cityName' => 'nativeName'));
        $select->join(array('d' => DistrictMapper::TABLE_NAME), 'address.districtId = d.id', array('districtName' => 'name'));

        $query = $dbSql->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        if ($results->count()) {
            $addresses = array();
            foreach ($results as $result) {
                $address = new Book();
                $row = (array)$result;
                $address->exchangeArray($row);
                $addresses[] = $address;
            }
            return $addresses;
        }
        return null;
    }

    /**
     * @param Book $address
     * @return \Zend\Db\Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     */
    public function saveAddress(Book $address)
    {
        $data = array(
            'name' => $address->getName() ? : null,
            'mobile' => $address->getMobile() ? : null,
            'email' => $address->getEmail() ? : null,
            'cityId' => $address->getCityId() ? : null,
            'districtId' => ((int)$address->getDistrictId()) ? : null,
            'address' => $address->getAddress() ? : null,
            'createdById' => ((int)$address->getCreatedById()) ? : null,
        );
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');

        if (null === ($id = $address->getId())) {
            $insert = $dbSql->insert(self::TABLE_NAME);
            $insert->values($data);
            $query = $dbSql->buildSqlString($insert);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        } else {
            $update = $dbSql->update(self::TABLE_NAME);
            $update->set($data);
            $update->where("id = " . $address->getId());
            $query = $dbSql->buildSqlString($update);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        }
        return $results;
    }


    public function removeAddress($id)
    {
        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        $delete = $dbSql->delete(self::TABLE_NAME);
        $delete->where(array('id' => $id));
        $query = $dbSql->buildSqlString($delete);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

        return $results;
    }
}