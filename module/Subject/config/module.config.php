<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Subject\Controller\Subject' => 'Subject\Controller\SubjectController',

        )
    ),
    'router' => array(
        'routes' => array(
            'subject' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/subject',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Subject\Controller',
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
        'Subject' => array(
            'default' => 'layout/layout'
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'document' => __DIR__ . '/../view'
        ),


    ),
);