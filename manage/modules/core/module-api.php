<?php

use \Siktec\Bsik\StdLib as BsikStd;
use \Siktec\Bsik\CoreSettings;
use \Siktec\Bsik\Api\EndPoint\ApiEndPoint;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Api\Input\Validate;
use \Siktec\Bsik\Module\ModuleInstall;
use \Siktec\Bsik\Module\Modules;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\Render\Pages\AdminPage;
use \Siktec\Bsik\Objects\SettingsObject;
use \Siktec\Bsik\Privileges as Priv;
use \Siktec\Bsik\Builder\Components;

require_once "includes".DS."endpoints-api.php";

require_once "includes".DS."settings-api.php";

/****************************************************************************/
/*******************  Custom filters / Validators     ***********************/
/****************************************************************************/
// Wrap it in the condition to make sure its not triggering errors when dynamically loaded as a global extenssion:
// if (!class_exists('PagesModuleValidation')) {
//     class PagesModuleValidation {
//         final public static function in_array($input, string $allowed) {
//             return in_array($input, explode("|", $allowed)) ? true : "@input@ is not a valid input.";
//         }
//     }
// }

// //Register validator if not set:
// if (!Validate::has_validator("in_array"))
//     Validate::add_validator("in_array", "PagesModuleValidation::in_array");


/****************************************************************************/
/**********************  Greeting Message  **********************************/
/****************************************************************************/

AdminApi::register_endpoint(new ApiEndPoint(
    module          : "core",
    name            : "saybye", 
    params          : [ 
        "name" => null
    ],
    filter          : [],
    validation      : [],
    //The method to execute -> has Access to BsikApi
    method          : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        $engine = new Template(
            cache : BsikStd\FileSystem::path($Endpoint->working_dir, "templates", "cache")
        );

        $engine->addFolders([BsikStd\FileSystem::path($Endpoint->working_dir, "templates")]);

        $ret = $engine->render("saybye", $args);

        $Api->request->update_answer_status(200);
        $Api->request->answer_data([
            "message" => $ret
        ]);
        return true;
    },
    working_dir     : dirname(__FILE__),
    allow_global   : true,
    allow_external : true,
    allow_override : false
));

/****************************************************************************/
/**********************  Install module            **************************/
/****************************************************************************/
$install_module_policy = new Priv\RequiredPrivileges();
$install_module_policy->define(
    new Priv\Default\PrivModules( settings: true, view: true, install: true )
);
AdminApi::register_endpoint(new ApiEndPoint(
    module          : "core",
    name            : "install_module_uploaded", 
    describe        : "install a module from an uploaded zip file.",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy          : $install_module_policy,
    params          : [],
    filter          : [],
    validation      : [],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        // Perform file upload with Api safe file:
        [$status, $ret] = $Api->file(
            name        : "module_file", 
            to          : CoreSettings::$path["manage"].DS."uploaded", 
            max_bytes   : BsikStd\FileSystem::format_size_to(10, "MB", "B"),
            mime        : ["zip"]
        );

        // If file is not available:
        if (!$status) {
            $Endpoint->log_warning(
                message : "Uploaded Module archive to uninstall - not uploaded.", 
                context : ["error" => $ret]
            );
            $Api->request->update_answer_status(200, $ret, "upload_failed");
            return true;
        }
        
        // Save uploaded path:
        $Api->request->append_answer_data(["uploaded" => $ret]);

        // Basic cleanup
        $cleanup = function($installer, $delete = "", $clean = false) {
            $installer->close_zip();
            if (!empty($delete)) BsikStd\FileSystem::delete_files($delete);
            if ($clean) $installer->clean();
        };

        $installed = [];
        $Installer = null;

        // Perform sik module installation:
        try {

            $Installer = new ModuleInstall($ret, BsikStd\FileSystem::path_to("modules")["path"]);
            
            // Validate in zip:
            $validation = $Installer->validate_required_files_in_zip();
            if (!empty($validation)) {
                $Endpoint->log_warning(
                    message : "Uploaded Module archive to install - rejected zip file.", 
                    context : ["validation" => $validation]
                );
                $Api->request->update_answer_status(200, $validation, "invalid");
                $cleanup($Installer, $ret);
                return false;
            }
            $Api->request->append_answer_data(["valid_zip" => "yes"]);

            // Deploy zip to destination - temp extract:
            $Installer->temp_deploy();

            // install the module or pack:
            [$status, $errors, $installed] = $Installer->install($Api->get_user("id"));
            $Api->request->append_answer_data([
                "installed" => $installed
            ]);
            if (!$status) {
                $Endpoint->log_warning(
                    message : "Uploaded Module archive to install - module not valid.", 
                    context : ["validation" => $errors]
                );
                $cleanup($Installer, $ret, true);
                $Api->request->update_answer_status(200, $errors, "install_error");
                return false;
            } 
        } catch (Throwable $e) {
            $Endpoint->log_error(
                message : "Uploaded Module archive to install - internal error.", 
                context : [
                    "error" => $e->getMessage(),
                    "file"  => $e->getFile(),
                    "line"  => $e->getLine()
                ]
            );
            $Api->request->update_answer_status(200, $e->getMessage(), "internal_error");
            $cleanup($Installer, $ret, true);
            return false;
        }

        //finish:
        $Endpoint->log_info(
            message : "Installed Modules successfully.", 
            context : ["installed" => $installed]
        );
        $cleanup($Installer, $ret, true);
        $Api->request->update_answer_status(200);

        return true;
    }
));

