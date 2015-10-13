<?php
return array(
    'db' => array(
        'driver'         => 'Pdo_Mysql',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
        ),
    ),
	'locale' => array(
		'default' => 'vi_VN',
		'supported' => array('vi_VN', 'en_US')
	),
	'app' => array(
		'session.tableName' => 'sessions'
	),
	'session' => array(
		'name' => 'erp4w6eytgdvsaddfbdvsdda',
		'remember_me_seconds' => 86400,
		'use_cookies'       => true,
		'cookie_httponly'   => true,
		'cookie_lifetime'   => 86400,
		'gc_maxlifetime'    => 86400,
		'save_path' => './data/session',
// 	    'cookie_domain' => '.'. str_replace('www.', '', $_SERVER['HTTP_HOST'])
	),
//     'service_manager' => array(
//         'factories' => array(
//             'Zend\Db\Adapter\Adapter' => 'Home\Service\DbAdapterFactory',
//         	'Zend\Db\Sql\Sql' => 'Home\Service\DbSqlFactory',
//         	'cache' => 'Home\Service\CacheFactory',
//             'log' => 'Home\Service\LogFactory',
//             'baseLog' => 'Home\Service\LogFactory',
//         ),
//     	'aliases' => array(
//     		'dbAdapter' => 'Zend\Db\Adapter\Adapter',
//     		'dbSql' => 'Zend\Db\Sql\Sql',
//     	)
//     ),
    'view_manager' => array(
		'display_not_found_reason' => false,
		'display_exceptions'       => false,
    	'doctype'                  => 'HTML5',
	),
	'smtpOptions' => array(
		'name'              => 'no-reply',
	    'host'              => 'smtp.gmail.com',
	    'port'              => 587,
	    'connection_class'  => 'login',
	    'connection_config' => array(
	    	'username' => 'no-reply@nhanh.vn',
	    	'password' => '0M1s-zQ3uakA',
	        'ssl'      => 'tls',
	    ),
	),
	'captcha' => array(
		'reCAPTCHA' => array(
			'domainName' => 'hotels.local',
			'publicKey' => '6LeKwMQSAAAAAOnwp1Tl7Z5J2ixvYPiNLBIPdZvu',
			'privateKey' => '6LeKwMQSAAAAAJSxtj5329MM-wL5ae-hfkR9jzAT'
		)
	),
);