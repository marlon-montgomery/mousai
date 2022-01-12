<?php

define('MINIMUM_VERSION', '7.3');
if ( ! version_compare(PHP_VERSION, MINIMUM_VERSION)) exit('You need at least PHP '.MINIMUM_VERSION.' to install this application.');

/*
 * Check for JSON extension
 */
if (!function_exists('json_decode')) {
    exit('JSON PHP Extension is required in order to install. You should be able to enable it from cpanel in most cases.');
}

/*
 * PHP headers
 */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

/*
 * Debug mode
 */
$isDebug = array_key_exists('debug', $_REQUEST);

if ($isDebug) {
    @ini_set('display_errors', 1);
    @ini_set('display_startup_errors', 1);
    error_reporting(1);
}
else {
    @ini_set('display_errors', 0);
    error_reporting(0);
}

/*
 * Constants
 */
define('PATH_INSTALL', str_replace("\\", "/", realpath(dirname(__FILE__)."/../../../")));

/*
 * Address timeout limits
 */
@set_time_limit(3600);
@ini_set('memory_limit', '-1');

/*
 * Prevent PCRE engine from crashing
 */
@ini_set('pcre.recursion_limit', '524'); // 256KB stack. Win32 Apache

/*
 * Handle fatal errors with AJAX
 */
register_shutdown_function('installerShutdown');
function installerShutdown()
{
    global $installer;
    $error = error_get_last();
    if (isset($error['type']) && in_array($error['type'], [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE])) {
        header('HTTP/1.1 500 Internal Server Error');
        $errorMsg = htmlspecialchars_decode(strip_tags($error['message']));
        echo $errorMsg;
        if (isset($installer)) {
            $installer->log('Fatal error: %s on line %s in file %s', $errorMsg, $error['line'], $error['file']);
        }
        exit;
    }
}

/*
 * Bootstrap the installer
 */
require_once 'InstallerException.php';
require_once 'Installer.php';

try {
    $installer = new Installer();
    $installer->startNewLogSection();
    $installer->log('Host: %s', php_uname());
    $installer->log('PHP version: %s', PHP_VERSION);
    $installer->log('Server software: %s', isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown');
    $installer->log('Operating system: %s', PHP_OS);
    $installer->log('Memory limit: %s', ini_get('memory_limit'));
    $installer->log('Max execution time: %s', ini_get('max_execution_time'));
}
catch (Exception $ex) {
    $fatalError = $ex->getMessage();
}
