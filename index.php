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

/******************************************************************************/
/******************************  REQUIRES  ************************************/
/******************************************************************************/
require_once 'bsik.php';

use \Siktec\Bsik\Trace;
use \Siktec\Bsik\Std;
use \Siktec\Bsik\Base;
use \Siktec\Bsik\CoreSettings;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\Users\User;
use \Siktec\Bsik\Api\FrontApi;
use \Siktec\Bsik\Render\Pages\FrontPage;

Trace::add_step(__FILE__, "Controller - front index");


/******************************************************************************/
/*********************  LOAD CONF AND DB CONNECTION  **************************/
/******************************************************************************/
Base::configure($conf);
Trace::add_trace("Loaded Base Configuration Object",__FILE__, $conf);
Base::connect_db();
Trace::add_trace("Establish db connection",__FILE__);

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

//Session start:
if(!session_id()){ session_start(); }

/******************************************************************************/
/**************************   LOAD USER   *************************************/
/******************************************************************************/
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

// Trace::expose_trace();
// exit;

/******************************************************************************/
/************************  FRONT PAGE CONSTANTS  ******************************/
/******************************************************************************/
FrontPage::set_user_string("----------");
FrontPage::$index_page_url = CoreSettings::$url["full"];
FrontPage::tokenize();
FrontPage::load_request($_REQUEST ?? []);
FrontPage::load_logger(
    path : CoreSettings::$path["logs"],
    channel: FrontPage::$request->type == "api" ? "fapi-front" : "fpage-general",
);
FrontPage::load_defined_pages();
FrontPage::load_paths(global_dir  : ["front", "global"]);

//Initialize Api:
$FApi = new FrontApi(
    csrf                : FrontPage::csrf(),  // CSRF TOKEN
    debug               : CoreSettings::get("api-responses-with-debug-info", false), 
    issuer_privileges   : $User->priv
);

//------
Trace::add_trace("Loaded FrontPage object", __FILE__, ["token" => FrontPage::$token]);
Trace::reg_vars(["Request"          => FrontPage::$request->get()]);
Trace::reg_vars(["Requested page"   => FrontPage::$request->page]);
Trace::reg_vars(["Requested which"  => FrontPage::$request->which]);
Trace::reg_vars(["Available pages"  => FrontPage::$pages]);

/******************************  Global Includes      *****************************/
//Load global endpoints:
//TODO: we need to consider this as the possibility of full dynamic front is supported
if (Std::$fs::file_exists(FrontPage::$paths["global-api"])) {
    include_once FrontPage::$paths["global-api"];
}

/******************************************************************************/
/************************    CONTROLLER LOGIC    ******************************/
/******************************************************************************/

Trace::add_step(__FILE__,"Loading and building page:");
switch (FrontPage::$request->type) {
    
    case "page": {

        Trace::add_trace("Type detected", __FILE__, FrontPage::$page_type);
        
        //Load page -> request based:
        if (FrontPage::load_page_record()) {

            //Two types of pages:
            switch (FrontPage::$page_type) {

                case "file": {
                
                    //paths for file based page:
                    FrontPage::load_paths(page_dir : ["front", "pages"]);
                    Trace::add_trace("loaded page required paths", __FILE__, FrontPage::$paths);
                    
                    //Include page implementation:
                    include FrontPage::$paths["page"].DS.FrontPage::$page["file_name"];

                    //Trace some defined properties:
                    Trace::add_trace("register pages", __FILE__, FrontPage::$implemented_pages);
                    $Page_instance = FrontPage::load_page(FrontPage::$page["name"], $User);

                    if (FrontPage::is_allowed()) {
                        //Render the page:    
                        $Page_instance->render();
                        Trace::add_trace("page settings", __FILE__, FrontPage::$settings->get_all());
                    } else {
                        //Jump to home say no privileges:
                        FrontPage::jump_to_page();
                    }
                } break;

                case "dynamic": {
                    var_dump("dynamic");
                    var_dump(FrontPage::$page);
                } break;
            }
            // --> is it static  ???????
                    // --> load global components
                    // --> load global api.
                    // --> load page.

            // --> is it dynamic ???????
                    // --> load global api.
                    // --> load dynamic page builder.
        } else {
            FrontPage::error_page(404);
        }
        Trace::expose_trace();
    } break;
    case "api": {

        Trace::add_trace("Api type request detected", __FILE__);
        $FApi->set_headers(content : "application/json");
        
        //Preload endpoints of the current page:
        if (FrontPage::load_page_record()) {
            //Two types of pages:
            switch (FrontPage::$page_type) {
                case "file": {
                    //paths for file based page:
                        FrontPage::load_paths(page_dir : ["front", "pages"]);
                    //Preloads the module end point:
                    if (Std::$fs::file_exists("raw", FrontPage::$paths["page-api"])) {
                        include_once FrontPage::$paths["page-api"];
                    }
                } break;
                case "dynamic": {
                } break;
            }
        }
        
        $FApi->parse_request($_REQUEST);

        if ($FApi->is_manage_call()) {
            $FApi->answer_from_manage(print : true);
        } else {
            $FApi->answer(print : true, execute : true, external : true);
        }
    } break;
    case "error": {
        FrontPage::error_page(FrontPage::$request->page);
    } break;
    case "logout": {
        if ($User->is_signed) {
            $User->user_logout();
        }
        FrontPage::jump_to_page();
    }
    break;
    default: {
        FrontPage::error_page(404);
    }
}


