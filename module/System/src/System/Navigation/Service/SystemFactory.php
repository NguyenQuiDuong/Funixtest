<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace System\Navigation\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

class SystemFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'system';
    }
}