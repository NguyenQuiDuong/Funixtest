<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace Website\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class Template implements ListenerAggregateInterface, ServiceLocatorAwareInterface
{
    const SESSION_VISITOR = 'visitor';

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var \Store\Service\Store
     */
    protected $serviceStore;

    /**
     * @var \Website\Model\Domain
     */
    protected $domain;

    /**
     * @var \Website\Model\Template
     */
    protected $template;

    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @return \Store\Service\Store
     */
    public function getServiceStore()
    {
        return $this->getServiceLocator()->get('Store\Service\Store');
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $e)
    {
        $config = $this->getServiceLocator()->get('Config');
        if (isset($config['db']['profilerEnabled']) && $config['db']['profilerEnabled']) {
            $this->listeners[] = $e->attach(MvcEvent::EVENT_FINISH, array($this, 'onFinish'), -2000);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $e)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($e->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Event callback to be triggered on finish for
     * profiling and logging sql queries
     *
     * @author VanCK
     * @return void
     */
    public function onFinish()
    {
        /* @var $profiler \Zend\Db\Adapter\Profiler\Profiler */
        $profiler = $this->getServiceLocator()->get('dbAdapter')->getProfiler();
        $profiles = $profiler->getProfiles();

        $totalQueries = 0;
        $totalTime = 0;
        foreach ($profiles as $profile) {
            $totalQueries++;
            $totalTime += $profile['elapse'];
        }

        $totalTime = round($totalTime, 5);
        /* @var $log \Zend\Log\Logger */
        $log = $this->getServiceLocator()->get('log');
        $log->info("$totalQueries queries ($totalTime)");
        $i = 1;
        foreach ($profiles as $profile) {
            $totalTime += $profile['elapse'];
            $sql = $profile['sql'];
            if (isset($profile['parameters'])) {
                /* @var $parameters \Zend\Db\Adapter\ParameterContainer */
                $parameters = $profile['parameters'];
                foreach ($parameters->getNamedArray() as $position => $data) {
                    $sql = str_replace(':' . $position, $data, $sql);
                }
            }
            $log->info($i++ . ' ~ ' . round($profile['elapse'], 5) . ' ' . $sql);
        }
    }
}