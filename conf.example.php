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

/******************************  Configuration - General  *****************************/
define("BSIK_SET_DOMAIN", "http://localhost");
define("BSIK_IN_FOLDER",  "folder");

/************************** System Configuration & Trace ******************************/
define('PLAT_CHARSET',                      'utf-8');
define('PLAT_HASH_SALT',                    'xxxxx');
define('ERROR_METHOD',                      'inline'); // inline | redirect | hide
if (!defined('USE_BSIK_ERROR_HANDLERS')) define('USE_BSIK_ERROR_HANDLERS', true);

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
