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
use Accounting\Model\ExpenseCategory;
use Home\Model\Tree;

class ExpenseController extends ControllerBase
{

    public function categoryAction()
    {
        $form = new \Accounting\Form\ExpenseCategoryFilter($this->getServiceLocator());
        $form->setData($this->params()->fromQuery());
        $this->getViewModel()->setVariable('form', $form);

        if ($form->isValid()) {
            $expenseCategory = new ExpenseCategory();
            $expenseCategory->exchangeArray($form->getData());
            $expenseCategory->addOption('loadUser', true);
            $expenseCategory->addOption('loadCompany', true);
            if(!$this->user()->getUser()->isAdmin()){
                $expenseCategory->addOption('companyId', $this->user()->getCompanyId());
            }
            $expenseCategoryMapper = $this->getServiceLocator()->get('Accounting\Model\ExpenseCategoryMapper');
            $tree = new Tree();
            $expenseCategorytrees = $tree->toArrayRecusived($expenseCategoryMapper->fetchAll($expenseCategory));
            $this->getViewModel()->setVariable('expenseCategorys', $expenseCategorytrees);
        }
        return $this->getViewModel();
    }

    public function addcategoryAction()
    {
        $form = new \Accounting\Form\ExpenseCategory($this->getServiceLocator());
        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()
                ->getPost());
            if ($form->isValid()) {
                $data = $form->getData();
                $expenseCategory = new ExpenseCategory();
                $expenseCategory->exchangeArray($data);
                $expenseCategory->setCreatedById($this->user()->getIdentity());
                $expenseCategory->setCreatedDateTime(DateBase::getCurrentDateTime());
                $expenseCategory->setStatus($expenseCategory::STATUS_ACTIVE);
                $expenseCategoryMapper = $this->getServiceLocator()->get('Accounting\Model\ExpenseCategoryMapper');
                $expenseCategoryMapper->save($expenseCategory);
                if ($form->get('afterSubmit')->getValue()) {
                    return $this->redirect()->toUrl($form->get('afterSubmit')
                        ->getValue());
                }
            }
        }
        $this->getViewModel()->setVariable('form', $form);

        return $this->getViewModel();
    }

    public function editcategoryAction()
    {
        $expenseCategoryMapper = $this->getServiceLocator()->get('Accounting\Model\ExpenseCategoryMapper');
        $expenseCategory = new ExpenseCategory();
        $expenseCategory->setId($this->params()->fromQuery('id'));
        if (! $expenseCategory->getId() || ! $expenseCategoryMapper->get($expenseCategory)) {
            return $this->page404();
        }
        $form = new \Accounting\Form\ExpenseCategory($this->getServiceLocator(),['isEdit' => true]);
        $form->remove('afterSubmit');
        $form->populateValues($expenseCategory->toFormValues());

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $expenseCategory->exchangeArray($form->getData());
                $expenseCategoryMapper->save($expenseCategory);
                return $this->redirect()->toUrl('/accounting/expense/category');
            }
        }

        $this->getViewModel()->setVariable('form', $form);

        return $this->getViewModel();
    }

    public function deletecategoryAction()
    {
        $id = $this->params()->fromQuery('id');
        $account = new Account();
        $account->setId($id);

        $accountMapper = $this->getServiceLocator()->get('Accounting\Model\AccountMapper');
        if (! $accountMapper->get($account)) {
            return $this->page404();
        }
        $auth = $this->getServiceLocator()->get('Authorize\Service\Authorize');
        if ($account->getCreatedById() == $this->user()->getIdentity() || $auth->isAllowed('accounting:expense','delete')) {
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
        $expenseCategory = new ExpenseCategory();
        $expenseCategory->setName($q);
        $expenseCategory->setCompanyId($this->getRequest()->getPost('companyId'));
        $jsonModel = new JsonModel();
        if (! $q) {
            $jsonModel->setVariables([
                'code' => 1,
                'data' => []
                ]);
            return $jsonModel;
        }
        $expenseCategoryMapper = $this->getServiceLocator()->get('Accounting\Model\ExpenseCategoryMapper');
        $jsonModel->setVariables([
            'code' => 1,
            'data' => $expenseCategoryMapper->suggest($expenseCategory)
            ]);
        return $jsonModel;
    }


    public function loadcategoriesAction(){
        $companyId = $this->getRequest()->getPost('companyId');
        if(!$companyId){
            $companyId = $this->user()->getCompanyId();
        } else {
            if(!$this->company()->canManage($companyId)){
                return $this->getJsonModel()->setVariables(array(
                    'code' => 0,
                    'messages' => ['Bạn không được quyền quản lí doanh nghiệp này']
                ));
            }
        }

        $expenseCategory = new \Accounting\Model\ExpenseCategory();
        $expenseCategory->setCompanyId($companyId);
        $expenseCategoryMapper = $this->getServiceLocator()->get('\Accounting\Model\ExpenseCategoryMapper');
        $tree = new \Home\Model\Tree();
        $categoies = $tree->toArrayRecusived($expenseCategoryMapper->fetchAll($expenseCategory));
        $data = [];
        if($categoies){
            foreach ($categoies as $expenseCategory){
                $data[] = array(
                    'id' => $expenseCategory->getId(),
                    'name' => $expenseCategory->getName(),
                    'code' => $expenseCategory->getCode(),
                    'ord' => $expenseCategory->getOption('ord'),
                    'displayName' => str_repeat('--', $expenseCategory->getOption('ord')?:0).$expenseCategory->getName()
                );
            }
        }
        return $this->getJsonModel()->setVariables(array(
            'code' => 1,
            'data' => $data
        ));
    }
}