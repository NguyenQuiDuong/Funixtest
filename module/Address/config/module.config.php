<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Address\Controller\Address' => 'Address\Controller\AddressController',
        	'Address\Controller\City' => 'Address\Controller\CityController',
        	'Address\Controller\District' => 'Address\Controller\DistrictController',
        	'Address\Controller\Country'=> 'Address\Controller\CountryController'
        )
    ),
    'router' => array(
        'routes' => array(
        	'address' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/address',
                    'defaults' => array(
                    	'__NAMESPACE__' => 'Address\Controller',
                        'controller' => 'Address\Controller\Address',
                        'action'     => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '[/:controller][/:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Address\Controller',
                                'controller' => 'Address\Controller\Address',
                                'action'     => 'index'
                            )
                        )
                    ),
                    'book' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/book',
                            'defaults' => array(
                                'controller' => 'Address\Controller\Address',
                                'action'     => 'book'
                            )
                        )
                    ),
                    'ajaxaddbook' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/ajaxaddbook',
                            'defaults' => array(
                                'controller' => 'Address\Controller\Address',
                                'action'     => 'ajaxaddbook'
                            )
                        )
                    ),
                    'addbook' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/addbook',
                            'defaults' => array(
                                'controller' => 'Address\Controller\Address',
                                'action'     => 'addbook'
                            )
                        )
                    ),
                    'editbook' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/editbook[/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+'
                            ),
                            'defaults' => array(
                                'controller' => 'Address\Controller\Address',
                                'action'     => 'editbook'
                            )
                        )
                    ),
                    'removebook' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route'    => '/removebook',
                            'defaults' => array(
                                'controller' => 'Address\Controller\Address',
                                'action'     => 'removebook'
                            )
                        )
                    )
				)
        	)
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'address' => __DIR__ . '/../view'
        ),
	    'strategies'      => array(
	        'ViewJsonStrategy'
	    )
    ),
);