<?php
/**
 * Home\Controller
 *
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace Home\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\Http\Response;

class ControllerBase extends AbstractActionController
{
	/**
	 * @var \Zend\View\Model\ViewModel
	 */
	protected $viewModel;
	/**
	 * @var \Zend\View\Model\JsonModel
	 */
	protected $jsonModel;

	/**
	 * @return \Zend\View\Model\ViewModel
	 */
	public function getViewModel()
	{
		if(!$this->viewModel) {
			$this->viewModel = new ViewModel();
		}
		return $this->viewModel;
	}

	/**
	 * @return \Zend\View\Model\JsonModel
	 */
	public function getJsonModel()
	{
		if(!$this->jsonModel) {
			$this->jsonModel = new JsonModel();
		}
		return $this->jsonModel;
	}

	/**
	 * @author VanCK
	 * @param string $options
	 */
	public function page403($options = null) {
// 		$this->getResponse()->setStatusCode(Response::STATUS_CODE_403);
		return $this->getViewModel()->setTemplate('error/403');
	}

	/**
	 * @author VanCK
	 * @param string $options
	 */
	public function page404($options = null) {
// 		$this->getResponse()->setStatusCode(Response::STATUS_CODE_404);
		return $this->getViewModel()->setTemplate('error/404');
	}

	/**
	 * @author VanCK
	 * @return \Company\Service\Company
	 */
	public function company() {
		return $this->getServiceLocator()->get('Company\Service\Company');
	}

	/**
	 * @author KienNN
	 * @return \Company\Service\Department
	 */
	public function department(){
		return $this->getServiceLocator()->get('Company\Service\Department');
	}

	/**
	 * @author VanCK
	 * @return \Work\Service\Work
	 */
	public function work() {
		return $this->getServiceLocator()->get('Work\Service\Work');
	}

	/**
	 * @author KienNN
	 * @return \Work\Service\Project
	 */
	public function project(){
		return $this->getServiceLocator()->get('Work\Service\Project');
	}

	public function user(){
	    return $this->getServiceLocator()->get('User\Service\User');
	}

	public function crm(){
	    return $this->getServiceLocator()->get('Crm\Service\Crm');
	}

	public function hrm(){
	    return $this->getServiceLocator()->get('Hrm\Service\Hrm');
	}

	/**
	 * @author VanCK
	 * @param int $page
	 * @param int $icpp
	 * @return array
	 */
	protected function getPagingParams($page = null, $icpp = null)
	{
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();

        $page = (int)$request->getQuery('page', $page);
        $icpp = (int)$request->getQuery('icpp', $icpp);
        $options = array(
            'page' => $page > 0 ? $page : 1,
            'icpp' => $icpp > 0 ? ($icpp > 200 ? 200 : $icpp) : 30,
        	//'sort' => $request->getQuery('sort')
        );
		return $options;
	}

	/**
	 * @param string $defaultSort
	 * @param string $defaultDir
	 * @return array
	 */
	protected function getSorting($defaultSort = null, $defaultDir = null, $sortKeys) {
		$sort = $this->getRequest()->getQuery('sort', $defaultSort);
		if(is_array($sortKeys) && !in_array($sort, $sortKeys)) {
			$sort = $defaultSort;
		}
		$dir = $this->getRequest()->getQuery('dir', $defaultDir);
		if(!in_array($dir, array('asc', 'desc'))) {
			$dir = $defaultDir;
		}
		if(!$sort){
			return null;
		}
        return array(
        	'sort' => $sort,
        	'dir' => $dir
        );
	}
}