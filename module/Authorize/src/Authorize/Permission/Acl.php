<?php
/**
 * @Acl            Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace Authorize\Permission;

use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\Permissions\Acl\Role\GenericRole as Role;

class Acl extends ZendAcl
{

    public function __construct()
    {
        $this->addRole(new Role('Guest'));
        $this->addRole(new Role('Member'), 'Guest');
        $this->addRole(new Role('Mentor'), 'Guest');

        $this->addRole(new Role('Admin'), 'Member');
        $this->addRole(new Role('Super Admin'), 'Member');

        $this->addResource('home:index');
        $this->addResource('home:media');
        
        $this->addResource('admin:index');
        $this->addResource('admin:subject');
        $this->addResource('admin:user');
        $this->addResource('admin:expert');

        $this->addResource('expert:index');

        $this->addResource('subject:subject');

        $this->addResource('address:district');
        $this->addResource('address:city');
        $this->addResource('user:user');
        $this->addResource('user:profile');
        $this->addResource('user:signin');
        $this->addResource('user:manage');

        $this->addResource('system:index');
        $this->addResource('system:feature');
        $this->addResource('system:user');
        $this->addResource('system:tool');
        $this->addResource('system:role');
        $this->addResource('system:import');
        $this->addResource('system:auto');
        $this->addResource('system:api');



        $this->allow('Guest', 'home:index');
        $this->allow('Guest', 'home:media');
        $this->allow('Guest', 'address:district', ['load']);
        $this->allow('Guest', 'address:city', ['load']);
        $this->allow('Guest', 'user:user', ['signin', 'signout', 'signup',
        		 'active', 'getactivecode', 'getpassword', 'ajaxsignup', 'ajaxsignin', 'resetpassword']);
        $this->allow('Guest', 'user:signin', ['index', 'google', 'facebook']);
        $this->allow('Guest', 'user:manage');
        // deploy code
        $this->allow('Guest', 'system:tool', ['resetopcache']);
        $this->allow('Guest', 'expert:index');

        $this->allow('Guest', 'subject:subject',['suggest']);

        $this->allow('Member', 'user:profile');
        $this->allow('Member', 'user:user', ['updatecode']);
        $this->allow('Member', 'system:user', ['suggest']);

        $this->allow('Admin', null);
        $this->allow('Super Admin', null);
    }
    
	private function loadPrivilege()
	{

	}

    /**
     * @return array
     */
    public function getDependencies() {
		return array(
			);
    }
}