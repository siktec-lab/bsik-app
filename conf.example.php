<?php
/******************************************************************************/
// Created by: shlomo hassid.
// Release Version : 1.0.1
// Creation Date: 10/05/2020
// Copyright 2020, shlomo hassid.
/******************************************************************************/
/*****************************      Changelog       ****************************
1.0.1:
    ->Creation, initial.
*******************************************************************************/

/************************** System Configuration & Trace ******************************/
define('PLAT_CHARSET',                      'utf-8');
define('PLAT_HASH_SALT',                    'xxxxx');
if (!defined('USE_BSIK_ERROR_HANDLERS'))
    define('USE_BSIK_ERROR_HANDLERS',       true);
define('ERROR_METHOD',                      'inline'); // inline | redirect | hide

/******************************  headers and ini  *****************************/
header('Content-Type: text/html; charset='.PLAT_CHARSET);
ini_set('log_errors', true);
error_reporting(-1); // -1 all, 0 don't

/******************************  Configuration - DataBase  *****************************/
$conf = [];
//Insert your db credentials:
$conf["db"] = [
    'host'   => 'localhost',
    'port'   => '3306',
    'name'   => 'bsik',
    'user'   => 'xxxxxxx',
    'pass'   => 'xxxxxxx'
];