/****************************************************************************/
/**********************  Uninstall module      ******************************/
/****************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module          : "core",
    name            : "uninstall_module", 
    describe        : "uninstall a module with all its related data.",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy          : $install_module_policy,
    params      : [
        "module_name"   => null
    ],
    filter      : [
        "module_name"      => Validate::filter("trim")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter()
    ],
    validation  : [
        "module_name"      => Validate::condition("required")::condition("min_length", "2")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //TODO: depends modules should be addressed as we are going to remove all dependencies also
        // This should intreduce a ref count system to installed modules on the system and remove them only if the ref count is 1 or less 
        // unless the module is directly addressed - if we are going to uninstall a module with a high ref count than warn the user
        
        //Get the module:
        $get_module = $Api->call(
            args     : ["module_name" => $args["module_name"]], 
            endpoint : "core.get_installed"
        );
        
        //If the internal query succeeded:
        if ($get_module->answer_code() === 200) {
            //If module is here:
            if (array_key_exists($args["module_name"], $get_module->answer_data())) {
                
                $menu_actions = [];
                //Load module if needed events:
                $module = $get_module->answer_data()[$args["module_name"]];

                //Make sure its not a core module:
                if ($module["core"]) {
                    //Nope prevent:
                    $Endpoint->log_warning(
                        message : "Requested Module to uninstall ['{$args["module_name"]}'] is a protected core module."
                    );
                    $Api->request->update_answer_status(403, "Core modules are protected");
                    return false; 
                }

                /** @var ?Module $module_obj */
                $module_obj = null;
                try {
                    $module_obj = Modules::initiate_module(
                        module_name : $args["module_name"],
                        view : "",
                        db : $Api::$db,
                        Api : $Api,
                        Page : AdminPage::$ref, // we don't really care about APage
                        User : null   // we don't give user info to the module
                    );
                    if (isset($module["menu"]["action"])) {
                        $menu_actions[] = $module["menu"]["action"];
                    }
                } catch (\Throwable $e) {
                    $origin = $e->getPrevious();
                    $Endpoint->log_error(
                        message : $e->getMessage(),
                        context : [
                            "module_loaded" => $args["module_name"],
                            "file"   => is_null($origin) ? $e->getFile() : $origin->getFile(),
                            "line"   => is_null($origin) ? $e->getLine() : $origin->getLine()
                        ]
                    );
                    $module_obj = null;
                }

                //Execute Events:
                if ($module_obj) {
                    $module_obj->exec_event("me-uninstall");
                }

                //Save folder copy to trash:
                //TODO: maybe its a good idea to add here also the db entries.
                $trash_name = BsikStd\Dates::time_datetime("YmdHis")."_module_".$module_obj->module_name.".zip";
                $temp_zip = BsikStd\Zip::zip_folder(
                    $module_obj->path, 
                    BsikStd\FileSystem::path_to("trash", $trash_name)["path"]
                );

                //Delete folder:
                BsikStd\FileSystem::clear_folder($module_obj->path ,true);

                //Remove Module entry:
                $Api::$db->where("name", $module["name"])->delete("bsik_modules", 1);

                //log the operation:
                $Endpoint->log_info("EVENT: Unistalled Module '{$module_obj->module_name}'", ["trash" => $trash_name]);

                //Return data:
                $Api->request->answer_data([
                    "module"     => $module,
                    "menu"       => $menu_actions
                ]);
                
            } else {
                //Log it - we got a request to uninstall an unknown module:
                $Endpoint->log_error(
                    message : "Requested Module to uninstall ['{$args["module_name"]}'] not found."
                );
                $Api->request->update_answer_status(500, "Requested module not found");
            }
        } else {
            //Don't log it should have been logged by the child call:
            $Api->request->answer = clone $get_module->answer;
        }
        //Check module is installed:
        return true;
    }
));
/****************************************************************************/
/*****************  get installed modules object      ***********************/
/****************************************************************************/
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "get_installed", 
    describe    : "lists installed modules (or module) and their data / state.",
    params      : [
        "module_name"   => null
    ],
    filter      : [
        "module_name"      => Validate::filter("trim")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter()
    ],
    validation  : [
        "module_name"      => Validate::condition("optional")::condition("min_length", "2")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {
        //Modules installed:
        $modules = [];
        if ($args["module_name"]) {
            $modules = $Api::$db->map("name")->where("name", $args["module_name"])->get("bsik_modules");
        } else {
            $modules = $Api::$db->map("name")->get("bsik_modules");
        }
        
        foreach ($modules as &$module) {
            $module["settings"] = BsikStd\Strings::parse_jsonc($module["settings"],   onerror: []);
            $module["menu"]     = BsikStd\Strings::parse_jsonc($module["menu"],       onerror: []);
            $module["info"]     = BsikStd\Strings::parse_jsonc($module["info"],       onerror: []);
        }
        $Api->request->answer_data($modules);
        return true;
    },
    working_dir     : dirname(__FILE__),
    allow_global    : true,
    allow_external  : true,
    allow_override  : false
));


