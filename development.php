<?php
/**
 *
 */

error_reporting(E_ALL);

$ldpb = '5e4ryhdgbvaq3w5e6rtyufhgbd';
$dbProfilerEnabled = false;

if(isset($_GET['ldbp']) && $_GET['ldbp'] == $ldpb) {
    $dbProfilerEnabled = true;
    setcookie('ldbp', $ldpb, time()+60*60*24*365, '/');
} else if(isset($_COOKIE['ldbp']) && $_COOKIE['ldbp'] == $ldpb) {
    $dbProfilerEnabled = true;
}

return array(
    'db'           => array(
        'dsn'             => 'mysql:dbname=mentor;host=127.0.0.1:3306',
        'username'        => 'root',
        'password'        => '',
        'profilerEnabled' => true,
        'profilerIps'     => '127.0.0.1'
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
    ),
    
);