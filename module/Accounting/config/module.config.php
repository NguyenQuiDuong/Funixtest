<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Accounting\Controller\Account' => 'Accounting\Controller\AccountController',
            'Accounting\Controller\Expense' => 'Accounting\Controller\ExpenseController',
            'Accounting\Controller\Transaction' => 'Accounting\Controller\TransactionController'

        )
    ),
    'router' => array(
        'routes' => array(
            'accounting' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/accounting',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Accounting\Controller',
                        'controller' => 'Account',
                        'Accounting',
                        'action' => 'index'
                    )
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '[/:controller][/:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ),
                            'defaults' => array(
                                '__NAMESPACE__' => 'Accounting\Controller',
                                'controller' => 'Account',
                                'action' => 'index'
                            )
                        )
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'accounting' => __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        )
    ),

    'navigation' => array(
        'accounting' => array(

            array(
                'label' => 'Danh sách tài khoản',
                'ico' => 'fa fa-bank',
                'route' => 'accounting/default',
                'controller' => 'account',
                'resource' => 'accounting:account',
                'privilege' => 'index',
                'pages' => array(
                    array(
                        'label' => 'Tài khoản',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-list',
                        'params' => array(
                            'controller' => 'account',
                            'action' => 'index'
                        ),
                        'resource' => 'accounting:account',
                        'privilege' => 'index'
                    ),

                    array(
                        'label' => 'Thêm tài khoản',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-plus',
                        'params' => array(
                            'controller' => 'account',
                            'action' => 'add'
                        ),
                        'resource' => 'accounting:account',
                        'privilege' => 'add'
                    ),
                    array(
                        'label' => 'Khởi tạo cơ bản cho cty mới',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-plus',
                        'params' => array(
                            'controller' => 'account',
                            'action' => 'initnewcompany'
                        ),
                        'resource' => 'accounting:account',
                        'privilege' => 'initnewcompany'
                    )
                )
            ),

            array(
                'label' => 'Khoản mục thu chi',
                'ico' => 'fa fa-usd',
                'route' => 'accounting',
                'controller' => 'repense',
                'resource' => 'accounting:expense',
                'privilege' => 'category',
                'pages' => array(
                    array(
                        'label' => 'Danh sách khoản mục',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-list',
                        'params' => array(
                            'controller' => 'expense',
                            'action' => 'category'
                        ),
                        'resource' => 'accounting:expense',
                        'privilege' => 'category'
                    ),
                    array(
                        'label' => 'Thêm khoản mục',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-plus',
                        'params' => array(
                            'controller' => 'expense',
                            'action' => 'addcategory'
                        ),
                        'resource' => 'accounting:expense',
                        'privilege' => 'addcategory'
                    ),
                )
            ),

            array(
                'label' => 'Thu chi',
                'ico' => 'fa fa-exchange',
                'route' => 'accounting/default',
                'controller' => 'transaction',
                'resource' => 'accounting:transaction',
                'privilege' => 'index',
                'pages' => array(
                    array(
                        'label' => 'Danh sách phiếu thu chi',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-list',
                        'params' => array(
                            'controller' => 'transaction',
                            'action' => 'index'
                        ),
                        'resource' => 'accounting:transaction',
                        'privilege' => 'index'
                    ),
                    array(
                        'label' => 'Lập phiếu xin chi',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-plus',
                        'params' => array(
                            'controller' => 'transaction',
                            'action' => 'addreqpayment'
                        ),
                        'resource' => 'accounting:transaction',
                        'privilege' => 'addreqpayment'
                    ),
                    array(
                        'label' => 'Lập phiếu xin thu',
                        'route' => 'accounting/default',
                        'ico' => 'fa fa-plus',
                        'params' => array(
                            'controller' => 'transaction',
                            'action' => 'addreqrecieve'
                        ),
                        'resource' => 'accounting:transaction',
                        'privilege' => 'addreqrecieve'
                    )
                )
            )
        )

    )

)
;