/****************************************************************************/
/*****************  disable module ******************************************/
/****************************************************************************/
$module_status_policy = new Priv\RequiredPrivileges();
$module_status_policy->define(
    new Priv\Default\PrivModules( activate: true, view: true )
);
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "status_module", 
    describe    : "Sets the status of a module to active or disable",
    working_dir     : dirname(__FILE__),
    allow_global    : false,
    allow_external  : true,
    allow_override  : false,
    policy     : $module_status_policy,
    params      : [
        "module_id"     => null,
        "module_status" => "disable",
    ],
    filter      : [
        "module_id"     => Validate::filter("trim")::filter("strchars","A-Z","a-z","0-9","_")::create_filter(),
        "module_status" => Validate::filter("trim")::filter("strchars","a-z")::create_filter(),
    ],
    validation  : [
        "module_id"     => Validate::condition("required")::condition("type", "string")::create_rule(),
        "module_status" => Validate::condition("required")::condition("in_array", "disable|active")::create_rule(),
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //get db row:
        $set_status = $args["module_status"];
        
        if ($Api::$db->where("name", $args["module_id"])->has("bsik_modules")) {
            $Api::$db->where("name", $args["module_id"])->update("bsik_modules", [
                "status" => $set_status == "active" ? 1 : 0
            ], 1);
            $Api->request->answer_data($Api::$db->where("name", $args["module_id"])->get("bsik_modules"));
        } else {
            $Api->request->update_answer_status(400, "module is not installed.");
        }
        return true;
    }
));

