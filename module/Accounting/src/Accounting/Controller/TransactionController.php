<?php
/**
 * Accounting\Controller
 *
 * @category    ERP library
 * @copyright    http://erp.nhanh.vn
 * @license        http://erp.nhanh.vn/license
 */
namespace Accounting\Controller;

use Home\Controller\ControllerBase;
use Home\Model\DateBase;

class TransactionController extends ControllerBase
{
    public function indexAction()
    {
        $fFilter = new \Accounting\Form\Transaction\Filter($this->getServiceLocator());
        $this->getViewModel()->setVariable('form', $fFilter);
        $fFilter->setData($this->getRequest()->getQuery());
        if($fFilter->isValid()){
            $formData = $fFilter->getData();
            $transaction = new \Accounting\Model\Transaction();
            $transaction->exchangeArray($formData);
            $transaction->setOptions($formData);
            $transaction->addOption('companyIds', $this->company()->getManageabaleIds());
            $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
            $this->getViewModel()->setVariable('paginator',
                $transactionMapper->search($transaction, $this->getPagingParams(0, 30)));

        }
        $this->getViewModel()->setVariable('loginUserId', $this->user()->getIdentity());
        return $this->getViewModel();
    }

    /**
     * Lập phiếu xin thu
     */
    public function addreqrecieveAction(){
        $form = new \Accounting\Form\Transaction\ReqRecieve($this->getServiceLocator());
        $this->getViewModel()->setVariable('form', $form);
        $companyId = $this->getRequest()->getPost('companyId');
        if(!$this->user()->getUser()->isAdmin())
        {
            $companyId = $this->user()->getCompanyId();
        }
            $expenseCategory = new \Accounting\Model\ExpenseCategory();
            $expenseCategory->setCompanyId($companyId);
            $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
            $tree = new \Home\Model\Tree();
            $categoryValueOptions = $tree->toSelectBoxArray($expenseCategoryMapper->fetchAll($expenseCategory));
            $this->getViewModel()->setVariable('categoryValueOptions', $categoryValueOptions);
        
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $transaction = new \Accounting\Model\Transaction();
                $transaction->exchangeArray($formData);
                $transaction->setType(\Accounting\Model\Transaction::TYPE_RECIEVE);
                $transaction->setCreatedDate(DateBase::getCurrentDate());
                $transaction->setCreatedDateTime(DateBase::getCurrentDateTime());
                $transaction->setCreatedById($this->user()->getIdentity());
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_NEW);

                $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
                $transactionMapper->save($transaction);

                $itemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
                if(isset($formData['itemData']) && count($formData['itemData'])){
                    foreach ($formData['itemData'] as $itemData){
                        $item = new \Accounting\Model\Transaction\Item();
                        $item->exchangeArray($itemData);
                        $item->setTransactionId($transaction->getId());
                        $item->setStatus($transaction->getStatus());
                        $item->setTransactionDate($transaction->getApplyDate());
                        $itemMapper->save($item);
                    }
                }

