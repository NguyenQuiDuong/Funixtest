<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
            'User\Controller\Profile' => 'User\Controller\ProfileController',
            'User\Controller\Signin' => 'User\Controller\SigninController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'user' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/user',
                    'defaults' => array(
                        '__NAMESPACE__' => 'User\Controller',
                        'controller' => 'User\Controller\User',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'signin' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/signin',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action' => 'signin',
                            ),
                        ),
                    ),
                    'signout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/signout',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action' => 'signout',
                            ),
                        ),
                    ),
                    'signup' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/signup',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action' => 'signup',
                            ),
                        ),
                    ),
                    'active' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/active',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action' => 'active',
                            ),
                        ),
                    ),
                    'getactivecode' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getactivecode',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action' => 'getactivecode',
                            ),
                        ),
                    ),
                    'getpassword' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getpassword',
                            'defaults' => array(
                                'controller' => 'User\Controller\User',
                                'action' => 'getpassword',
                            ),
                        ),
                    ),
                ),

            ),
// End user router
// start manage router
        	'manage' => array(
        		'type' => 'Literal',
        		'options' => array(
        			'route' => '/user',
        			'defaults' => array(
        				'__NAMESPACE__' => 'User\Controller',
        				'controller' => 'Manage',
        				'action' => 'index',
        			),
        		),
        		'may_terminate' => true,
        		'child_routes' => array(
        			'default' => array(
        				'type' => 'Segment',
        				'options' => array(
        					'route' => '/[:controller[/:action]]',
        					'constraints' => array(
        						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
        						'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
        					),
        					'defaults' => array(
        					),
        				),
        			),
        		),
        	),
// End manage router

// Begin profile router
            'profile' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/profile',
                    'defaults' => array(
                        '__NAMESPACE__' => 'User\Controller',
                        'controller' => 'User\Controller\Profile',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'edit' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/edit',
                            'defaults' => array(
                                'controller' => 'User\Controller\Profile',
                                'action' => 'edit',
                            ),
                        ),
                    ),
                    'changepassword' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/changepassword',
                            'defaults' => array(
                                'controller' => 'User\Controller\Profile',
                                'action' => 'changepassword',
                            ),
                        ),
                    ),
                ),
            ),
// End profile router
// Begin signin router
            'signin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/signin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'User\Controller',
                        'controller' => 'User\Controller\Signin',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'facebook' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/facebook',
                            'defaults' => array(
                                'controller' => 'User\Controller\Signin',
                                'action' => 'facebook',
                            ),
                        ),
                    ),
                    'google' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/google',
                            'defaults' => array(
                                'controller' => 'User\Controller\Signin',
                                'action' => 'google',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'doctype' => 'HTML5',
        'exception_template' => 'error/index',
        'not_found_template' => 'error/404',
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
	'navigation' => array(
		'user' => array(
			array(
				'label'	=> 'Profile',
				'ico'	=> 'fa fa-user',
				'route'			=> 'profile',
				'controller'	=> 'profile',
				'action' 		=> 'index',
				'resource' 		=> 'user:profile',
				'privilege' 	=> 'index',
			),
			array(
				'label'	=> 'Đổi mật khẩu',
				'ico'	=> 'fa fa-tasks',
				'route'			=> 'profile/changepassword',
				'controller'	=> 'profile',
				'action' 		=> 'changepassword',
				'resource' 		=> 'user:profile',
				'privilege' 	=> 'index',
			),
		),
	),
);