<?php

define("BUILD_ON",           "PHP 8.2");
define("BSIK_APP_VERSION",   "1.2.1");

if (!defined('DS')) 
    define('DS', DIRECTORY_SEPARATOR);

    if (!defined('ROOT_PATH')) 
    define("ROOT_PATH", dirname(__FILE__));

if (!defined('BSIK_AUTOLOAD')) 
    define("BSIK_AUTOLOAD", ROOT_PATH.DS."vendor".DS."autoload.php");

require_once ROOT_PATH.DS."conf.php"; // Conf..
require_once BSIK_AUTOLOAD;

use \Siktec\Bsik\CoreSettings;
use \Siktec\Bsik\Exceptions\BsikUseExcep;

BsikUseExcep::init();
CoreSettings::init();
