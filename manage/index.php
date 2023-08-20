<?php
/******************************************************************************/
// Created by: shlomo hassid.
// Release Version : 1.0.1
// Creation Date: 10/05/2020
// Copyright 2020, shlomo hassid.
/******************************************************************************/
/*****************************      Changelog       ****************************
1.0.1:
    ->Creation - Initial
            
*******************************************************************************/
define('USE_BSIK_ERROR_HANDLERS', true);

/******************************  Requires       *****************************/
require_once '..'.DIRECTORY_SEPARATOR.'bsik.php';

use \Siktec\Bsik\Std;
use \Siktec\Bsik\Trace;
use \Siktec\Bsik\CoreSettings;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Base;
use \Siktec\Bsik\Privileges as Priv;
use \Siktec\Bsik\Users\User;
use \Siktec\Bsik\Render\Pages\AdminPage;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\Impl\CoreLoader;

Trace::add_step(__FILE__, "Controller - manage index");


/*********************  Load Conf and DataBase  *****************************/
Base::configure($conf);
Trace::add_trace("Loaded Base Configuration Object",__FILE__, $conf);
Base::connect_db();
Trace::add_trace("Establish db connection",__FILE__);

/******************************************************************************/
/*********************  Add secondary connection ******************************/
/******************************************************************************/
// Base::add_db_connection("shared", $conf["db-catalog"]);
// Trace::add_trace("Establish shared db connection",__FILE__);

/******************************************************************************/
/*********************  LOAD CORE SETTINGS  ***********************************/
/******************************************************************************/
if (!CoreSettings::extend_from_database(Base::$db)) {
    throw new Exception("Cant Load Settings", E_PLAT_ERROR);
}

//Core settings:
CoreSettings::load_constants();

//Set object defaults:
Trace::$enable = CoreSettings::get("trace-debug-expose", false);
Base::$db->setTrace(Trace::$enable);
Template::$default_debug      = CoreSettings::get("template-rendering-debug-mode", false);
Template::$default_autoreload = CoreSettings::get("template-rendering-auto-reload", true);

//Start session:
if(!session_id()){ session_start(); }

/******************************  Load Admin      *****************************/
$User = new User();
Trace::add_trace("Loaded User Object", __FILE__);

/******************************  User login / logout   *****************************/
//Check user signed or not:
$User->user_login();
$User->initial_user_login_status();
Trace::reg_vars(["User signed" => $User->is_signed]);
Trace::add_trace("User login status",__FILE__, $User->user_data);
Trace::reg_vars(["User granted privileges" => $User->priv->all_granted(true)]);

/******************************  Load Modules And Page Controller *****************************/
AdminPage::set_user_string($User->user_identifier());
AdminPage::tokenize();
AdminPage::load_request($_REQUEST ?? []);

//Initialize controller:
$APage = new AdminPage(
    enable_logger       : true, 
    logger_channel      : AdminPage::$request->type == "api" ? "aapi-manage" : "apage-general",
    issuer_privileges   : $User->priv
);

//Initialize Api:
$AApi = new AdminApi(
    csrf                : AdminPage::csrf(), // CSRF TOKEN
    debug               : CoreSettings::get("api-responses-with-debug-info", false),    // Operation Mode
    issuer_privileges   : $User->priv
);

//------

Trace::add_trace("Loaded AdminPage object", __FILE__, ["token" => AdminPage::$token]);
Trace::reg_vars(["Request"            => AdminPage::$request->get()]);
Trace::reg_vars(["Requested module"   => AdminPage::$request->module]);
Trace::reg_vars(["Requested which"    => AdminPage::$request->which]);
Trace::reg_vars(["Available modules"  => AdminPage::$modules->get_all_installed()]);

/******************************  Core Includes      *****************************/
CoreLoader::load_all();

/***************************  Required Privileges  *****************************/
$access_policy = new Priv\RequiredPrivileges();
$access_policy->define(new Priv\Default\PrivAccess(
    manage : true
));

