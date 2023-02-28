<?php

use \Siktec\Bsik\Api\Endpoint\ApiEndPoint;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Api\Input\Validate;
use \Siktec\Bsik\Objects\Password;
use \Siktec\Bsik\Privileges as Priv;

/****************************************************************************/
/*******************  local Includes    *************************************/
/****************************************************************************/

if (!defined('PLAT_HASH_SALT')) 
    define('PLAT_HASH_SALT', "");

/****************************************************************************/
/*******************  Custom filters / Validators     ***********************/
/****************************************************************************/

/****************************************************************************/
/**********************  privileges policies  *******************************/
/****************************************************************************/

$required_create_user = new Priv\RequiredPrivileges();
$required_create_user->define(
    new Priv\Default\PrivUsers(create : true)
);

/********************************************************************************/
/******* Endpoint -> create a new role: *****************************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "create_user_account", 
    params      : [
        "about"             => "",
        "company-name"      => "",
        "password"          => null,
        "confirm-password"  => null,
        "email"             => null,
        "first-name"        => null,
        "last-name"         => null,
        "role"              => null,
        "status"            => 2        //suspended
    ],
    filter      : [
        "about"             => Validate::filter("type", "string")::filter("trim")::filter("transform_spaces")::filter("max_length", 250)::create_filter(),
        "company-name"      => Validate::filter("type", "string")::filter("trim")::filter("transform_spaces")::filter("utf_names")::filter("max_length", 50)::create_filter(),
        "password"          => Validate::filter("type", "string")::filter("max_length", 150)::create_filter(),
        "confirm-password"  => Validate::filter("type", "string")::filter("max_length", 150)::create_filter(),
        "email"             => Validate::filter("type", "string")::filter("trim")::filter("lowercase")::filter("max_length", 250)::filter("sanitize", FILTER_SANITIZE_EMAIL)::create_filter(),
        "first-name"        => Validate::filter("type", "string")::filter("trim")::filter("transform_spaces")::filter("utf_names")::filter("max_length", 50)::create_filter(),
        "last-name"         => Validate::filter("type", "string")::filter("trim")::filter("transform_spaces")::filter("utf_names")::filter("max_length", 50)::create_filter(),
        "role"              => Validate::filter("type", "number")::create_filter(),
        "status"            => Validate::filter("type", "string")::create_filter()
    ],
    validation  : [
        "about"             => Validate::condition("optional")::condition("max_length", "250")::create_rule(),
        "company-name"      => Validate::condition("optional")::condition("max_length", "50")::create_rule(),
        "password"          => Validate::condition("required")::condition("min_length", "8")::create_rule(),
        "confirm-password"  => Validate::condition("required")::condition("min_length", "8")::create_rule(),
        "email"             => Validate::condition("required")::condition("email")::create_rule(),
        "first-name"        => Validate::condition("required")::condition("length", "2", "50")::create_rule(),
        "last-name"         => Validate::condition("required")::condition("length", "2", "50")::create_rule(),
        "role"              => Validate::condition("required")::condition("min", "1")::create_rule(),
        "status"            => Validate::condition("required")::condition("one_of", "0|2")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Test Password:
        $valid = true;
        $Pass = new Password( salt : PLAT_HASH_SALT);
        $v_token = $Pass->encrypt($Pass->generate_password()); // This will generate a random hash for validation token.

        //Validate email is unique:
        if ($Api::$db->where("email", $args["email"])->has("bsik_users")) {
            $valid = false;
            $Api->request->append_answer_data(["email" => ["email is not unique"]]);
        }
        //Validate - password and save it:
        if (!$Pass->set_password($args["password"])) {
            $valid = false;
            $Api->request->append_answer_data(["password" => ["password is not valid"]]);
        }
        //Validate match:
        if ($valid && !$Pass->compare($args["confirm-password"])) {
            $valid = false;
            $Api->request->append_answer_data(["confirm-password" => ["passwords mismatch"]]);
        }
        //Validate roles
        if (!$Api::$db->where("id", $args["role"])->has("bsik_users_roles")) {
            $valid = false;
            $Api->request->append_answer_data(["role" => ["role is not set in db"]]);
        }
        //If valid save to db and send validation:
        if (!$valid) {
            $Api->request->update_answer_status(400, "params are not valid");
        } else {
            try {
                //Create account safely:
                $Api::$db->insert("bsik_users", [
                    "email"             => $args["email"],
                    "first_name"        => $args["first-name"],
                    "last_name"         => $args["last-name"],
                    "comp_name"         => $args["company-name"],
                    "password"          => $Pass->get_hash(),
                    "v_token"           => $v_token,
                    "role"              => $args["role"],
                    "about"             => $args["about"],
                    "account_status"    => $args["status"] === '0' ? 1 : $args["status"]
                ]);
                $Api->request->append_answer_data([
                    "id" => $Api::$db->getInsertId()
                ]);
            } catch(Exception $e) {
                $valid = false;
                $Endpoint->log_error("DB Error while creating user account [{$args["email"]}], this should not happen",
                    ["mysql" => $e->getMessage()]
                );
                $Api->request->update_answer_status(500, "Internal db error while saving account");
            }
        }
        $Api->request->append_answer_data([
            "valid" => $valid
        ]);
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    policy          : $required_create_user
));
