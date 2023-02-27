<?php

use \Siktec\Bsik\Api\Endpoint\ApiEndPoint;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Api\Input\Validate;
use \Siktec\Bsik\Objects\Password;
use \Siktec\Bsik\Users\User;

/****************************************************************************/
/*******************  local Includes    *************************************/
/****************************************************************************/

/****************************************************************************/
/*******************  Custom filters / Validators     ***********************/
/****************************************************************************/

/****************************************************************************/
/**********************  privileges policies  *******************************/
/****************************************************************************/

/********************************************************************************/
/******* Endpoint -> create a new role: *****************************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "login", 
    params      : [
        "username"          => null,
        "password"          => null,
        "remember"          => true,
    ],
    filter      : [
        "username"          => Validate::filter("type", "string")::filter("trim")::filter("lowercase")::filter("max_length", 250)::filter("sanitize", FILTER_SANITIZE_EMAIL)::create_filter(),
        "password"          => Validate::filter("type", "string")::filter("max_length", 150)::create_filter(),
        "remember"          => Validate::filter("type", "boolean")::create_filter()
        
    ],
    validation  : [
        "username"          => Validate::condition("required")::condition("email")::create_rule(),
        "password"          => Validate::condition("required")::condition("min_length", "8")::create_rule(),
        "remember"          => Validate::condition("optional")::condition("type", "boolean")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Load password:
        $Pass = new Password();
        $Pass->set_password($args["password"]);

        $User = new User();
        //perform login:
        $args["csrftoken"] = $Api->csrf;
        $login = $User->user_login($args);
        //Set results:
        $Api->request->answer_data([
            "login"  => $login,
            "errors" => $User->errors
        ]);
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    allow_front     : true,
    policy          : null
));
