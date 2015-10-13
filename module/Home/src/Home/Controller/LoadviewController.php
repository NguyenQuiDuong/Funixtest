<?php
/**
 * @category    Shop99 library
 * @copyright    http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace Home\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class LoadviewController extends AbstractActionController
{

    public function indexAction()
    {
        /* @var $request \Zend\Http\Request */
        $request = $this->getRequest();
        $viewName = trim($request->getQuery('v'));
        $variable = $request->getPost('variable');

        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);

        if(isset($variable)){
            $viewModel->setVariable('variable', json_decode($variable));
        }

        if (!isset($viewName)) {
            return $viewModel;
        }
        $viewModel->setVariables(['serviceLocator' => $this->getServiceLocator()]);
        $viewModel->setTemplate('home/loadview/' . $viewName);
        return $viewModel;
    }

}