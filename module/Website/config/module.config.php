<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Website\Controller\Template' => 'Website\Controller\TemplateController',
        	'Website\Controller\Domain' => 'Website\Controller\DomainController'
        ),
    ),
    'router' => array(
        'routes' => array(
            'website' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/website[/:controller][/:action]',
                    'constraints' => array(
						'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                    	'__NAMESPACE__' => 'Website\Controller',
                        'controller' => 'Website\Controller\Template',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'website' => __DIR__ . '/../view',
        ),
    ),
);