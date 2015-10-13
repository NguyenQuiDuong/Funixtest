<?php
/**
 * Home\Controller
 *
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Home\Controller;

use Zend\View\Model\JsonModel;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use \stdClass;

class SearchController extends AbstractActionController
{
	public function indexAction()
	{
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $ps = new \Product\Model\Store();
        $ps->setServiceLocator($this->getServiceLocator());

        if (in_array($this->getServiceLocator()->get('Store\Service\Store')->getStoreId(), [66, 224, 81])) {
            $ps->setCategoryBaseId(12); // @todo fixed attrCategoryId for default store
        }

        $variables = $ps->prepareSearch();

        $page = (int)$request->getQuery('page', 1);
        $icpp = (int)$request->getQuery('icpp', 20);

		$order = urldecode($request->getQuery('order','id desc'));
		$options = array(
			'page' => $page > 0 ? $page : 1,
			'icpp' => $icpp > 0 ? ($icpp > 100 ? 100 : $icpp) : 20,
			'order' => (strlen($order) > 0 ? $order : 'id desc')
		);
		/* @var $mapper \Product\Model\StoreMapper */
		$mapper = $this->getServiceLocator()->get('Product\Model\StoreMapper');
		$paginator = $mapper->search($ps, $options);

        $viewModel = new ViewModel();
        // switch to json view mode
        if($request->getQuery('format') == 'json') {
            $products = array();
            foreach($paginator as $p) {
                /* @var $p \Product\Model\Store */
                $products[] = $p->toStd();
            }
            return new JsonModel(array(
                'products' => $products,
            	'maxPage' => count($paginator),
            	'totalProduct' => $paginator->getTotalItemCount(),
            ));
        }
        if($request->getPost('template')){
            $viewModel->setTemplate($request->getPost('template'));
            $viewModel->setTerminal($request->getPost('terminal', false));
        }

        $viewModel->setVariables(array(
            'paginator' => $paginator,
            'query' => urldecode($request->getQuery('q')),
            'order' => urldecode($request->getQuery('order'))
        ));
        $viewModel->setVariables($variables);

		return $viewModel;
    }

    /**
     * @uses autocomplete
     */
    public function suggestionAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $data = array();
        if (!($q = urldecode(trim($request->getQuery('q'))))) {
            return new JsonModel($data);
        }
        $ps = new \Product\Model\Store();
        $ps->setServiceLocator($this->getServiceLocator());

        /* @var $psMapper \Product\Model\StoreMapper */
        $psMapper = $this->getServiceLocator()->get('Product\Model\StoreMapper');
        /* @var $categoryMapper \Product\Model\CategoryMapper */
        $categoryMapper = $this->getServiceLocator()->get('Product\Model\CategoryMapper');

        $data['searchOptions'] = $ps->prepareSearch();
        $limit = trim($request->getQuery('limit'));
        $options['limit'] = $limit > 0 ? $limit : 20;
        $products = $psMapper->search($ps, $options);

        if (is_array($products) && count($products)) {
            $cIds = [];
            /* @var $ps \Product\Model\Store */
            foreach ($products as $ps) {
                $data['products'][] = $ps->toStd();
                if($ps->getCategoryId()){
                    $cIds[$ps->getCategoryId()] = $ps->getCategoryId();
                }
            }
            if($request->getQuery('showMore') && $request->getQuery('showMore') == 'category'){
                $category = new \Product\Model\Category();
                $category->setStoreId($ps->getStoreId());
                $category->setChilds($cIds);
                $categories = $categoryMapper->search($category, ['limit' => 5]);
                if (count($categories)) {
                    foreach ($categories as $c) {
                        $data['categories'][] = $c->toStd();
                    }
                }
            }
        }
        return new JsonModel($data);
    }

    public function noresultAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $layoutMode = trim($request->getQuery('layout', null));
    	$view = new ViewModel();
    	if($layoutMode == 'false') {
    		$view->setTerminal(true);
    	}
    	return $view;
    }

    public function albumAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $album = new \Album\Model\Album();
        $album->setServiceLocator($this->getServiceLocator());

        $page = (int)$request->getQuery('page', 1);
        $icpp = (int)$request->getQuery('icpp', 20);

        $options = array(
            'page' => $page > 0 ? $page : 1,
            'icpp' => $icpp > 0 ? ($icpp > 100 ? 100 : $icpp) : 20,
        );

        $variables = $album->searchOptions($options);
        /* @var $AlbumMapper \Album\Model\AlbumMapper */
        $AlbumMapper = $this->getServiceLocator()->get('Album\Model\AlbumMapper');
        $paginator = $AlbumMapper->search($album, $options);

        $viewModel = new ViewModel();

        $viewModel->setVariables(array(
            'paginator' => $paginator,
            'query' => urldecode($request->getQuery('q')),
        ));
        $viewModel->setVariables($variables);

        return $viewModel;
    }

}