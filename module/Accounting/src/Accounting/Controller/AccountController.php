<?php
/**
 * Accounting\Controller
 *
 * @category    Shop99 library
 * @copyright    http://shop99.vn
 * @license        http://shop99.vn/license
 */
namespace Accounting\Controller;

use Home\Controller\ControllerBase;
use Accounting\Model\Account;
use Home\Model\DateBase;
use Zend\View\Model\JsonModel;
use Home\Model\Tree;

class AccountController extends ControllerBase
{

    public function indexAction()
    {
        $form = new \Accounting\Form\AccountFilter($this->getServiceLocator());
        $form->setData($this->params()->fromQuery());
        
        $this->getViewModel()->setVariable('form', $form);
        
        if ($form->isValid()) {
            $account = new Account();
            $account->exchangeArray($form->getData());
            $account->addOption('loadUser', true);
            $account->addOption('loadCompany', true);
            
            if(!$this->user()->getUser()->isAdmin()){              
                $account->addOption('companyId', $this->user()->getCompanyId());
            }
            if(!$this->user()->getCompanyId()){
            	return $this->page403();
            }
            $accountMapper = $this->getServiceLocator()->get('Accounting\Model\AccountMapper');
            $tree = new Tree();
            $accounttrees = $tree->toArrayRecusived($accountMapper->fetchAll($account));
            $this->getViewModel()->setVariable('paginator', $accounttrees);
        }
        return $this->getViewModel();
    }

    public function addAction()
    {
        $form = new \Accounting\Form\Account($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()
                ->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $account = new Account();
                $account->exchangeArray($data);
                $account->setCreatedById($this->user()
                    ->getIdentity());
                $account->setCreatedDateTime(DateBase::getCurrentDateTime());
                $account->setStatus($account::STATUS_ACTIVE);
                $accountMapper = $this->getServiceLocator()->get('Accounting\Model\AccountMapper');
                $accountMapper->save($account);
                if ($form->get('afterSubmit')->getValue()) {
                    return $this->redirect()->toUrl($form->get('afterSubmit')
                        ->getValue());
                }
            }
        }
        $this->getViewModel()->setVariable('form', $form);

