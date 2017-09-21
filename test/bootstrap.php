<?php
/*
 * This file is part of the Engine framework.
 * (c) Mathias Methner <mathiasmethner@gmail.com>
 * Please view the LICENSE file
 */
error_reporting(-1);
ini_set('display_errors', 'On');

define('ENGINE_APP_ROOT', realpath(dirname(__FILE__)));

require_once(ENGINE_APP_ROOT . '/../vendor/autoload.php');
require_once(ENGINE_APP_ROOT . '/../src/autoload.php');

define('SID', 'PHPSESSID=');

$_SERVER['SERVER_NAME'] = 'phpunit';

new \Engine\Core\Config(ENGINE_APP_ROOT);
