<?php
return array(
    'controllers'     => array(
        'invokables' => array(
        	'Home\Model\Consts'    	   	=> 'Home\Model\Consts',
            'Home\Controller\Index'    	=> 'Home\Controller\IndexController',
            'Home\Controller\Search'   	=> 'Home\Controller\SearchController',
            'Home\Controller\Searchs'  	=> 'Home\Controller\SearchsController',
            'Home\Controller\Loadview' 	=> 'Home\Controller\LoadviewController',
        	'Home\Controller\Media' 	=> 'Home\Controller\MediaController',
        )
    ),
    'router'          => array(
        'routes' => array(
            'home'      => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults'    => array(
                                'controller' => 'Index',
                                'action'     => 'index'
                            )
                        )
                    )
                )
            ),
            'homeAlias' => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/home',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults'    => array(
                                'controller' => 'Index',
                                'action'     => 'index'
                            )
                        )
                    )
                )
            ),
            // Search router
            'search'    => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/search',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Search',
                        'action'        => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'suggestion' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/suggestion',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Home\Controller',
                                'controller'    => 'Search',
                                'action'        => 'suggestion'
                            )
                        )
                    ),
                    'noresult'   => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/noresult',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Home\Controller',
                                'controller'    => 'Search',
                                'action'        => 'noresult'
                            )
                        )
                    ),
                    'default'    => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:action]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Home\Controller',
                                'controller'    => 'Search',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults'    => array()
                        )
                    )
                )
            ),
            'searchs'   => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/searchs',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Searchs',
                        'action'        => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'suggestion' => array(
                        'type'    => 'Literal',
                        'options' => array(
                            'route'    => '/suggestion',
                            'defaults' => array(
                                '__NAMESPACE__' => 'Home\Controller',
                                'controller'    => 'Searchs',
                                'action'        => 'suggestion'
                            )
                        )
                    ),
                    'default'    => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:action]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Home\Controller',
                                'controller'    => 'Searchs',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults'    => array()
                        )
                    )
                )
            ),
            // Loadview router
            'loadview'  => array(
                'type'          => 'Literal',
                'options'       => array(
                    'route'    => '/loadview',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Home\Controller',
                        'controller'    => 'Home\Controller\Loadview',
                        'action'        => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes'  => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '[/:action]',
                            'constraints' => array(
                                '__NAMESPACE__' => 'Home\Controller',
                                'controller'    => 'Home\Controller\Loadview',
                                'action'        => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults'    => array()
                        )
                    )
                )
            ),
        )
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        	'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory'
        )
    ),
    'translator'      => array(
        'locale'                    => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo'
            )
        )
    ),
    'view_manager'    => array(
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'template_map'        => array(
            'empty'       => __DIR__ . '/../view/layout/emptylayout.phtml',
            'site/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
            'error/403'   => __DIR__ . '/../view/error/403.phtml',
        	'error/404'   => __DIR__ . '/../view/error/404.phtml',
        	'partial/formInput'		=> __DIR__ . '/../view/home/partial/formInput.phtml',
        	'partial/formFilter'	=> __DIR__ . '/../view/home/partial/formFilter.phtml',
        	'partial/dataGrid'		=> __DIR__ . '/../view/home/partial/dataGrid.phtml',
        	'partial/moduleMenu'	=> __DIR__ . '/../view/home/partial/moduleMenu.phtml',
        	'partial/paginatorItem' => __DIR__ . '/../view/home/partial/paginatorItem.phtml',
        	'partial/loliFilter'	=> __DIR__ . '/../view/home/partial/loliFilter.phtml',
            'partial/my-form-input' => __DIR__. '/../view/home/partial/my-form-input.phtml',
        ),
        'exception_template'  => 'error/index',
        'not_found_template'  => 'error/404'
    ),
    'strategies'      => array(
        'ViewJsonStrategy'
    ),
//     'navigation' => array(
//         'default' => array(
//             array(
//                 'label'	=> 'Người dùng',
//                 'route'		=> 'user',
//                 'resource' 	=> 'user:user',
//                 'privilege' => 'index',
//             ),
//             array(
//                 'label'	=> 'Môn học',
//                 'route'		=> 'subject',
//                 'resource' 	=> 'subject:subject',
//                 'privilege' => 'index',
//             ),
//         ),
//     ),
);