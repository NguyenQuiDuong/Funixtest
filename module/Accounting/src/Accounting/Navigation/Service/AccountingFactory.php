<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */

namespace Accounting\Navigation\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

class AccountingFactory extends DefaultNavigationFactory
{
    protected function getName()
    {
        return 'accounting';
    }
}