        return $this->getViewModel();
    }

    public function editAction()
    {
        $accountMapper = $this->getServiceLocator()->get('Accounting\Model\AccountMapper');

        $account = new Account();
        $account->setId($this->params()
            ->fromQuery('id'));

        if (! $account->getId() || ! $accountMapper->get($account)) {
            return $this->page404();
        }

        $form = new \Accounting\Form\Account($this->getServiceLocator());
        $form->remove('afterSubmit');
        $form->populateValues($account->toFormValues());

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()
                ->getPost());
            if ($form->isValid()) {
                $account->exchangeArray($form->getData());
                $accountMapper->save($account);
                return $this->redirect()->toUrl('/accounting/account/index?id=' . $account->getId());
            }
        }

        $this->getViewModel()->setVariable('form', $form);

        return $this->getViewModel();
    }

    public function deleteAction()
    {
        $id = $this->params()->fromQuery('id');
        $account = new Account();
        $account->setId($id);

        $accountMapper = $this->getServiceLocator()->get('Accounting\Model\AccountMapper');
        if (! $accountMapper->get($account)) {
            return $this->page404();
        }
        $auth = $this->getServiceLocator()->get('Authorize\Service\Authorize');
        if ($account->getCreatedById() == $this->user()->getIdentity() ||$this->user()->getUser()->isAdmin()) {
            $accountMapper->delete($account);
            $jsonModel = new JsonModel();
            $jsonModel->setVariables([
                'code' => 1,
                'messages' => [
                    'Đã xóa thành công.'
                ]
            ]);
            return $jsonModel;
        } else {
            $jsonModel = new JsonModel();
            $jsonModel->setVariables([
                'code' => 0,
                'messages' => [
                    'Bạn không có quyền xóa.'
                ]
            ]);
            return $jsonModel;
        }
        return $this->redirect()->toUrl('/accounting');
    }
    public function suggestAction()
    {
        $q = $this->getRequest()->getPost('q');
        //$q = $this->params()->fromQuery('q');
        $account = new Account();
        $account->setName($q);
        $jsonModel = new JsonModel();
        if (! $q) {
            $jsonModel->setVariables([
                'code' => 1,
                'data' => []
                ]);
            return $jsonModel;
        }
        $accountMapper = $this->getServiceLocator()->get('Accounting\Model\AccountMapper');
        $jsonModel->setVariables([
            'code' => 1,
            'data' => $accountMapper->suggest($account)
            ]);
        return $jsonModel;
    }

    public function loadAction(){
        $companyId = $this->getRequest()->getPost('companyId');
        if(!$companyId){
            $companyId = $this->user()->getCompanyId();
        } elseif (!$this->company()->canManage($companyId)){
            return $this->getJsonModel()->setVariables(array(
            	'code' => 0,
                'messages' => ['Bạn không có quyền quản lí doanh nghiệp này']
            ));
        }
        $modeLoad = $this->getRequest()->getPost('modeLoad');
        $accountIds = [];
        $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        if($modeLoad && $modeLoad == 'basic'){
            $account = new \Accounting\Model\Account();
            $account->setCompanyId($companyId);

            $accountIds = $accountMapper->recusiveBranchChildIds($account, [\Accounting\Model\Account::ID_CASH]);
        }
        $account = new \Accounting\Model\Account();

        if(count($accountIds)){
            $account->addOption('ids', $accountIds);
        } else {
            $account->setCompanyId($companyId);
            $account->addOption('loadSystemAccount', true);
        }
        $tree = new \Home\Model\Tree();
        $accounts = $tree->toArrayRecusived($accountMapper->fetchAll($account));
        $data = [];
        if($accounts){
            foreach ($accounts as $account){
                $data[] = array(
                    'id' => $account->getId(),
                    'name' => $account->getName(),
                    'code' => $account->getCode(),
                    'ord' => $account->getOption('ord'),
                    'displayName' => str_repeat('--', $account->getOption('ord')?:0).$account->getName()
                );
            }
        }
        return $this->getJsonModel()->setVariables(array(
            'code' => 1,
            'data' => $data
        ));
    }

    public function initnewcompanyAction(){
        set_time_limit(300);
        $form = new \Accounting\Form\InitNewCompany($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $companyId = $data['companyId'];
                $this->insertaccount(require_once BASE_PATH.'/data/RootAccountingAccount.php', $companyId);
                if ($form->get('afterSubmit')->getValue()) {
                    return $this->redirect()->toUrl($form->get('afterSubmit')
                        ->getValue());
                }
            }
        }
        $this->getViewModel()->setVariable('form', $form);
        return $this->getViewModel();
    }

    private function insertaccount($accounts, $companyId, $parentId = null){
        if(!$accounts || !count($accounts)){
            return null;
        }
        $accountMapper = $this->getServiceLocator()->get('\Accounting\Model\AccountMapper');
        foreach ($accounts as $accountArray){
            $account = new \Accounting\Model\Account();
            $account->setCode($accountArray['code']);
            $account->setName($accountArray['name']);
            $account->setParentId($parentId);
            $account->setCompanyId($companyId);
            if(!$accountMapper->isExisted($account)){
                $account->setStatus(\Accounting\Model\Account::STATUS_ACTIVE);
                $account->setCreatedById($this->user()->getIdentity());
                $account->setCreatedDateTime(DateBase::getCurrentDateTime());
                $accountMapper->save($account);
            }
            if(isset($accountArray['childs']) && count($accountArray['childs'])){
                $this->insertaccount($accountArray['childs'], $companyId, $account->getId());
            }
        }
    }
}