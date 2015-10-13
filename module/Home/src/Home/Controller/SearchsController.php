<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace Home\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

class SearchsController extends AbstractActionController
{
    public function indexAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $p = new \Product\Model\Store();
        $p->setServiceLocator($this->getServiceLocator());
        $p->setOptions(['page' => $request->getQuery('page'), 'icpp' => $request->getQuery('icpp')]);

        $variables = $p->searchOptions();

        /* @var $baseStoreMapper \Product\Model\BaseStoreMapper */
        $baseStoreMapper = $this->getServiceLocator()->get('Product\Model\BaseStoreMapper');
        $paginator = $baseStoreMapper->search($p);

        $viewModel = new ViewModel();
        if ($request->getPost('template')) {
            $viewModel->setTemplate($request->getPost('template'));
            $viewModel->setTerminal($request->getPost('terminal', false));
        }

        $viewModel->setVariables(array(
            'variableFilter' => $variables,
            'paginator' => $paginator,
        ));

        return $viewModel;
    }

    /**
     * @uses autocomplete
     */
    public function suggestionAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $jsonModel = new  JsonModel();
        if (!($q = urldecode(trim($request->getQuery('q'))))) {
            return $jsonModel;
        }
        $p = new \Product\Model\Store();
        $p->setServiceLocator($this->getServiceLocator());

        $data = ['searchOptions' => $p->searchOptions()];
        $limit = trim($request->getQuery('limit'));
        $p->addOption('limit', $limit > 0 ? $limit : 20);

        /* @var $baseStoreMapper \Product\Model\BaseStoreMapper */
        $baseStoreMapper = $this->getServiceLocator()->get('Product\Model\BaseStoreMapper');
        $products = $baseStoreMapper->search($p);
        if (is_array($products) && count($products)) {
            $cIds = [];
            foreach ($products as $p) {
                /* @var $p \Product\Model\Store */
                $p->setIsBaseProduct(true);
                $data['products'][] = $p->toStd();
                if ($p->getBaseCategoryId()) {
                    $cIds[$p->getBaseCategoryId()] = $p->getBaseCategoryId();
                }
            }
            if ($request->getQuery('showMore') && $request->getQuery('showMore') == 'category') {
                /* @var $baseCategoryMapper \Product\Model\BaseCategoryMapper */
                $baseCategoryMapper = $this->getServiceLocator()->get('Product\Model\BaseCategoryMapper');

                $baseCategory = new \Product\Model\BaseCategory();
                $baseCategory->setChilds($cIds);
                $baseCategory->addOption('limit', 5);
                $categories = $baseCategoryMapper->search($baseCategory);
                if (count($categories)) {
                    foreach ($categories as $c) {
                        /* @var \Product\Model\BaseCategory $c*/
                        $data['categories'][] = $c->toStd();
                    }
                }
            }
        }
        $jsonModel->setVariables($data);
        return $jsonModel;
    }

}