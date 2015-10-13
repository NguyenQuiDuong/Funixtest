<?php
/**
 * @category       Shop99 library
 * @copyright      http://shop99.vn
 * @license        http://shop99.vn/license
 */

namespace User;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                'User\Model\User'                    => 'User\Model\User',
                'User\Model\UserMapper'              => 'User\Model\UserMapper',
                'User\Model\OperatingSystem'         => 'User\Model\OperatingSystem',
                'User\Model\OperatingSystemMapper'   => 'User\Model\OperatingSystemMapper',
                'User\Model\Browser'                 => 'User\Model\Browser',
                'User\Model\BrowserMapper'           => 'User\Model\BrowserMapper',
                'User\Model\TrafficSource'           => 'User\Model\TrafficSource',
                'User\Model\TrafficSourceMapper'     => 'User\Model\TrafficSourceMapper',
                'User\Form\Signin'                   => 'User\Form\Signin',
                'User\Form\SigninFilter'             => 'User\Form\SigninFilter',
                'User\Form\Signup'                   => 'User\Form\Signup',
                'User\Form\SignupFilter'             => 'User\Form\SignupFilter',
                'User\Form\ChangePassword'           => 'User\Form\ChangePassword',
                'User\Form\ChangePasswordFilter'     => 'User\Form\ChangePasswordFilter',
                'User\Form\GetActiveCode'            => 'User\Form\GetActiveCode',
                'User\Form\GetActiveCodeFilter'      => 'User\Form\GetActiveCodeFilter',
                'User\Controller\Plugin\UserFactory' => 'User\Controller\Plugin\UserFactory',
                'User\Service\UserFactory'           => 'User\Service\UserFactory',
                'User\Service\AuthFactory'           => 'User\Service\AuthFactory',
                'User\Service\GoogleLogin'           => 'User\Service\GoogleLogin',
                'User\Service\FacebookLogin'         => 'User\Service\FacebookLogin',
                'User\View\Helper\UserFactory'       => 'User\View\Helper\UserFactory'
            ),
            'factories'  => array(
                'User\Service\User' => 'User\Service\UserFactory',
                'User\Service\Auth' => 'User\Service\AuthFactory',
            	'userNavigation' => 'User\Navigation\Service\UserFactory',
            ),
        );
    }

    public function getControllerPluginConfig()
    {
        return array(
            'factories' => array(
                'User' => 'User\Controller\Plugin\UserFactory'
            ),
        );
    }

    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'User' => 'User\View\Helper\UserFactory',
            )
        );
    }
}