                if($form->get('afterSubmit')->getValue()){
                    return $this->redirect()->toUrl($form->get('afterSubmit')->getValue());
                }
                return $this->redirect()->toUrl('/accounting/transaction/index');
            }
        }
        return $this->getViewModel();
    }


    /**
     * Lập phiếu xin chi
     */
    public function addreqpaymentAction(){
        $form = new \Accounting\Form\Transaction\ReqRecieve($this->getServiceLocator());
        $this->getViewModel()->setVariable('form', $form);

        $companyId = $this->getRequest()->getPost('companyId');
        if(!$this->user()->getUser()->isAdmin()){
            $companyId = $this->user()->getCompanyId();
        }
        
            $expenseCategory = new \Accounting\Model\ExpenseCategory();
            $expenseCategory->setCompanyId($companyId);
            $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
            $tree = new \Home\Model\Tree();
            $categoryValueOptions = $tree->toSelectBoxArray($expenseCategoryMapper->fetchAll($expenseCategory));
            $this->getViewModel()->setVariable('categoryValueOptions', $categoryValueOptions);
        
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $transaction = new \Accounting\Model\Transaction();
                $transaction->exchangeArray($formData);
                $transaction->setType(\Accounting\Model\Transaction::TYPE_PAYMENT);
                $transaction->setCreatedDate(DateBase::getCurrentDate());
                $transaction->setCreatedDateTime(DateBase::getCurrentDateTime());
                $transaction->setCreatedById($this->user()->getIdentity());
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_NEW);

                $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
                $transactionMapper->save($transaction);

                $itemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
                if(isset($formData['itemData']) && count($formData['itemData'])){
                    foreach ($formData['itemData'] as $itemData){
                        $item = new \Accounting\Model\Transaction\Item();
                        $item->exchangeArray($itemData);
                        $item->setTransactionId($transaction->getId());
                        $item->setStatus($transaction->getStatus());
                        $item->setTransactionDate($transaction->getApplyDate());
                        $itemMapper->save($item);
                    }
                }

                if($form->get('afterSubmit')->getValue()){
                    return $this->redirect()->toUrl($form->get('afterSubmit')->getValue());
                }
                return $this->redirect()->toUrl('/accounting/transaction/index');
            }
        }
        return $this->getViewModel();
    }

    public function editreqAction(){
        $id = $this->getRequest()->getQuery('id');
        if(!$id){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        $transaction = new \Accounting\Model\Transaction();
        $transaction->setId($id);
        $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
        if(!$transactionMapper->get($transaction)){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        if($transaction->getStatus() != \Accounting\Model\Transaction::STATUS_NEW){
            return $this->getViewModel()->setVariable('errorMsg', ['Phiếu đã được duyệt không thể sửa']);
        }
        if(!$this->user()->isAdmin()){
            if($transaction->getCreatedById() != $this->user()->getIdentity()){
                return $this->getViewModel()->setVariable('errorMsg', ['Bạn không có quyền sửa phiếu của người khác']);
            }
        }
        $form = new \Accounting\Form\Transaction\ApproveReq($this->getServiceLocator());
        $form->setData($transaction->toFormValue());
        $form->setCompanyId($transaction->getCompanyId());
        $form->setId($transaction->getId());
        $this->getViewModel()->setVariable('form', $form);
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $transaction->exchangeArray($formData);
                $transactionMapper->save($transaction);
                if(isset($formData['itemData']) && count($formData['itemData'])){
                    $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
                    foreach ($formData['itemData'] as $itemData){
                        $transactionItem = new \Accounting\Model\Transaction\Item();
                        $transactionItem->exchangeArray($itemData);
                        $transactionItem->setTransactionDate($transaction->getApplyDate());
                        $transactionItem->setTransactionId($transaction->getId());
                        $transactionItem->setStatus($transaction->getStatus());

                        $transactionItemMapper->save($transactionItem);
                    }
                }
                return $this->redirect()->toUrl('/accounting/transaction/index?id='.$transaction->getId());
            }
        }

        $this->getViewModel()->setVariable('transaction', $transaction);

        $transactionItem = new \Accounting\Model\Transaction\Item();
        $transactionItem->setTransactionId($transaction->getId());
        $transactionItem->addOption('loadAccountId', true);
        $transactionItem->addOption('loadExpenseCategory', true);
        $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
        $items = $transactionItemMapper->fetchAll($transactionItem);
        $this->getViewModel()->setVariable('items', $items);

        $company = new \Company\Model\Company();
        $company->setId($transaction->getCompanyId());
        $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
        if($companyMapper->get($company)){
            $this->getViewModel()->setVariable('company', $company);
        }
        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        $this->getViewModel()->setVariable('createdBy', $userMapper->get($transaction->getCreatedById()));

        $expenseCategory = new \Accounting\Model\ExpenseCategory();
        $expenseCategory->setCompanyId($transaction->getCompanyId());
        $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
        $tree = new \Home\Model\Tree();
        $this->getViewModel()->setVariable('categories',
            $tree->toArrayRecusived($expenseCategoryMapper->fetchAll($expenseCategory)));

        $account = new \Accounting\Model\Account();
        $account->setCompanyId($transaction->getCompanyId());
        $account->addOption('sort', ['sort' => 'c.id', 'dir' => 'ASC']);
        $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        $this->getViewModel()->setVariable('accounts',
            $tree->toArrayRecusived($accountMapper->fetchAll($account)));
        return $this->getViewModel();
    }

    public function approvereqAction(){
        $id = $this->getRequest()->getQuery('id');
        if(!$id){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        $transaction = new \Accounting\Model\Transaction();
        $transaction->setId($id);
        $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
        if(!$transactionMapper->get($transaction)){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        if(!in_array($transaction->getStatus(), [
            \Accounting\Model\Transaction::STATUS_NEW,
            \Accounting\Model\Transaction::STATUS_APPROVED,
            \Accounting\Model\Transaction::STATUS_INAPPROVED])){
            return $this->getViewModel()->setVariable('errorMsg', ['Phiếu đã được hạch toán không thể duyệt']);
        }
        $form = new \Accounting\Form\Transaction\ApproveReq($this->getServiceLocator());
        $form->setData($transaction->toFormValue());
        $form->setCompanyId($transaction->getCompanyId());
        $form->setId($transaction->getId());
        $this->getViewModel()->setVariable('form', $form);
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $transaction->exchangeArray($formData);
                $transaction->setApprovedById($this->user()->getIdentity());
                $transaction->setApprovedDateTime(DateBase::getCurrentDateTime());
                if($formData['isInApprove']){
                    $transaction->setStatus(\Accounting\Model\Transaction::STATUS_INAPPROVED);
                } else {
                    $transaction->setStatus(\Accounting\Model\Transaction::STATUS_APPROVED);
                }

                $transactionMapper->save($transaction);
                if(isset($formData['itemData']) && count($formData['itemData'])){
                    $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
                    foreach ($formData['itemData'] as $itemData){
                        $transactionItem = new \Accounting\Model\Transaction\Item();
                        $transactionItem->exchangeArray($itemData);
                        $transactionItem->setTransactionDate($transaction->getApplyDate());
                        $transactionItem->setTransactionId($transaction->getId());
                        $transactionItem->setStatus($transaction->getStatus());

                        $transactionItemMapper->save($transactionItem);
                    }
                }
                return $this->redirect()->toUrl('/accounting/transaction/index?id='.$transaction->getId());
            }
        }

        $this->getViewModel()->setVariable('transaction', $transaction);

        $transactionItem = new \Accounting\Model\Transaction\Item();
        $transactionItem->setTransactionId($transaction->getId());
        $transactionItem->addOption('loadAccountId', true);
        $transactionItem->addOption('loadExpenseCategory', true);
        $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
        $items = $transactionItemMapper->fetchAll($transactionItem);
        $this->getViewModel()->setVariable('items', $items);

        $company = new \Company\Model\Company();
        $company->setId($transaction->getCompanyId());
        $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
        if($companyMapper->get($company)){
            $this->getViewModel()->setVariable('company', $company);
        }
        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        $this->getViewModel()->setVariable('createdBy', $userMapper->get($transaction->getCreatedById()));

        $expenseCategory = new \Accounting\Model\ExpenseCategory();
        $expenseCategory->setCompanyId($transaction->getCompanyId());
        $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
        $tree = new \Home\Model\Tree();
        $this->getViewModel()->setVariable('categories',
            $tree->toArrayRecusived($expenseCategoryMapper->fetchAll($expenseCategory)));

        $account = new \Accounting\Model\Account();
        $account->setCompanyId($transaction->getCompanyId());
        $account->addOption('sort', ['sort' => 'c.id', 'dir' => 'ASC']);
        $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        $this->getViewModel()->setVariable('accounts',
            $tree->toArrayRecusived($accountMapper->fetchAll($account)));
        return $this->getViewModel();
    }

    public function accountingreqAction(){
        $id = $this->getRequest()->getQuery('id');
        if(!$id){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        $transaction = new \Accounting\Model\Transaction();
        $transaction->setId($id);
        $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
        if(!$transactionMapper->get($transaction)){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        if($transaction->getStatus() == \Accounting\Model\Transaction::STATUS_INAPPROVED){
            return $this->getViewModel()->setVariable('errorMsg', ['Phiếu này không được duyệt']);
        }
        if(!in_array($transaction->getStatus(), [\Accounting\Model\Transaction::STATUS_NEW,
             \Accounting\Model\Transaction::STATUS_APPROVED])){
            return $this->getViewModel()->setVariable('errorMsg', ['Phiếu đã được hạch toán']);
        }
        $form = new \Accounting\Form\Transaction\ApproveReq($this->getServiceLocator());
        $form->setData($transaction->toFormValue());
        $form->setCompanyId($transaction->getCompanyId());
        $form->setId($transaction->getId());
        $this->getViewModel()->setVariable('form', $form);
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $transaction->exchangeArray($formData);
                $transaction->setAccountingById($this->user()->getIdentity());
                $transaction->setAccountingDateTime(DateBase::getCurrentDateTime());
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_ACCOUNTING);
                $transactionMapper->save($transaction);
                if(isset($formData['itemData']) && count($formData['itemData'])){
                    $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
                    foreach ($formData['itemData'] as $itemData){
                        $transactionItem = new \Accounting\Model\Transaction\Item();
                        $transactionItem->exchangeArray($itemData);
                        $transactionItem->setTransactionDate($transaction->getApplyDate());
                        $transactionItem->setTransactionId($transaction->getId());
                        $transactionItem->setStatus($transaction->getStatus());

                        $transactionItemMapper->save($transactionItem);
                    }
                }
                return $this->redirect()->toUrl('/accounting/transaction/index?id='.$transaction->getId());
            }
        }

        $this->getViewModel()->setVariable('transaction', $transaction);

        $transactionItem = new \Accounting\Model\Transaction\Item();
        $transactionItem->setTransactionId($transaction->getId());
        $transactionItem->addOption('loadAccountId', true);
        $transactionItem->addOption('loadExpenseCategory', true);
        $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
        $items = $transactionItemMapper->fetchAll($transactionItem);
        $this->getViewModel()->setVariable('items', $items);

        $company = new \Company\Model\Company();
        $company->setId($transaction->getCompanyId());
        $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
        if($companyMapper->get($company)){
            $this->getViewModel()->setVariable('company', $company);
        }
        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        $this->getViewModel()->setVariable('createdBy', $userMapper->get($transaction->getCreatedById()));
        if($transaction->getApprovedById()){
            $this->getViewModel()->setVariable('approveBy', $userMapper->get($transaction->getApprovedById()));
        }

        $expenseCategory = new \Accounting\Model\ExpenseCategory();
        $expenseCategory->setCompanyId($transaction->getCompanyId());
        $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
        $tree = new \Home\Model\Tree();
        $this->getViewModel()->setVariable('categories',
            $tree->toArrayRecusived($expenseCategoryMapper->fetchAll($expenseCategory)));

        $account = new \Accounting\Model\Account();
        $account->setCompanyId($transaction->getCompanyId());
        $account->addOption('sort', ['sort' => 'c.id', 'dir' => 'ASC']);
        $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        $this->getViewModel()->setVariable('accounts',
            $tree->toArrayRecusived($accountMapper->fetchAll($account)));
        return $this->getViewModel();
    }

    public function paymentAction(){
        $id = $this->getRequest()->getQuery('id');
        if(!$id){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        $transaction = new \Accounting\Model\Transaction();
        $transaction->setId($id);
        $transactionMapper = $this->getServiceLocator()->get('\Accounting\Model\TransactionMapper');
        if(!$transactionMapper->get($transaction)){
            return $this->getViewModel()->setVariable('errorMsg', ['Không tìm thấy phiếu thu chi']);
        }
        if(!in_array($transaction->getStatus(), [\Accounting\Model\Transaction::STATUS_ACCOUNTING])){
            return $this->getViewModel()->setVariable('errorMsg', ['Phiếu chưa được hạch toán, không thể kí chi thu']);
        }
        $form = new \Accounting\Form\Transaction\ApproveReq($this->getServiceLocator());
        $form->setData($transaction->toFormValue());
        $form->setCompanyId($transaction->getCompanyId());
        $form->setId($transaction->getId());
        $this->getViewModel()->setVariable('form', $form);
        if($this->getRequest()->isPost()){
            $form->setData($this->getRequest()->getPost());
            if($form->isValid()){
                $formData = $form->getData();
                $transaction->exchangeArray($formData);
                $transaction->setPaymentById($this->user()->getIdentity());
                $transaction->setPaymentDateTime(DateBase::getCurrentDateTime());
                $transaction->setStatus(\Accounting\Model\Transaction::STATUS_PAYMENT);
                $transactionMapper->save($transaction);
                if(isset($formData['itemData']) && count($formData['itemData'])){
                    $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
                    foreach ($formData['itemData'] as $itemData){
                        $transactionItem = new \Accounting\Model\Transaction\Item();
                        $transactionItem->exchangeArray($itemData);
                        $transactionItem->setTransactionDate($transaction->getApplyDate());
                        $transactionItem->setTransactionId($transaction->getId());
                        $transactionItem->setStatus($transaction->getStatus());

                        $transactionItemMapper->save($transactionItem);
                    }
                }
                return $this->redirect()->toUrl('/accounting/transaction/index?id='.$transaction->getId());
            }
        }

        $this->getViewModel()->setVariable('transaction', $transaction);

        $transactionItem = new \Accounting\Model\Transaction\Item();
        $transactionItem->setTransactionId($transaction->getId());
        $transactionItem->addOption('loadAccountId', true);
        $transactionItem->addOption('loadExpenseCategory', true);
        $transactionItemMapper = $this->getServiceLocator()->get('\Accounting\Model\Transaction\ItemMapper');
        $items = $transactionItemMapper->fetchAll($transactionItem);
        $this->getViewModel()->setVariable('items', $items);

        $company = new \Company\Model\Company();
        $company->setId($transaction->getCompanyId());
        $companyMapper = $this->getServiceLocator()->get('\Company\Model\CompanyMapper');
        if($companyMapper->get($company)){
            $this->getViewModel()->setVariable('company', $company);
        }
        $userMapper = $this->getServiceLocator()->get('\User\Model\UserMapper');
        $this->getViewModel()->setVariable('createdBy', $userMapper->get($transaction->getCreatedById()));
        if($transaction->getApprovedById()){
            $this->getViewModel()->setVariable('approveBy', $userMapper->get($transaction->getApprovedById()));
        }
        if($transaction->getAccountingById()){
            $this->getViewModel()->setVariable('accountingBy', $userMapper->get($transaction->getAccountingById()));
        }

        $expenseCategory = new \Accounting\Model\ExpenseCategory();
        $expenseCategory->setCompanyId($transaction->getCompanyId());
        $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
        $tree = new \Home\Model\Tree();
        $this->getViewModel()->setVariable('categories',
            $tree->toArrayRecusived($expenseCategoryMapper->fetchAll($expenseCategory)));

        $account = new \Accounting\Model\Account();
        $account->setCompanyId($transaction->getCompanyId());
        $account->addOption('sort', ['sort' => 'c.id', 'dir' => 'ASC']);
        $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        $this->getViewModel()->setVariable('accounts',
            $tree->toArrayRecusived($accountMapper->fetchAll($account)));
        return $this->getViewModel();
    }
}