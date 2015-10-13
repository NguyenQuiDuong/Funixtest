<?php

/**
 * @category       ERP library
 * @copyright      http://erp.nhanh.vn
 * @license        http://erp.nhanh.vn/license
 */
namespace Accounting\Model;

use Home\Model\BaseMapper;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;

class TransactionMapper extends BaseMapper
{
    CONST TABLE_NAME = 'accounting_transactions';

    /**
     *
     * @param \Accounting\Model\Transaction $item
     */
    public function save($item)
    {
        $data = array(
            'companyId'          =>  $item->getCompanyId(),
            'type'               =>  $item->getType(),
            'applyDate'          =>  $item->getApplyDate()?:null,
            'amount'             =>  $item->getAmount()?:null,
            'description'        =>  $item->getDescription()?:null,
            'status'             =>  $item->getStatus(),
            'createdDate'        =>  $item->getCreatedDate(),
            'createdById'        =>  $item->getCreatedById(),
            'createdTime'        =>  $item->getCreatedTime(),
            'approvedById'       =>  $item->getApprovedById()?:null,
            'approvedDateTime'   =>  $item->getApprovedDateTime()?:null,
            'accountingById'     =>  $item->getAccountingById()?:null,
            'accountingDateTime' =>  $item->getAccountingDateTime()?:null,
            'paymentById'        =>  $item->getPaymentById()?:null,
            'paymentDateTime'    =>  $item->getPaymentDateTime()?:null,
            'itemType'           =>  $item->getItemType()?:null,
            'itemId'             =>  $item->getItemId()?:null,
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
     * @param \Accounting\Model\Transaction $item
     */
    public function get($item)
    {
        if(!$item->getId()){
            return null;
        }
        $select = $this->getDbSql()->select(['t' => self::TABLE_NAME]);
        $select->where(['id' => $item->getId()]);
        $select->limit(1);
        $query = $this->getDbSql()->buildSqlString($select);
        $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
        if($rows->count()){
            $item->exchangeArray((array) $rows->current());

            if($item->getOption('loadTransactionItems')){
                $select = $this->getDbSql()->select(['ti' => \Accounting\Model\Transaction\ItemMapper::TABLE_NAME]);
                $select->where(['transactionId' => $item->getId()]);
                $query = $this->getDbSql()->buildSqlString($select);
                $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
                $transactionItems = [];
                if($rows->count()){
                    foreach ($rows->toArray() as $row){
                        $transactionItems[] = new \Accounting\Model\Transaction\Item($row);
                    }
                }
                $item->addOption('items', $transactionItems);
            }

            return $item;
        }
        return null;
    }

    /**
     * @author KienNN
     * @param \Accounting\Model\Transaction $item
     * @param unknown $options
     */
    public function search($item, $options)
    {
        $select = $this->getDbSql()->select(['t' => self::TABLE_NAME]);
        if($item->getCompanyId()){
            $select->where(['t.companyId' => $item->getCompanyId()]);
        }
        if ($item->getOption('companyIds')){
            $select->where(['t.companyId' => $item->getOption('companyIds')]);
        }
        if($item->getId()){
            $select->where(['t.id' => $item->getId()]);
        }
        if($item->getStatus()){
            $select->where(['t.status' => $item->getStatus()]);
        }
        if($item->getType()){
            $select->where(['t.type' => $item->getType()]);
        }
        if($item->getCreatedById()){
            $select->where(['t.createdById' => $item->getCreatedById()]);
        }
        if($item->getOption('fromApplyDate')){
            $select->where(['t.applyDate >= ?' => $item->getOption('fromApplyDate')]);
        }
        if($item->getOption('toApplyDate')){
            $select->where(['t.applyDate <= ?' => $item->getOption('toApplyDate')]);
        }
        $select->order(['t.applyDate DESC', 't.id DESC']);
        $paginator = $this->preparePaginator($select, $options, new \Accounting\Model\Transaction());
        $ids = [];
        $userIds = [];
        $companyIds = [];
        if($paginator->count()){
            foreach ($paginator as $transaction){
                /*@var $transaction \Accounting\Model\Transaction */
                $ids[$transaction->getId()] = $transaction->getId();
                $userIds[$transaction->getCreatedById()] = $transaction->getCreatedById();
                if($transaction->getApprovedById()){
                    $userIds[$transaction->getApprovedById()] = $transaction->getApprovedById();
                }
                if($transaction->getAccountingById()){
                    $userIds[$transaction->getAccountingById()] = $transaction->getAccountingById();
                }
                if($transaction->getPaymentById()){
                    $userIds[$transaction->getPaymentById()] = $transaction->getPaymentById();
                }
                $companyIds[$transaction->getCompanyId()] = $transaction->getCompanyId();
            }
        }
        $valueBeforeTaxs = [];
        $totalItems = [];
        if(count($ids)){
            $select = $this->getDbSql()->select(['ti' => \Accounting\Model\Transaction\ItemMapper::TABLE_NAME]);
            $select->columns(array(
            	'transactionId' => 'transactionId',
                'totalItem' => new Expression('COUNT(id)'),
                'valueBeforeTax' => new Expression('SUM(amount)')
            ));
            $select->where(['transactionId' => $ids]);
            $select->group(['transactionId']);
            $query = $this->getDbSql()->buildSqlString($select);
			$rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
			if($rows->count()){
			    foreach ($rows as $row){
			        $row = (array) $row;
			        $valueBeforeTaxs[$row['transactionId']] = $row['valueBeforeTax'];
			        $totalItems[$row['transactionId']] = $row['totalItem'];
			    }
			}
        }
        $users = [];
        if(count($userIds)){
            $select = $this->getDbSql()->select(['u' => \User\Model\UserMapper::TABLE_NAME]);
            $select->where(['id' => $userIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows as $row){
                    $row = (array) $row;
                    $user = new \User\Model\User();
                    $user->exchangeArray($row);
                    $users[$user->getId()] = $user;
                }
            }
        }
        $companies = [];
        if(count($companyIds)){
            $select = $this->getDbSql()->select(['c' => \Company\Model\CompanyMapper::TABLE_NAME]);
            $select->where(['id' => $companyIds]);
            $query = $this->getDbSql()->buildSqlString($select);
            $rows = $this->getDbAdapter()->query($query, Adapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows as $row){
                    $row = (array) $row;
                    $companies[$row['id']] = $row['name'];
                }
            }
        }
        if($paginator->getCurrentModels()){
            foreach ($paginator->getCurrentModels() as $transaction){
                if(isset($totalItems[$transaction->getId()])){
                    $transaction->addOption('totalItems', $totalItems[$transaction->getId()]);
                }
                if(isset($valueBeforeTaxs[$transaction->getId()])){
                    $transaction->addOption('valueBeforeTax', $valueBeforeTaxs[$transaction->getId()]);
                }
                if(isset($users[$transaction->getCreatedById()])){
                    $transaction->addOption('createdBy', $users[$transaction->getCreatedById()]);
                }
                if(isset($companies[$transaction->getCompanyId()])){
                    $transaction->addOption('companyName', $companies[$transaction->getCompanyId()]);
                }
                if($transaction->getApprovedById() && isset($users[$transaction->getApprovedById()])){
                    $transaction->addOption('approvedBy', $users[$transaction->getApprovedById()]);
                }
                if($transaction->getAccountingById() && isset($users[$transaction->getAccountingById()])){
                    $transaction->addOption('accountingBy', $users[$transaction->getAccountingById()]);
                }
                if($transaction->getPaymentById() && isset($users[$transaction->getPaymentById()])){
                    $transaction->addOption('paymentBy', $users[$transaction->getPaymentById()]);
                }
            }
        }
        return $paginator;
    }

    /**
     * @author KienNN
     * @param \Accounting\Model\Transaction $item
     * @param unknown $options
     */
    public function searchCrmContract($item, $options){
        $select = $this->getDbSql()->select(['t' => self::TABLE_NAME]);
        $select->join(['c' => \Crm\Model\ContractMapper::TABLE_NAME], 't.itemId=c.id', []);
        $select->where(['t.itemType' => \Accounting\Model\Transaction::ITEM_TYPE_CRM_CONTRACT]);
        if($item->getCompanyId()){
            $select->where(['t.companyId' => $item->getCompanyId()]);
        }
        if ($item->getOption('companyIds')){
            $select->where(['t.companyId' => $item->getOption('companyIds')]);
        }
        if($item->getOption('userIds')){
            $select->join(['lu' => \Crm\Model\Lead\UserMapper::TABLE_NAME], new Expression('lu.accountId=c.accountId OR lu.leadId=c.leadId'), []);
            $select->where(['lu.userId' =>  $item->getOption('userIds')]);
        }
        if($item->getId()){
            $select->where(['t.id' => $item->getId()]);
        }
        if($item->getStatus()){
            $select->where(['t.status' => $item->getStatus()]);
        }
        if($item->getType()){
            $select->where(['t.type' => $item->getType()]);
        }
        if($item->getCreatedById()){
            $select->where(['t.createdById' => $item->getCreatedById()]);
        }
        if($item->getOption('departmentId')){
            $select->where(['c.departmentId' => $item->getOption('departmentId')]);
        }
        if($item->getOption('contractId')){
            $select->where(['t.itemId' => $item->getOption('contractId')]);
        }
        if($item->getOption('productId')){
            $select->join(['cp' => \Crm\Model\Contract\ProductMapper::TABLE_NAME], 'cp.contractId=t.itemId', []);
            $select->where(['cp.productId' => $item->getOption('productId')]);
        }
        if($item->getOption('accountingType')){
            $select->join(['i' => \Accounting\Model\Transaction\ItemMapper::TABLE_NAME], 'i.transactionId=t.id', []);
            $condition = new Expression('i.creditAccountId=ac.id OR i.debitAccountId=ac.id');
            $select->join(['ac' => \Accounting\Model\AccountMapper::TABLE_NAME], $condition, []);
            $select->where(['ac.type' => $item->getOption('accountingType')]);
        }
        if($item->getOption('accountId')){
            $select->where(['c.accountId' => $item->getOption('accountId')]);
        }
        if($item->getOption('leadId')){
            $select->where(['c.leadId' => $item->getOption('leadId')]);
        }

        if($item->getOption('fromApplyDate')){
            $select->where(['t.applyDate >= ?' => $item->getOption('fromApplyDate')]);
            $select->where(['t.accountingById IS NOT NULL']);
        }
        if($item->getOption('toApplyDate')){
            $select->where(['t.applyDate <= ?' => $item->getOption('toApplyDate')]);
            $select->where(['t.accountingById IS NOT NULL']);
        }
        if($item->getOption('fromCreatedDate')){
            $select->where(['t.createdDate >= ?' => $item->getOption('fromCreatedDate')]);
        }
        if($item->getOption('toCreatedDate')){
            $select->where(['t.createdDate <= ?' => $item->getOption('toCreatedDate')]);
        }
        if($item->getCreatedById()){
            $select->where(['t.createdById' => $item->getCreatedById()]);
        }
        if($item->getAccountingById()){
            $select->where(['t.accountingById' => $item->getAccountingById()]);
        }
        if($item->getOption('commisstionToEmployeeId')){
            $select->join(['com' => \Crm\Model\Contract\CommissionMapper::TABLE_NAME], 't.itemId=com.contractId', []);
            $select->where(['com.employeeId' => $item->getOption('commisstionToEmployeeId')]);
        }
        $select->group(['t.id']);
        $select->order([new Expression('IFNULL(t.applyDate, t.createdDate) DESC'), 't.id DESC']);
        $paginator = $this->preparePaginator($select, $options, new \Accounting\Model\Transaction());

        $contractIds = [];
        $userIds = [];
        $transactionIds = [];

        foreach ($paginator as $transaction){
            if($transaction->getItemId()){
                $contractIds[$transaction->getItemId()] = $transaction->getItemId();
            }
            if($transaction->getCreatedById()){
                $userIds[$transaction->getCreatedById()] = $transaction->getCreatedById();
            }
            if($transaction->getApprovedById()){
                $userIds[$transaction->getApprovedById()] = $transaction->getApprovedById();
            }
            if($transaction->getAccountingById()){
                $userIds[$transaction->getAccountingById()] = $transaction->getAccountingById();
            }
            if($transaction->getPaymentById()){
                $userIds[$transaction->getPaymentById()] = $transaction->getPaymentById();
            }
            $transactionIds[$transaction->getId()] = $transaction->getId();
        }
        $crmAccounts = [];
        $crmLeads = [];
        $contractValues = [];
        $contractPaids = [];
        if(count($contractIds)){
            if($item->getOption('loadCustomer')){
                $select = $this->getDbSql()->select(['a' => \Crm\Model\AccountMapper::TABLE_NAME]);
                $select->join(['c' => \Crm\Model\ContractMapper::TABLE_NAME], 'c.accountId=a.id', ['contractId' => 'id']);
                $select->where(['c.id' => $contractIds]);
                $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                if($rows->count()){
                    foreach ($rows->toArray() as $row){
                        $account = new \Crm\Model\Account();
                        $account->exchangeArray($row);
                        $crmAccounts[$row['contractId']] = $account;
                    }
                }

                $select = $this->getDbSql()->select(['l' => \Crm\Model\LeadMapper::TABLE_NAME]);
                $select->join(['c' => \Crm\Model\ContractMapper::TABLE_NAME], 'c.leadId=l.id', ['contractId' => 'id']);
                $select->where(['c.id' => $contractIds]);
                $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                if($rows->count()){
                    foreach ($rows->toArray() as $row){
                        $lead = new \Crm\Model\Lead();
                        $lead->exchangeArray($row);
                        $crmLeads[$row['contractId']] = $lead;
                    }
                }
            }
            if($item->getOption('loadContractValue')){
                $select = $this->getDbSql()->select(['p' => \Crm\Model\Contract\ProductMapper::TABLE_NAME]);
                $select->where(['contractId' => $contractIds]);
                $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                if($rows->count()){
                    foreach ($rows->toArray() as $row){
                        $product = new \Crm\Model\Contract\Product($row);
                        $value = $product->calculateValue();
                        if(isset($contractValues[$product->getContractId()])){
                            $contractValues[$product->getContractId()] += $value;
                        } else {
                            $contractValues[$product->getContractId()] = $value;
                        }

                    }
                }
            }
            if($item->getOption('loadContractPaid')){
                $select = $this->getDbSql()->select(['t' => self::TABLE_NAME]);
                $select->where(['itemType' => \Accounting\Model\Transaction::ITEM_TYPE_CRM_CONTRACT]);
                $select->where(['itemId' => $contractIds]);
                $select->where(['status' => [\Accounting\Model\Transaction::STATUS_ACCOUNTING, \Accounting\Model\Transaction::STATUS_PAYMENT]]);
                $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                if($rows->count()){
                    foreach ($rows->toArray() as $row){
                        if(isset($contractPaids[$row['itemId']])){
                            $contractPaids[$row['itemId']] += $row['amount'];
                        } else {
                            $contractPaids[$row['itemId']] = $row['amount'];
                        }

                    }
                }
            }
        }
        $accountingAccounts = [];
        if($item->getOption('loadAccountingAccount') && count($transactionIds)){
            $select = $this->getDbSql()->select(['a' => \Accounting\Model\AccountMapper::TABLE_NAME]);
            $select->join(['i' => \Accounting\Model\Transaction\ItemMapper::TABLE_NAME], new Expression(
            	'i.creditAccountId=a.id OR i.debitAccountId=a.id'), ['transactionId' => 'transactionId']
            );
            $select->where(['i.transactionId' => $transactionIds]);
            $select->group(['i.transactionId']);
            $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows->toArray() as $row){
                   $accountingAccount = new \Accounting\Model\Account();
                   $accountingAccount->exchangeArray($row);
                   $accountingAccounts[$row['transactionId']] = $accountingAccount;
                }
            }
        }
        $users = [];
        if($item->getOption('loadUsers') && count($userIds)){
            $select = $this->getDbSql()->select(['u' => \User\Model\UserMapper::TABLE_NAME]);
            $select->where(['id' => $userIds]);
            $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
            if($rows->count()){
                foreach ($rows->toArray() as $row){
                    $user = new \User\Model\User();
                    $user->exchangeArray($row);
                    $users[$user->getId()] = $user;
                }
            }
        }
        if($paginator->getCurrentModels()){
            foreach ($paginator->getCurrentModels() as $transaction){
                if($transaction->getItemId() && isset($crmAccounts[$transaction->getItemId()])){
                    $transaction->addOption('crmAccount', $crmAccounts[$transaction->getItemId()]);
                }
                if($transaction->getItemId() && isset($crmLeads[$transaction->getItemId()])){
                    $transaction->addOption('crmLead', $crmLeads[$transaction->getItemId()]);
                }
                if(isset($accountingAccounts[$transaction->getId()])){
                    $transaction->addOption('accountingAccount', $accountingAccounts[$transaction->getId()]);
                }
                if($transaction->getCreatedById() && isset($users[$transaction->getCreatedById()])){
                    $transaction->addOption('createdBy', $users[$transaction->getCreatedById()]);
                }
                if($transaction->getAccountingById() && isset($users[$transaction->getAccountingById()])){
                    $transaction->addOption('accountingBy', $users[$transaction->getAccountingById()]);
                }
                if($transaction->getPaymentById() && isset($users[$transaction->getPaymentById()])){
                    $transaction->addOption('paymentBy', $users[$transaction->getCreatedById()]);
                }
                if(isset($contractValues[$transaction->getItemId()])){
                    $transaction->addOption('contractValue', $contractValues[$transaction->getItemId()]);
                }
                if(isset($contractPaids[$transaction->getItemId()])){
                    $transaction->addOption('contractPaid', $contractPaids[$transaction->getItemId()]);
                }
            }
        }
        return $paginator;
    }

    /**
     * @author KienNN
     * @param \Accounting\Model\Transaction $item
     * Từ transaction tìm lại hợp đồng
     * Tính toán tỉ lệ ăn chia với từng nhân viên [employeeId => amount]
     *     + nếu đã tồn tại payment (salemanId + transactionId) thì update lại thông tin cho payment
	 *     + nếu không thì tạo mới payment
	 *     Xóa đi những payment ko dc tính
     */
    public function recalculatePayment($item){
        if(!$item->getId() || !$item->getAmount() || !$item->getItemId()
        || $item->getItemType() != \Accounting\Model\Transaction::ITEM_TYPE_CRM_CONTRACT
        || !in_array($item->getStatus(), [\Accounting\Model\Transaction::STATUS_ACCOUNTING, \Accounting\Model\Transaction::STATUS_PAYMENT])){
            return null;
        }

        // lấy ra các sản phẩm trong hợp đồng
        $product = new \Crm\Model\Contract\Product();
        $product->setContractId($item->getItemId());
        $productMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\ProductMapper');
        $products = $productMapper->fetchAll($product);
        // lấy commission
        $commission = new \Crm\Model\Contract\Commission();
        $commission->setContractId($item->getItemId());
        $commissionMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\CommissionMapper');
        $commissions = $commissionMapper->fetchAll($commission);

        // lấy ra các payment cũ của transaction
        $select = $this->getDbSql()->select(['p' => \Crm\Model\Contract\PaymentMapper::TABLE_NAME]);
        $select->join(['e' => \Hrm\Model\EmployeeMapper::TABLE_NAME], 'e.userId=p.salemanId',
            ['employeeId' => 'id']);
        $select->where(['p.transactionId' => $item->getId()]);
        $rowPays = $this->getDbAdapter()->query(
            $this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
        $paymentToDeletes =[];
        if($rowPays->count()){
            foreach ($rowPays->toArray() as $rowPay){
                $paymentToDeletes[$rowPay['employeeId']] = new \Crm\Model\Contract\Payment($rowPay);
            }
        }
        // lấy ra tài khoản kế toán của phiếu thu
        $select = $this->getDbSql()->select(['ti' => \Accounting\Model\Transaction\ItemMapper::TABLE_NAME]);
        $select->where(['ti.transactionId' => $item->getId()]);
        $select->limit(1);
        $row = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
        $row = (array) $row->current();
        $accountingAccountId = null;
        if($row['debitAccountId']){
            $accountingAccountId = $row['debitAccountId'];
        } elseif ($row['creditAccountId']){
            $accountingAccountId = $row['creditAccountId'];
        }
        $accountingAccount = new \Accounting\Model\Account();
        $accountingAccount->setId($accountingAccountId);
        $accountingAccountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        $accountingAccountMapper->get($accountingAccount);

        // tính toán ra số tiền cho từng nhân viên
        $paymentAmounts = \Crm\Model\Contract\Payment::breakToCommission($item->getAmount(), $products, $commissions);

        $employeeMapper = $this->getServiceLocator()->get('\Hrm\Model\EmployeeMapper');
        $paymentMapper = $this->getServiceLocator()->get('\Crm\Model\Contract\PaymentMapper');
        foreach ($paymentAmounts as $employeeId => $amount){
            // lấy ra user của employee
            $employee = new \Hrm\Model\Employee();
            $employee->setId($employeeId);
            $employeeMapper->get($employee);
            if($employee->getUserId()){
                $payment = new \Crm\Model\Contract\Payment();
                $payment->setTransactionId($item->getId());
                $payment->setSalemanId($employee->getUserId());
                if($paymentMapper->isExistedInTransaction($payment)){
                    /** nếu payment đã tồn tại chỉ update lại amount */
                    $payment->setAmount($amount);
                    $paymentMapper->save($payment);

                    unset($paymentToDeletes[$employeeId]);
                } else {
                    /** nếu payment chưa tồn tại thì tạo mới */
                    $payment->setAmount($amount);
                    $payment->setAccountingAccountId($accountingAccountId);
                    $payment->setDescription($item->getDescription());
                    $payment->setCompanyId($employee->getCompanyId());
                    $payment->setDepartmentId($employee->getDepartmentId());
                    $payment->setContractId($item->getItemId());
                    if($accountingAccount->getType() == \Accounting\Model\Account::TYPE_CASH){
                        $payment->setType(\Crm\Model\Contract\Payment::TYPE_CASH);
                    } else {
                        $payment->setType(\Crm\Model\Contract\Payment::TYPE_MONEY_TRANSFER);
                    }
                    $payment->setStatus(\Crm\Model\Contract\Payment::STATUS_CHECKED);
                    $payment->setCheckedById($item->getAccountingById());
                    $payment->setCheckedDate($item->getApplyDate());
                    $payment->setCheckedDateTime($item->getAccountingDateTime());
                    $payment->setCreatedById($item->getAccountingById());
                    $payment->setCreatedDateTime($item->getAccountingDateTime());
                    $paymentMapper->save($payment);
                }
            }
        }
        /** Xóa các payment thừa */
        if(count($paymentToDeletes)){
            foreach ($paymentToDeletes as $payment){
                $delete = $this->getDbSql()->delete(\Crm\Model\Contract\PaymentMapper::TABLE_NAME);
                $delete->where(['id' => $payment->getId()]);
                $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($delete), Adapter::QUERY_MODE_EXECUTE);
            }
        }

    }

    /**
     * @author KienNN
     * @param \Accounting\Model\Transaction $item
     */
    public function fetchAll($item){
        $select = $this->getDbSql()->select(['t' => self::TABLE_NAME]);
        if($item->getItemId()){
            $select->where(['t.itemId' => $item->getItemId()]);
        }
        if($item->getItemType()){
            $select->where(['t.itemType' => $item->getItemType()]);
        }
        $select->order(['t.id DESC']);
        $rows = $this->getDbAdapter()->query($this->getDbSql()->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
        $result = [];
        if($rows->count()){
            foreach ($rows->toArray() as $row){
                $model = new \Accounting\Model\Transaction();
                $model->exchangeArray($row);
                $result[] = $model;
            }
        }
        return $result;
        // Nếu muốn load cái j ra nữa thì nhớ check option, vì cái này dùng chung nhiều, load nhiều nặng lắm
    }
}