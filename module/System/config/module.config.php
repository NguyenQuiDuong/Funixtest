<?php
return array(
	'controllers' => array(
		'invokables' => array(
			'System\Controller\Index' => 'System\Controller\IndexController',
			'System\Controller\Feature' => 'System\Controller\FeatureController',
			'System\Controller\User' => 'System\Controller\UserController',
			'System\Controller\Tool' => 'System\Controller\ToolController',
			'System\Controller\Role' => 'System\Controller\RoleController',
			'System\Controller\Import' => 'System\Controller\ImportController',
			'System\Controller\Auto' => 'System\Controller\AutoController',
		    'System\Controller\Api' => 'System\Controller\ApiController',
		)
	),
	'router' => array(
		'routes' => array(
			'system' => array(
				'type' => 'Literal',
				'options' => array(
					'route' => '/system',
					'defaults' => array(
						'__NAMESPACE__' => 'System\Controller',
						'controller' => 'Index',
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
								'action' => 'index'
							),
						),
					),
				),
			)
		)
	),
	'view_manager' => array(
        'template_path_stack' => array(
            'system' => __DIR__ . '/../view',
        ),
		'template_map'        => array(
			'partial/dropdown'		=> __DIR__ . '/../view/system/partial/dropdown.phtml',
        ),
    ),
	'navigation' => array(
	    'system' => array(
	    	array(
	    		'label'	=> 'Danh sách user',
	    		'ico'	=> 'fa fa-users',
	    		'route'		=> 'system/default',
	    		'params' 	=> array(
	    			'controller'	=> 'user',
	    			'action'		=> 'index'
	    		),
	    		'resource' 	=> 'system:user',
	    		'privilege' => 'index',
	    	),
	    	array(
	    		'label'	=> 'Thêm user',
	    		'ico'	=> 'fa fa-plus',
	    		'route'		=> 'system/default',
	    		'params' 	=> array(
	    			'controller'	=> 'user',
	    			'action'		=> 'add'
	    		),
	    		'resource' 	=> 'system:user',
	    		'privilege' => 'add',
	    	),
	    	array(
	    		'label'	=> 'Phân quyền',
	    		'ico'	=> 'fa fa-sitemap',
	    		'route'		=> 'system/default',
	    		'params' 	=> array(
	    			'controller'	=> 'feature',
	    			'action'		=> 'mca'
	    		),
	    		'resource' 	=> 'system:feature',
	    		'privilege' => 'mca',
	    		'pages' => array(
	    			array(
	    				'label'	=> 'Hệ thống MCA',
	    				'ico'	=> 'fa fa-sitemap',
	    				'route'		=> 'system/default',
	    				'params' 	=> array(
	    					'controller'	=> 'feature',
	    					'action'		=> 'mca'
	    				),
	    				'resource' 	=> 'system:feature',
	    				'privilege' => 'mca',
	    			),
	    			array(
	    				'label'	=> 'Company Features',
	    				'ico'	=> 'fa fa-building',
	    				'route'		=> 'system/default',
	    				'params' 	=> array(
	    					'controller'	=> 'feature',
	    					'action'		=> 'company'
	    				),
	    				'resource' 	=> 'system:feature',
	    				'privilege' => 'company',
	    			),
	    			array(
	    				'label'	=> 'Chức danh hệ thống',
	    				'ico'	=> 'fa fa-book',
	    				'route'		=> 'system/default',
	    				'params' 	=> array(
	    					'controller'	=> 'role',
	    					'action'		=> 'index'
	    				),
	    				'resource' 	=> 'system:role',
	    				'privilege' => 'index',
	    			),
	    			array(
	    				'label'	=> 'Role Features',
	    				'ico'	=> 'fa fa-users',
	    				'route'		=> 'system/default',
	    				'params' 	=> array(
	    					'controller'	=> 'feature',
	    					'action'		=> 'role'
	    				),
	    				'resource' 	=> 'system:feature',
	    				'privilege' => 'role',
	    			),

				)
	    	),
        ),
	),
);