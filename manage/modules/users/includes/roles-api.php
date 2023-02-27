<?php

use \Siktec\Bsik\Std;
use \Siktec\Bsik\Api\Endpoint\ApiEndPoint;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Api\Input\Validate;
use \Siktec\Bsik\Builder\Components;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\Privileges as Priv;

/****************************************************************************/
/*******************  local Includes    *************************************/
/****************************************************************************/
require_once "components.php";

/****************************************************************************/
/*******************  Custom filters / Validators     ***********************/
/****************************************************************************/


/****************************************************************************/
/**********************  privileges policies  *******************************/
/****************************************************************************/

$required_edit_role = new Priv\RequiredPrivileges();
$required_edit_role->define(
    new Priv\Default\PrivRoles(edit : true)
);

$required_edit_priv_role = new Priv\RequiredPrivileges();
$required_edit_priv_role->define(
    new Priv\Default\PrivRoles(edit : true, grant: true),
);

$required_create_role = new Priv\RequiredPrivileges();
$required_create_role->define(
    new Priv\Default\PrivRoles(create : true, grant: true),
);

$required_delete_role = new Priv\RequiredPrivileges();
$required_delete_role->define(
    new Priv\Default\PrivRoles(delete : true)
);

/********************************************************************************/
/******* Endpoint -> get role id save privileges definition *********************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "get_role_privileges", 
    params      : [
        "role"  => null,
        "form"  => false
    ],
    filter      : [
        "role"  => Validate::filter("type", "number")::create_filter(),
        "form"  => Validate::filter("type", "boolean")::create_filter(),
    ],
    validation  : [
        "role"  => Validate::condition("required")::condition("min", "1")::create_rule(),
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //get db row:
        $role = $Api::$db->where("id", $args["role"])->getOne("bsik_users_roles");
        if (empty($role)) {
            $Api->request->update_answer_status(500, "requested role does not exists");
            $Endpoint->log_warning(
                "Error while loading role privileges, this should not happen", 
                ["role-id-request" => $args["role"]]
            );
            return false;
        } else {
            $priv = Components::all_empty_privileges();
            if (!empty($role["priv"])) {
                $current = Priv\GrantedPrivileges::safe_unserialize($role["priv"]);
                if (is_null($current)) {
                    $Api->request->update_answer_status(500, "cant load role privileges - malformed");
                    $Endpoint->log_warning(
                        "Error while parsing role [{$role["role"]}] privileges, this should not happen", 
                        ["role-id" => $role["id"]]
                    );
                    return false;
                } else {
                    $priv->update($current);
                }
            }

            //Raw parsed data:
            $role["priv"] = $priv->all_privileges();
            $role["meta"] = $priv->all_meta();

            //Add the for also:
            if ($args["form"] === true) {
                $engine = new Template(
                    cache : Std::$fs::path($Endpoint->working_dir, "templates", "cache")
                );
                $engine->addFolders([
                    Std::$fs::path($Endpoint->working_dir, "templates")
                ]);
                $role["form"] = Components::privileges_form_check($engine, "role_priv_form", $role["priv"], $role["meta"]);
            } else {
                $role["form"] = "";
            }
            //Add data to answer:
            $Api->request->answer_data($role);
        }
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    
));

/********************************************************************************/
/******* Endpoint -> save edited role privileges ********************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "edit_role_privileges", 
    params      : [
        "role"  => null,
        "priv"  => false
    ],
    filter      : [
        "role"  => Validate::filter("type", "number")::create_filter(),
        "priv"  => Validate::filter("type", "array")::create_filter(),
    ],
    validation  : [
        "role"  => Validate::condition("required")::condition("min", "1")::create_rule(),
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        $priv = Components::all_empty_privileges();
        $priv->update_from_arr($args["priv"]);
        $granted = $priv->all_granted(true);
        try {
            $Api::$db->where("id", $args["role"])->update("bsik_users_roles", [
                "priv" => serialize($priv),
                "tags" => json_encode($granted)
            ], true);
            $Api->request->answer_data($granted);
        } catch (\Exception $e) {
            $Endpoint->log_error("DB Error while updating role [{$args["role"]}] privileges, this should not happen",
                [
                    "role-id" => $args["role"], 
                    "mysql"   => $e->getMessage()
                ]
            );
            $Api->request->update_answer_status(500, "Error while saving");
        }
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy: $required_edit_role
));

/********************************************************************************/
/******* Endpoint -> create a new role: *****************************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "create_new_role", 
    params      : [
        "role_name"  => "",
        "role_desc"  => "",
        "role_color" => "",
        "priv"       => false
    ],
    filter      : [
        "role_name"  => Validate::filter("type", "string")
                                ::filter("trim")
                                ::filter("strchars","A-Z","a-z","0-9","_")
                                ::create_filter(),

        "role_desc"  => Validate::filter("type", "string")
                                ::filter("sanitize", FILTER_SANITIZE_FULL_SPECIAL_CHARS)
                                ::filter("trim")
                                ::create_filter(),

        "role_color" => Validate::filter("type", "string")
                                ::filter("trim")
                                ::filter("strchars","A-Z","a-z","0-9","#")
                                ::create_filter(),

        "priv"       => Validate::filter("type", "array")
                                ::create_filter(),
    ],
    validation  : [
        "role_name"  => Validate::condition("required")
                                ::condition("min_length", "3")
                                ::create_rule(),

        "role_desc"  => Validate::condition("optional")
                                ::condition("max_length", "250")
                                ::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {
        //Save lower:
        $role = strtolower($args["role_name"]);
        try {
            if (!$Api::$db->where("role", $role)->has("bsik_users_roles")) {

                //Create priv definition:
                $priv = Components::all_empty_privileges();
                $priv->update_from_arr($args["priv"]);
                $granted = $priv->all_granted(true);

                //Save new:
                $Api::$db->insert("bsik_users_roles", [
                    "role"          => $role,
                    "description"   => empty($args["role_desc"]) ? null : $args["role_desc"],
                    "priv"          => serialize($priv),
                    "tags"          => json_encode($granted),
                    "color"         => empty($args["role_color"]) ? null : $args["role_color"]
                ]);

                //Return the final privileges:
                $Api->request->answer_data([
                    "role"      => $role,
                    "granted"   => $granted
                ]);

            } else {
                $Api->request->update_answer_status(200, "This role name [{$role}] is allready defined");
            }
        } catch (\Exception $e) {
            $Endpoint->log_error("DB Error while creating role [{$role}], this should not happen",
                ["role-name" => $role, "mysql" => $e->getMessage()]
            );
            $Api->request->update_answer_status(500, "Error while creating new role");
        }
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy          : $required_create_role
));

/********************************************************************************/
/******* Endpoint -> edit role data: ********************************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "save_role_data", 
    params      : [
        "role"       => null,
        "role_name"  => "",
        "role_desc"  => "",
        "role_color" => ""
    ],
    filter      : [
        "role"       => Validate::filter("type", "number")::create_filter(),
        "role_name"  => Validate::filter("type", "string")
                                ::filter("trim")
                                ::filter("strchars","A-Z","a-z","0-9","_")
                                ::create_filter(),

        "role_desc"  => Validate::filter("type", "string")
                                ::filter("sanitize", FILTER_SANITIZE_FULL_SPECIAL_CHARS) //TODO: this should be changes for php 8.0.1
                                ::filter("trim")
                                ::create_filter(),

        "role_color" => Validate::filter("type", "string")
                                ::filter("trim")
                                ::filter("strchars","A-Z","a-z","0-9","#")
                                ::create_filter(),
    ],
    validation  : [
        "role"       => Validate::condition("required")::condition("min", "1")::create_rule(),
        "role_name"  => Validate::condition("required")
                                ::condition("min_length", "3")
                                ::create_rule(),

        "role_desc"  => Validate::condition("optional")
                                ::condition("max_length", "250")
                                ::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {
        //Save lower:
        $role = strtolower($args["role_name"]);
        try {
            if ($Api::$db->where("id", $args["role"])->has("bsik_users_roles")) {

                //Save new:
                $Api::$db->where("id", $args["role"])->update("bsik_users_roles", [
                    "role"          => $role,
                    "description"   => empty($args["role_desc"]) ? null : $args["role_desc"],
                    "color"         => empty($args["role_color"]) ? null : $args["role_color"]
                ], 1);

                //Return the final privileges:
                $Api->request->answer_data([
                    "role"      => $args["role"],
                ]);

            } else {
                $Api->request->update_answer_status(200, "This role id [{$args["role"]}] is not defined");
            }
        } catch (\Exception $e) {
            $Endpoint->log_error("DB Error while editing role [{$args["role"]}] data, this should not happen",
                ["role-id" => $args["role"], "mysql" => $e->getMessage()]
            );
            $Api->request->update_answer_status(500, "Error while creating new role");
        }
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy          : $required_edit_role 
));

/********************************************************************************/
/******* Endpoint -> delete role  ***********************************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "users",
    name        : "delete_role", 
    params      : [
        "role"  => null
    ],
    filter      : [
        "role"  => Validate::filter("type", "number")::create_filter()
    ],
    validation  : [
        "role"  => Validate::condition("required")::condition("min", "1")::create_rule(),
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        try {
            if (!$Api::$db->where("role", $args['role'])->has("bsik_users_roles")) {

                //Save new:
                $deleted = $Api::$db->where("id", $args["role"])->delete("bsik_users_roles", 1);

                //Return the final privileges:
                $Api->request->answer_data([
                    "role"      => $args["role"],
                    "granted"   => $deleted
                ]);

            } else {
                $Api->request->update_answer_status(400, "This role name is not registered");
            }
        } catch (\Exception $e) {
            $Endpoint->log_error("DB Error while deleting role [{$args["role"]}], this should not happen",
                ["role-id" => $args["role"], "mysql" => $e->getMessage()]
            );
            $Api->request->update_answer_status(500, "Error while creating new role");
        }
        return true;
    },
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy          : $required_delete_role
));