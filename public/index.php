<?php

// if(!isset($_GET['test'])) {
//     die("Hệ thống đang nâng cấp, xin vui lòng thử lại sau ít phút nữa");
// }

if (function_exists('header_remove')) { // PHP 5.3+
	header_remove('X-Powered-By');
	header_remove('Server');
} else {
	@ini_set('expose_php', 'off');
}

date_default_timezone_set('Asia/Saigon');

list($usec, $sec) = explode(" ", microtime());
global $beginTime;
$beginTime = ((float)$usec + (float)$sec);

// error handler function
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if(getenv('APPLICATION_ENV') != 'production') {
        return true;
    }
    $file = fopen(dirname(dirname(__FILE__)) . "/data/logs/_erp_err.log", "a");
    fwrite($file, date('Y-m-d H:i:s') ." == ");
    fwrite($file, ' IP: ' . getenv('REMOTE_ADDR') . ' == ');
    fwrite($file, (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $_SERVER['REQUEST_URI'] ." == ");
    fwrite($file, getenv('HTTP_USER_AGENT') ."\n");
    fwrite($file, 'ErrNo: '. $errno .' - ErrFile: '. $errfile .' - ErrLine: '. $errline . "\n");
    fwrite($file, $errstr);
    fwrite($file, "\n\n");
    fclose($file);

    /* Don't execute PHP internal error handler */
    return false;
}

set_error_handler("myErrorHandler");

if(getenv('APPLICATION_ENV') != 'production' && function_exists('opcache_reset')) {
	opcache_reset();
}

function vdump($data = null) {
	if(in_array(getenv('APPLICATION_ENV'), ['development', 'localhost'])) {
		echo "<pre>";
		var_dump($data);
		echo "</pre>";
	}
}

// redirect www to non www
if (substr(strtolower($_SERVER['HTTP_HOST']), 0, 4) === 'www.') {
    $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https://' : 'http://';
    $host .= substr($_SERVER['HTTP_HOST'], 4);
    if ($_SERVER["SERVER_PORT"] != '80') {
        $host .= ':' . $_SERVER['SERVER_PORT'];
    }
    header('Location: '. $host . $_SERVER['REQUEST_URI']);
    exit;
}

// force https
//if(getenv('APPLICATION_ENV') == 'production' && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off')) {
//	header('Location: http://mentor.funix.edu.vn'. $_SERVER['REQUEST_URI']);
//	exit;
//}

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('BASE_PATH') || define('BASE_PATH', dirname(dirname(__FILE__)));
defined('LIB_PATH') || define('LIB_PATH', getenv("LIB_PATH"));
defined('VENDOR_PATH') || define('VENDOR_PATH', realpath(BASE_PATH .'/vendor'));
defined('TEMPLATES_PATH') || define('TEMPLATES_PATH', realpath(BASE_PATH .'/public/tp'));
defined('MEDIA_PATH') || define('MEDIA_PATH', realpath(BASE_PATH .'/public/media'));
set_include_path(implode(
	PATH_SEPARATOR,
	array(
		LIB_PATH,
		VENDOR_PATH,
		get_include_path()
	)
));

$zendPath = in_array(getenv('APPLICATION_ENV'), ['development', 'localhost']) ? LIB_PATH : VENDOR_PATH;
/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

include 'Zend/Loader/AutoloaderFactory.php';
Zend\Loader\AutoloaderFactory::factory(array(
	'Zend\Loader\StandardAutoloader' => array(
		'namespaces' => array(
			'Zend'     	=> $zendPath . '/Zend',
			'ZendX'		=> VENDOR_PATH . '/ZendX',
			'ZendService'		=> $zendPath . '/ZendService'
		),
	)
));
Zend\Mvc\Application::init(require 'config/application.config.php')->run();