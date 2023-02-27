<?php
//Extending the Api of manage core for settings handling:

use \Siktec\Bsik\Std;
use \Siktec\Bsik\Api\Input\Validate;
use \Siktec\Bsik\Api\EndPoint\ApiEndPoint;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Privileges as Priv;
use \Siktec\Bsik\Builder\Components;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\CoreSettings;
use \Siktec\Bsik\Objects\SettingsObject;

/********************************************************************************/
/*****************  get system settings  ******************************************/
/********************************************************************************/
$get_core_settings_policy = new Priv\RequiredPrivileges();
$get_core_settings_policy->define(
    new Priv\Default\PrivAccess(manage : true)
);

AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "get_system_settings",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : false,
    allow_override  : false,
    policy          : $get_core_settings_policy,
    describe    : "Return core system settings",
    params      : [],
    filter      : [],
    validation  : [],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Answer data:
        $Api->request->answer_data([
            "settings"  => strval(CoreSettings::$settings),
            "object"    => serialize(CoreSettings::$settings)
        ]);

        return true;
    }
));

/********************************************************************************/
/*****************  get system settings  ******************************************/
/********************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module          : "core",
    name            : "get_system_settings_groups",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    policy          : $get_core_settings_policy,
    describe        : "Return core system settings in a groups form",
    params      : [
        "groups"     => [],
        "settings"   => [],
        "form"       => false,
        "object"     => false,
        "array"      => false,
        "flatten"    => false
    ],
    filter      : [
        "groups"   => Validate::filter("type", "array")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter(),
        "settings" => Validate::filter("type", "array")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter(),
        "form"     => Validate::filter("type", "boolean")::create_filter(),
        "object"   => Validate::filter("type", "boolean")::create_filter(),
        "array"    => Validate::filter("type", "boolean")::create_filter(),
        "flatten"  => Validate::filter("type", "boolean")::create_filter()
    ],
    validation  : [
        "groups"    => Validate::condition("required")::condition("type", "array")::condition("count", "0", "200")::create_rule(),
        "settings"  => Validate::condition("required")::condition("type", "array")::condition("count", "0", "200")::create_rule(),
        "form"      => Validate::condition("type", "boolean")::create_rule(),
        "object"    => Validate::condition("type", "boolean")::create_rule(),
        "array"     => Validate::condition("type", "boolean")::create_rule(),
        "flatten"   => Validate::condition("type", "boolean")::create_rule()
    ],

    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Put into groups:
        $settings = CoreSettings::$settings->get_all();
        $filtered = [];
        $flatten = [];

        foreach($settings as $key => $value) {
            $group = explode("-", $key)[0];
            if (!empty($args["groups"]) && !in_array($group, $args["groups"])) {
                continue;
            }
            if (!empty($args["settings"]) && !in_array($key, $args["settings"])) {
                continue;
            }
            if (!array_key_exists($group, $filtered)) {
                $filtered[$group] = new SettingsObject();
            }
            if ($args["flatten"] && !array_key_exists($group, $filtered)) {
                $flatten[$group] = [];
            }
            /** @var SettingsObject[] $filtered  */
            $opt = CoreSettings::$settings->get_key($key);
            if (!is_null($opt["option"])) 
                $filtered[$group]->set_option($key,  $opt["option"]);
            if (!is_null($opt["default"]))
                $filtered[$group]->set_default($key, $opt["default"]);
            if (!is_null($opt["description"])) 
                $filtered[$group]->set_description($key,  $opt["description"]);
            if (!is_null($opt["value"]))
                $filtered[$group]->set($key,  $opt["value"]);
            if ($args["flatten"]) {
                $flatten[$group][$key] = $opt;
                $flatten[$group][$key]["calculated"] = $filtered[$group]->get($key);
            }
        }

        //Object type output:
        if ($args["object"]) {
            $Api->request->append_answer_data([
                "object" => $filtered
            ]);
        }

        //Array type output:
        if ($args["array"]) {
            $to_arr = [];
            foreach ($filtered as $group => $set) {
                /** @var SettingsObject $set  */
                $to_arr[$group] = $set->dump_parts();
            }
            $Api->request->append_answer_data([
                "array" => $to_arr
            ]);
        }

        //Flatten type output:
        if ($args["flatten"]) {
            $Api->request->append_answer_data([
                "flatten" => $flatten
            ]);
        }

        //Form type output:
        if ($args["form"]) {
            //Build the form from template:
            $engine = new Template();
            $engine->addFolders([
                CoreSettings::$path["manage-templates"]
            ]);
            $form = "";
            $set  = [];
            foreach ($filtered as $group => $setObj) {
                $form .= Components::settings_form(
                    settings : $setObj,
                    attrs    : ["data-group" =>  $group],
                    engine   : $engine,
                    template : "settings_form"
                );
                $set  = Std::$arr::extend($set, $setObj->dump_parts());
            }
            $Api->request->append_answer_data([
                "form" => $form,
                "settings" => $set
            ]);
        }

        return true;
    }

));

/*********************************************************************************/
/*****************  save core settings  ******************************************/
/*********************************************************************************/
$save_core_settings_policy = new Priv\RequiredPrivileges();
$save_core_settings_policy->define(
    new Priv\Default\PrivAccess(manage : true),
    new Priv\Default\PrivCore(settings: true)
);
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "save_core_settings", 
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    policy          : $save_core_settings_policy,
    params      : [
        "settings"      => null,
        "group"         => null
    ],
    filter      : [
        "group"      => Validate::filter("trim")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter(),
        "settings"   => Validate::filter("trim")::create_filter(),
    ],
    validation  : [
        "group"      => Validate::condition("required")::condition("min_length", "2")::create_rule(),
        "settings"   => Validate::condition("required")::condition("min_length", "1")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Get current settings:
        $current = CoreSettings::$settings;

        /** @var SettingsObject $current */
        $errors = [];
        
        if ($current->extend($args["settings"], $errors)) {

            //Save to db:
            $Api::$db->where("name", "bsik-system")->update("bsik_settings", [
                "object" => $current->values_json(true)
            ], 1);

            //Set answer:
            $Api->request->answer_data([
                "settings" => $current->dump_parts(false, "values", "defaults"),
            ]);

        } else {
            //Errors while extending - return those:
            $Api->request->add_errors($errors);
            $Api->request->update_answer_status(500);
        }

        return true;
    }
));
