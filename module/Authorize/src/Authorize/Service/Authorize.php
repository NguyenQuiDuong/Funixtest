<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Authorize\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Permissions\Acl\Exception\InvalidArgumentException;
use Zend\Permissions\Acl\Role\GenericRole as Role;

class Authorize implements ServiceLocatorAwareInterface {

	/**
	 * @var ServiceLocatorInterface
	 */
	protected $serviceLocator;

	/**
	 * @var \Zend\Permissions\Acl\Acl
	 */
	protected $acl;

	/**
	 * @var \User\Service\User
	 */
	protected $userService;

	/**
	 * @return the $serviceLocator
	 */
	public function getServiceLocator() {
		return $this->serviceLocator;
	}

	/**
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 */
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

	/**
	 * @return \User\Service\User
	 */
	public function getUserService() {
		return $this->userService;
	}

	/**
	 * @param \User\Service\User $userService
	 */
	public function setUserService($userService) {
		$this->userService = $userService;
		return $this;
	}

	/**
	 * @return the $acl
	 */
	public function getAcl() {
		return $this->acl;
	}

	/**
	 * @param \Zend\Permissions\Acl\Acl $acl
	 */
	public function setAcl($acl) {
		$this->acl = $acl;
		return $this;
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 */
	public function isAllowed($resource, $privilege = null) {
		try {
			return $this->getAcl()->isAllowed($this->getUserService()->getCompanyRole(), $resource, $privilege);
		} catch (InvalidArgumentException $e) {
			return false;
		}
	}

	public function loadPrivilege(){
		if(!$this->acl || !$this->acl instanceof \Zend\Permissions\Acl\Acl){
			return null;
		}
		$userService = $this->getServiceLocator()->get('User\Service\User');
		/*@var $userService \User\Service\User */
		if(!$userService->hasIdentity()){
			return null;
		}
		$user = $userService->getUser();
		if(in_array($user->getRole(), [
			\User\Model\User::ROLE_ADMIN,
			\User\Model\User::ROLE_SUPERADMIN,
			\User\Model\User::ROLE_GUEST,
		])){
			return null;
		}
		$dependence = $this->acl->getDependencies();
		$resources = null;

		if($resources){
			foreach ($resources as $resource){
				if($this->acl->hasResource($resource['resource'])){
					$this->acl->allow($user->getRole(), $resource['resource'], $resource['privilege']);
					if(isset($dependence['/'.str_replace(':', '/', $resource['resource']).'/'.$resource['privilege']])){
						foreach ($dependence['/'.str_replace(':', '/', $resource['resource']).'/'.$resource['privilege']] as $depen){
							$arr = explode('/', $depen);
							if(count($arr) == 4){
								if($this->acl->hasResource($arr[1].':'.$arr[2])){
									$this->acl->allow($user->getRole(), $arr[1].':'.$arr[2], $arr[3]);
								}
							}
						}
					}
				}
			}
		}


		return $this->acl;
	}


}