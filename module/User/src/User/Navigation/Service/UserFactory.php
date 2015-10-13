<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace User\Navigation\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

class UserFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'user';
    }
}