/******************************  Build Page      *****************************/
Trace::add_step(__FILE__,"Loading and building page:");
switch (AdminPage::$request->type) {

    case "module": {

        Trace::add_trace("Module type detected", __FILE__);

        
        //Must be signed in and have access :
        if (!$User->is_signed) {
            Trace::add_trace("Module requires User to be signed in - redirecting", __FILE__);
            require_once CoreSettings::$path["manage-pages"].DS."login.php";
            
        }
        elseif (!$User->priv->has_privileges($access_policy)) {
            Trace::add_trace("No privileges to access manage panel", __FILE__);
            $User->errors["login"] = "privileges";
            require_once CoreSettings::$path["manage-pages"].DS."login.php";
        }

        //Make sure Module Exists:
        elseif (!$APage->is_module_installed()) {
            Trace::add_trace("Requested module is not set", __FILE__);
            $APage::error_page(404); // No module
        }
        
        //Make sure exists and is allowed:
        //TODO: move this to render block so we can return the good error in body:
        // elseif (false && !$APage->is_allowed_to_use_module($User)) {
        //     Trace::add_trace("Module requires privileges that user does not have", __FILE__);
        //     $APage::error_page(403); // Forbiden
        
        //Everything is fine -> render the page:
        else {

            Trace::add_trace("User check successfully - signed, is-set, is-allowed", __FILE__);

            //Load global core settings:
            $APage->load_settings("global", true);
            
            //Load requested module:
            if (!$APage->load_module(Api : $AApi, User: $User)) {
                Trace::add_trace("Module could not be loaded", __FILE__);
                $APage::error_page(404); // No module
            }

            //-----
            Trace::add_trace("Module loaded to APage.", __FILE__);
            if (!isset($APage::$module) || is_null($APage::$module)) {
                Trace::add_trace("Module is not set", __FILE__);
            } else {
                Trace::add_trace("Loaded paths", __FILE__, $APage::$module->paths);
                Trace::reg_vars(["Loaded module" => $APage::$module]);
                Trace::reg_vars(["Loaded Page settings (extended)" => $APage::$module->settings]);
                Trace::reg_var("Loaded Module settings (extended)", $APage::$module->settings->get_all(true));
                Trace::reg_var("Loaded View settings", $APage::$module->current_view->settings->get_all(true));
            }

            //-----
            
            require_once CoreSettings::$path["manage-pages"].DS."base.php";
        }
        Trace::expose_trace();
    } break;

    case "api": {

        Trace::add_trace("Api type request detected", __FILE__);
        $AApi->set_headers(content : "application/json");
        
        //Must be signed in:
        if (!$User->is_signed || !$User->priv->has_privileges($access_policy)) {
            $AApi->request->update_answer_status(403, "You must be registered and signed as an User.");
        }
        //Make sure Module Exists:
        elseif ($APage->is_module_installed() /*&& $APage->is_allowed_to_use($User) */) {
            Trace::add_trace("User check successfully - signed, is-set, is-allowed", __FILE__);
            $APage->load_module();
            Trace::add_trace("Loaded module", __FILE__, $APage::$module);
            Trace::add_trace("Loaded module paths", __FILE__, $APage::$module->paths);
            Trace::add_trace("User check successfully - signed, is-set, is-allowed", __FILE__);
            //Preloads the module endpoints:
            if (Std::$fs::file_exists($APage::$module->paths["api"])) {
                include_once($APage::$module->paths["api"]);
            }
        }
        //Execute if everything is ok:
        if ($AApi->request->answer_code() === 0) {
            $AApi->parse_request($_REQUEST);
            $AApi->answer(print : true, execute : true, external : true);
        } else {
            $AApi->answer(print : true, execute : false, external : true);
        }

    } break;
    case "error": {
        echo "error";
        var_dump($_REQUEST);
    } break;
    case "logout": {
        if ($User->is_signed) {
            $User->user_logout();
        }
        $APage::jump_to_page();
    }
    break;
    default: {
        // Not found page:
        $APage::error_page(404);
        Trace::expose_trace();
    }
}

