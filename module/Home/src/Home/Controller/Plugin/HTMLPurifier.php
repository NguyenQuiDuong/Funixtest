<?php
/**
 * @author 		VanCK
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class HTMLPurifier extends AbstractPlugin
{
    /**
     * @author VanCK
     * @param string $value
     * @return string
     */
    public function __invoke($value)
    {
    	$HTMLPurifier = new \Home\Filter\HTMLPurifier();
    	return $HTMLPurifier->filter($value);
    }
}