/********************************************************************************/
/*****************  get module settings  ******************************************/
/********************************************************************************/
$get_settings_policy = new Priv\RequiredPrivileges();
$get_settings_policy->define(
    new Priv\Default\PrivModules( settings: true, view: true )
);
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "get_module_settings",
    describe    : "Retrieves the current settings for a specific module",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    allow_front     : false,   
    policy          : $get_settings_policy,
    params      : [
        "module_name"    => null,
        "form"           => true,
        "object"         => false
    ],
    filter      : [
        "module_name"    => Validate::filter("type", "string")::filter("trim")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter(),
        "form"      => Validate::filter("type", "boolean")::create_filter(),
        "object"    => Validate::filter("type", "boolean")::create_filter()
    ],
    validation  : [
        "module_name"    => Validate::condition("type", "string")::condition("min_length", "2")::create_rule(),
        "form"      => Validate::condition("type", "boolean")::create_rule(),
        "object"    => Validate::condition("type", "boolean")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Load module installation row:
        $module_installed = $Api::$db->where("name", $args["module_name"])->getOne("bsik_modules");
        $Api->register_debug("from-module-installed", $module_installed);

        //If we have it load:
        if (!empty($module_installed)) {
            //Load module code:
            if (BsikStd\FileSystem::file_exists("modules", [$module_installed["path"], "module.php"])) {
                $module_file = BsikStd\FileSystem::path_to("modules", [$module_installed["path"], "module.php"]);

                //Load module:
                try {
                    require_once $module_file["path"];
                    $Module = Modules::module($module_installed["name"]);
                } catch (Exception $e) {
                    $Endpoint->log_error(
                        message : "Error while loading module ['{$args["module_name"]}'], requested module has errors",
                        context : [
                            "error" => $e->getMessage(),
                            "line"  => $e->getLine(),
                            "file" => $e->getFile(),
                        ]
                    );
                    $Api->request->update_answer_status(500, "Requested module has errors");
                    return false;
                }

                //Make sure its loaded:
                if (is_null($Module)) {
                    $Api->request->update_answer_status(500, "Requested module is not registering");
                    return false;
                }

                //Build the form from template:
                $engine = new Template();
                $engine->addFolders([
                    CoreSettings::$path["manage-templates"]
                ]);


                //Create form:
                $form = Components::settings_form(
                    settings : $Module->settings,
                    attrs    : ["data-module" =>  $Module->module_name],
                    engine   : $engine,
                    template : "settings_form"
                );
                $Api->register_debug("settings_object", $Module->settings->dump_parts());

                //Return:
                $Api->request->answer_data([
                    "settings"  => $Module->settings->get_all(true),
                    "form"      => $form,
                    "object"    => $args["object"] ? serialize($Module->settings) : ""
                ]);
                return true;
            } else {
                $Endpoint->log_error(
                    message : "Error while loading module ['{$args["module_name"]}'], requested module path is not reachable"
                );
                $Api->request->update_answer_status(500, "Requested module path is not correct");
            }
        } else {
            $Endpoint->log_error(
                message : "Error while loading module ['{$args["module_name"]}'], requested module not installed"
            );
            $Api->request->update_answer_status(404, "Requested module not installed");
        }
        return false;
    }
));


/*********************************************************************************/
/*****************  save module settings  ******************************************/
/*********************************************************************************/
$save_settings_policy = new Priv\RequiredPrivileges();
$save_settings_policy->define(
    new Priv\Default\PrivModules( settings: true, view: true )
);
AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "save_module_settings", 
    describe    : "Update a module settings",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    policy          : $save_settings_policy,
    params      : [
        "settings"      => null,
        "module_name"   => null
    ],
    filter      : [
        "module_name"      => Validate::filter("trim")::filter("strchars","A-Z","a-z","0-9","_","-")::create_filter(),
        "settings"         => Validate::filter("trim")::create_filter(),
    ],
    validation  : [
        "module_name"      => Validate::condition("required")::condition("min_length", "2")::create_rule(),
        "settings"         => Validate::condition("required")::condition("min_length", "1")::create_rule()
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        //Get current settings:
        $get = $Api->call(
            args     : array_merge($args, ["module_name" => $args["module_name"], "form" => false, "object" => true]), 
            endpoint : "core.get_module_settings"
        );
        
        //If 200 that means we got current settings and module is installed and can be accessed:
        if ($get->answer_code() === 200) {

            /** @var SettingsObject $settings */
            $settings = unserialize($get->answer_data()["object"]);
            $errors = [];
            
            if ($settings->extend($args["settings"], $errors)) {

                //Save to db:
                $Api::$db->where("name", $args["module_name"])->update("bsik_modules", [
                    "settings" => $settings->values_json(true)
                ], 1);

                //Set answer:
                $Api->request->answer_data([
                    "settings" => $settings->dump_parts(false, "values", "defaults"),
                ]);

            } else {

                //Errors while extending - return those:
                $Api->request->add_errors($errors);
                $Api->request->update_answer_status(500);
            }

        } else {
            $Api->request->answer = clone $get->answer;
        }
        return true;
    }
));
