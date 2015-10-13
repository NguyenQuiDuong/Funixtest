<?php

/**
 * @category       ERP library
 * @copyright      http://erp.nhanh.vn
 * @license        http://erp.nhanh.vn/license
 */
namespace Accounting\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class AccountMapper extends BaseMapper
{

    CONST TABLE_NAME = 'accounting_accounts';

    /**
     *
     * @param \Accounting\Model\Account $item
     */
    public function save($item)
    {
        $data = array(
            'parentId'          =>  $item->getParentId() ? : null,
            'companyId'         =>  $item->getCompanyId() ? : null,
            'type'              =>  $item->getType(),
            'code'              =>  $item->getCode(),
            'name'              =>  $item->getName(),
            'status'            =>  $item->getStatus(),
            'remain'            =>  $item->getRemain(),
            'createdById'       =>  $item->getCreatedById(),
            'createdDateTime' => $item->getCreatedDateTime(),

        );
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');
        if (! $item->getId()) {
            $insert = $this->getDbSql()->insert(self::TABLE_NAME);
            $insert->values($data);
            $query = $dbSql->buildSqlString($insert);

            /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
            $item->setId($results->getGeneratedValue());
        } else {
            $update = $this->getDbSql()->update(self::TABLE_NAME);
            $update->set($data);
            $update->where([
                'id' => (int) $item->getId()
            ]);
            $query = $dbSql->buildSqlString($update);
            $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        }
        return $results;
    }



    /**
     *
     * @author Hungpx
     * @param \Accounting\Model\Account $item
     */
    public function fetchAll($item)
    {
        $select = $this->getDbSql()->select(array(
            'c' => self::TABLE_NAME
        ));
        if ($item->getId()) {
            $select->where([
                'c.id' => $item->getId()
            ]);
        } elseif ($item->getOption('ids')){
            $select->where([
                'c.id' => $item->getOption('ids')
            ]);
        }
        if ($item->getCompanyId()) {
            $select->where([
                'c.companyId' => $item->getCompanyId()
            ]);
        }
        if($item->getOption('companyId')){
            $select->where([
                'c.companyId' => $item->getOption('companyId')
                ]);
        }
        if ($item->getName()) {
            $select->where([
                'c.name LIKE ?' => '%' . $item->getName() . '%'
            ]);
        }
        if($item->getOption('sort')){
            $select->order([implode(' ',$item->getOption('sort'))]);
        } else {
            $select->order([ 'c.id' => 'DESC']);
        }


        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $paginator = [];
        $userIds = [];
        $companyIds = [];
        $accountIds = [];
        if ($results->count()) {
            foreach ($results as $row) {
                $row = (array) $row;
                $account = new Account();
                $account->exchangeArray($row);
                $paginator[] = $account;
                if($account->getCreatedById()){
                    $userIds[$account->getCreatedById()] = $account->getCreatedById();
                }
                if($account->getCompanyId()){
                    $companyIds[$account->getCompanyId()] = $account->getCompanyId();
                }
                $accountIds[$account->getId()] = $account->getId();
            }
        }
        if($item->getOption('fetchOnlyIds')){
            return $accountIds;
        }
        $userNames = [];
        $companyNames = [];
        // get User's name
        if (count($userIds) && $item->getOption('loadUser')) {
            $select = $this->getDbSql()->select([
                'c' => \User\Model\UserMapper::TABLE_NAME
            ], [
                'id',
                'fullName'
            ]);
            $select->where([
                'c.id' => $userIds
            ]);
            $query = $this->getDbSql()->buildSqlString($select);
            $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
            unset($select);
            if ($results) {
                $resultArr = $results->toArray();
                foreach ($resultArr as $result) {
                    $userNames[$result['id']] = $result['fullName'];
                }
            }
        }

        // Get Company
        if (count($companyIds) && $item->getOption('loadCompany')) {
            $select = $this->getDbSql()->select([
                'cp' => \Company\Model\CompanyMapper::TABLE_NAME
            ], [
                'id',
                'name'
            ]);
            $select->where([
                'cp.id' => $companyIds
            ]);
            $query = $this->getDbSql()->buildSqlString($select);
            $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
            unset($select);
            if ($results) {
                $resultArr = $results->toArray();
                foreach ($resultArr as $result) {
                    $companyNames[$result['id']] = $result['name'];
                }
            }
        }

        foreach ($paginator as $account) {
            $userId = $account->getCreatedById();
            $companyId = $account->getCompanyId();
            $itemId = $account->getId();
            $account->addOption('userName', isset($userNames[$userId]) ? $userNames[$userId] : null);
            $account->addOption('companyName', isset($companyNames[$companyId]) ? $companyNames[$companyId] : null);
        }
        return $paginator;
    }

