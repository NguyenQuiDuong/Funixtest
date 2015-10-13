<?php
/**
 * @category   	ERP library
 * @copyright  	http://erp.nhanh.vn
 * @license    	http://erp.nhanh.vn/license
 */
namespace Home\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\FirePhp as FirePhpWriter;

require_once 'FirePHPCore\FirePHP.class.php';

class LogFactory implements FactoryInterface
{

    /**
     *
     * @author VanCK
     * @param ServiceLocatorInterface $sl
     * @return \Zend\Cache\StorageFactory
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $log = new Logger();
        $writer = new FirePhpWriter();
        $log->addWriter($writer);
        return $log;
    }
}