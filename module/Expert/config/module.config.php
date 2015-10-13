<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Expert\Controller\Index' => 'Expert\Controller\IndexController',

        )
    ),
    'router' => array(
        'routes' => array(
            'expert' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/experts',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Expert\Controller',
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
        'Expert' => array(
            'default' => 'layout/layout'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'document' => __DIR__ . '/../view'
        ),

    ),
);