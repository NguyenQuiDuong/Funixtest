<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Controller;

use Home\Controller\ControllerBase;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Home\Model\DateBase;

class ImportController extends ControllerBase{
	public function indexAction(){
		echo '\\';die;
	}

	public function mcaAction(){
		$dbSql = $this->getServiceLocator()->get('dbSql');
		$dbAdapter = $this->getServiceLocator()->get('dbAdapter');
		/* @var $dbSql \Zend\Db\Sql\Sql */
		$update = $dbSql->update(\System\Model\ModuleMapper::TABLE_NAME);
		$update->set(['updatedDateTime' => null]);
		$query = $dbSql->buildSqlString($update);
		$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

		$update = $dbSql->update(\System\Model\ControllerMapper::TABLE_NAME);
		$update->set(['updatedDateTime' => null]);
		$query = $dbSql->buildSqlString($update);
		$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

		$update = $dbSql->update(\System\Model\ActionMapper::TABLE_NAME);
		$update->set(['updatedDateTime' => null]);
		$query = $dbSql->buildSqlString($update);
		$results = $dbAdapter->query($query, $dbAdapter::QUERY_MODE_EXECUTE);

		$updatedDateTime = DateBase::getCurrentDateTime();

		$dir = realpath(BASE_PATH .'/module').'/';
		$modules = scandir($dir);
		$total = 0;

		$moduleMapper = $this->getServiceLocator()->get('\System\Model\ModuleMapper');
		$controllerMapper = $this->getServiceLocator()->get('\System\Model\ControllerMapper');
		$actionMapper = $this->getServiceLocator()->get('\System\Model\ActionMapper');
		foreach ($modules as $moduleName) {
			if ($moduleName != '.' && $moduleName != '..' && !strpos($moduleName, 'svn')) {
				//$moduleName = strtolower($moduleName);
				$module = new \System\Model\Module();
				$module->setName(strtolower($moduleName));
				$module->setCreatedById($this->user()->getIdentity());
				$module->setStatus(\System\Model\Action::STATUS_ACTIVE);
				$module->setCreatedDateTime($updatedDateTime);
				$module->setUpdatedDateTime($updatedDateTime);
				if($moduleMapper->isExisted($module)){
					echo "<b>{$module->getName()} ==========================</b><br>";
            		$module->setUpdatedDateTime($updatedDateTime);
            	} else {
            		echo "<b style='color:blue'>{$module->getName()} ==========================</b><br>";
            	}
            	$moduleMapper->save($module);

            	$controllerDir = @scandir($dir . $moduleName . '/src/'.$moduleName.'/Controller/');
            	if ($controllerDir) {
            		foreach ($controllerDir as $controllerFile) {
            			if ($controllerFile != '.' && $controllerFile != '..'
            					&& !strpos($controllerFile, 'svn')
            					&& is_file($dir . $moduleName . '/src/'.$moduleName.'/Controller/'.$controllerFile)) {
            				$controllerName = trim(strtolower(str_replace("Controller.php", "", $controllerFile)));
            				$controller = new \System\Model\Controller();
            				$controller->setModuleId($module->getId());
            				$controller->setName($controllerName);
            				$controller->setStatus(\System\Model\Action::STATUS_ACTIVE);
            				$controller->setCreatedById($this->user()->getIdentity());
            				$controller->setCreatedDateTime($updatedDateTime);
            				$controller->setUpdatedDateTime($updatedDateTime);
            				if($controllerMapper->isExisted($controller)) {
            					echo "<b>$controllerName ---------------------------------</b><br>";
            					$controller->setUpdatedDateTime($updatedDateTime);
            				} else {
            					echo "<b style='color:blue'>$controllerName ---------------------------------</b><br>";
            				}
            				$controllerMapper->save($controller);

            				require_once $dir . $moduleName . '/src/'.$moduleName.'/Controller/' . $controllerFile;
            				$actions = get_class_methods("\\".$moduleName."\\Controller\\".$controllerName."Controller");
            				if(is_array($actions)) {
            					foreach($actions as $actionName) {
            						if(strpos($actionName, 'Action') && !in_array($actionName, ['getMethodFromAction', 'notFoundAction'])) {
            							$actionName = str_replace('Action', '', $actionName);

            							$action = new \System\Model\Action();
            							$action->setControllerId($controller->getId());
            							$action->setName($actionName);
            							$action->setStatus(\System\Model\Action::STATUS_ACTIVE);
            							$action->setDisplay(\System\Model\Action::DISPLAY_INACTIVE);
            							$action->setCreatedById($this->user()->getIdentity());
            							$action->setCreatedDateTime($updatedDateTime);
            							$action->setUpdatedDateTime($updatedDateTime);
            							if($actionMapper->isExisted($action)) {
            								$action->setUpdatedDateTime($updatedDateTime);
            							} else {
            								echo ++$total . ": <b style='color:blue'>$actionName</b><br>";
            							}
            							$actionMapper->save($action);
            						}
            					}
            				} else {
                            	echo '<b style="color:red">'. ucfirst($moduleName) .'_'.
                            		ucfirst($controllerName) .'Controller </b> has no actions<br>';
                            }

            			}
            		}
            	}
			}
		}
		die;

	}

	public function descriptionAction(){
		$module = new \System\Model\Module();
		$moduleMapper = $this->getServiceLocator()->get('\System\Model\ModuleMapper');
		$modules = $moduleMapper->fetchAll($module);
		$actionMapper = $this->getServiceLocator()->get('\System\Model\ActionMapper');

		if($modules){
			foreach ($modules as $module){
				if($this->getServiceLocator()->has($module->getName().'Navigation')){
					$navi = $this->getServiceLocator()->get($module->getName().'Navigation');
					/*@var $navi \Zend\Navigation\Navigation */
					echo '<b>'.$module->getName().'</b><br/>';
					$iterator = new \RecursiveIteratorIterator($navi, \RecursiveIteratorIterator::SELF_FIRST);

					foreach ($iterator as $page) {
						if($page->resource && $page->privilege){
							$action = new \System\Model\Action();
							$action->addOption('path', '/'.str_replace(':', '/', $page->resource). '/' .$page->privilege);
							if($actionMapper->getByPath($action)){
								if(!$action->getDescription()){
									$action->setDescription($page->label);
									$action->setDisplay(\System\Model\Action::DISPLAY_ACTIVE);
									$actionMapper->save($action);
									echo $action->getId().' - '.$action->getDescription().'<br/>';
								}
							}
						}
					}
				}

			}
		}
		die;
	}
}