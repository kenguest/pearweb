<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/
require_once "pear-config.php";
if ($_SERVER['SERVER_NAME'] != PEAR_CHANNELNAME) {
    error_reporting(E_ALL);
    define('DEVBOX', true);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
    define('DEVBOX', false);
}

require_once "PEAR.php";

if (empty($format)) {
    if (basename(htmlspecialchars($_SERVER['PHP_SELF'])) == "xmlrpc.php") {
        $format = 'xmlrpc';
    } else {
        $format = 'html';
    }
}

include_once "pear-format-$format.php";

include_once "DB.php";
include_once "DB/storage.php";
include_once "pear-auth.php";
include_once "pear-database.php";
include_once "pear-rest.php";

function get($name)
{
    if (!empty($_GET[$name])) {
        return $_GET[$name];
    } else if (!empty($_POST[$name])) {
        return $_POST[$name];
    } else {
        return "";
    }
}

if (empty($dbh)) {
    $options = array(
        'persistent' => false,
        'portability' => DB_PORTABILITY_ALL,
    );
    $dbh =& DB::connect(PEAR_DATABASE_DSN, $options);
}
if (!isset($pear_rest)) {
    if (!DEVBOX) {
        $pear_rest = new pear_rest('/var/lib/pearweb/rest');
    } else {
        $pear_rest = new pear_rest(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'public_html' .
            DIRECTORY_SEPARATOR . 'rest');
    }
}

$tmp = filectime($_SERVER['SCRIPT_FILENAME']);
$LAST_UPDATED = date('D M d H:i:s Y', $tmp - date('Z', $tmp)) . ' UTC';

if (!empty($_GET['logout']) && $_GET['logout'] === '1') {
    auth_logout();
}

if (!empty($_COOKIE['PEAR_USER']) && !@auth_verify($_COOKIE['PEAR_USER'], $_COOKIE['PEAR_PW'])) {
    $__user = $_COOKIE['PEAR_USER'];
    setcookie('PEAR_USER', '', 0, '/');
    unset($_COOKIE['PEAR_USER']);
    setcookie('PEAR_PW', '', 0, '/');
    unset($_COOKIE['PEAR_PW']);
    $msg = "Invalid username ($__user) or password";
    if ($format == 'html') {
        $msg .= " <a href=\"/?logout=1\">[logout]</a>";
    }
    auth_reject(null, $msg);
}

if (!function_exists('file_get_contents')) {
    function file_get_contents($file, $use_include_path = false) {
        if (!$fp = fopen($file, 'r', $use_include_path)) {
            return false;
        }
        $data = fread($fp, filesize($file));
        fclose($fp);
        return $data;
    }
}

if (!function_exists('file_put_contents')) {
    function file_put_contents($fname, $contents)
    {
        $fp = fopen($fname, 'wb');
        fwrite($fp, $contents);
        fclose($fp);
    }
}

