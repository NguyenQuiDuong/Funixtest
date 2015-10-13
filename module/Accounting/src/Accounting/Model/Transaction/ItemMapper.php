<?php

/**
 * @category       ERP library
 * @copyright      http://erp.nhanh.vn
 * @license        http://erp.nhanh.vn/license
 */
namespace Accounting\Model\Transaction;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;

class ItemMapper extends BaseMapper
{
    CONST TABLE_NAME = 'accounting_transaction_items';

    /**
     *
     * @param \Accounting\Model\Transaction\Item $item
     */
    public function save($item)
    {
        $data = array(
            'transactionId'          =>  $item->getTransactionId(),
            'date'          =>  $item->getDate(),
            'amount'          =>  $item->getAmount(),
            'debitAccountId'          =>  $item->getDebitAccountId()?:null,
            'creditAccountId'          =>  $item->getCreditAccountId()?:null,
            'itemType'          =>  $item->getItemType()?:null,
            'itemId'          =>  $item->getItemId()?:null,
            'description'          =>  $item->getDescription()?:null,
            'status'          =>  $item->getStatus(),
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
     * @author KienNN
     * @param \Accounting\Model\Transaction\Item $item
     */
    public function get($item){
        if(!$item->getId()){
            return null;
        }
        $select = $this->getDbSql()->select(['i' => self::TABLE_NAME]);
        $select->where(['id' => $item->getId()]);
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($rows->count()){
            $item->exchangeArray((array) $rows->current());
            return $item;
        }
        return null;
    }


    /**
     * @author KienNN
     * @param \Accounting\Model\Transaction\Item $item
     */
    public function fetchAll($item){
        $select = $this->getDbSql()->select(['i' => self::TABLE_NAME]);
        if($item->getTransactionId()){
            $select->where(['i.transactionId' => $item->getTransactionId()]);
        }
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        $result = [];
        if($rows->count()){
            foreach ($rows->toArray() as $row){
                $model =  new \Accounting\Model\Transaction\Item();
                $model->exchangeArray($row);

                $result[] = $model;
            }
        }
        return $result;
    }
}