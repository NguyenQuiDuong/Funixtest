<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index' => 'Admin\Controller\IndexController',
            'Admin\Controller\Subject' => 'Admin\Controller\SubjectController',
            'Admin\Controller\User'     =>  'Admin\Controller\UserController',
            'Admin\Controller\Expert'   =>  'Admin\Controller\ExpertController'

        )
    ),
    'router' => array(
        'routes' => array(
            'admin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array()
                        )
                    )
                )
            )
        )
    ),
    'module_layouts' => array(
        'Admin' => array(
            'default' => 'admin/layout/layout'
        )
    ),
    'view_manager'    => array(
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
//         'template_map'        => array(
//             'site/layout' => __DIR__ . '/../view/layout/layout.phtml',
//         ),
    ),
    'service_manager' => array(
        'factories' => array(
            'navigationadmin' => 'Zend\Navigation\Service\DefaultNavigationFactory'
        )
    ),
    
    'navigation' => array(
        'default' => array(
            array(
                'label'	=> 'Người dùng',
                'route'		=> 'user',
                'resource' 	=> 'user:index',
                'privilege' => 'index',
            ),
            array(
                'label'	=> 'Môn học',
                'route'		=> 'subject',
                'resource' 	=> 'subject:index',
                'privilege' => 'index',
            ),
        ),
    ),
);