    /**
     *
     * @author Hungpx
     * @param \Accounting\Model\Account $item
     */
    public function delete($item)
    {
        if (! $item->getId()) {
            return false;
        }
        /* @var $dbAdapter \Zend\Db\Adapter\Adapter */
        $dbAdapter = $this->getServiceLocator()->get('dbAdapter');

        /* @var $dbSql \Zend\Db\Sql\Sql */
        $dbSql = $this->getServiceLocator()->get('dbSql');

        $delete = $this->getDbSql()->delete(self::TABLE_NAME);
        $delete->where([
            'id' => $item->getId()
        ]);
        $query = $dbSql->buildSqlString($delete);
        /* @var $results \Zend\Db\Adapter\Driver\Pdo\Result */
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        return $results;
    }

    public function get($item)
    {
        if (! $item->getId()) {
            return null;
        }
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        if ($item->getId()) {
            $select->where([
                'id' => $item->getId()
            ]);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);

        if ($results->count()) {
            $item->exchangeArray((array) $results->current());
            // search task of

            return $item;
        }

        return null;
    }

    /**
     *
     * @param \Accounting\Model\Account $item
     */
    public function isExisted($item){
        if(!$item->getCode() || !$item->getCompanyId()){
            return null;
        }
        $select = $this->getDbSql()->select(self::TABLE_NAME);
        $select->where([
            'code' => $item->getCode(),
            'companyId' => $item->getCompanyId()
        ]);
        if ($item->getId()) {
            $select->where(['id != ?' => $item->getId()]);
        }
        if($item->getParentId()){
            $select->where(['parentId' => $item->getParentId()]);
        } else {
            $select->where(['parentId IS NULL']);
        }
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $results = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);

        if ($results->count()) {
            $item->exchangeArray((array) $results->current());
            return true;
        }
        return false;
    }
    /**
     * @author KienNN
     * @param \Accounting\Model\Account $item
     * @param array $parentIds
     * @return NULL|Ambigous <multitype:unknown , NULL>
     */
    public function recusiveBranchChildIds($item, $parentIds){
        if(!$parentIds || !count($parentIds)){
            return null;
        }
        $result = [];
        foreach ($parentIds as $parentId){
            $result[$parentId] = $parentId;
        }
        $select = $this->getDbSql()->select(['aa' => self::TABLE_NAME]);
        $select->where(['parentId' => $parentIds]);
        if($item->getCompanyId()){
            $select->where(['companyId' => $item->getCompanyId()]);
        }
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $childIds = [];
        if($rows->count()){
            foreach ($rows as $row){
                $childIds[$row['id']] = $row['id'];
            }
        }
        if(count($childIds)){
            $result += $this->recusiveBranchChildIds($item, $childIds);
        }
        return $result;
    }

    public function suggest($item){

        $dbAdapter = $this->getDbAdapter();
        $item->prepairSuggest();
        $select = $this->getDbSql()->select( self::TABLE_NAME);
        $select->where([
            '(name LIKE ?)' =>
            ['%'.$item->getName().'%']
            ]);

        $query = $this->getDbSql()->buildSqlString($select);
        $results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);
        $result = array();
        if ($results->count()) {
            $row = array();
            foreach ($results as $value) {
                $row['id'] = $value['id'];
                $row['name'] = $value['name'];
                $row['label'] = $value['name'];
                $result[] = $row;
            }

        }
        return $result;
    }
}