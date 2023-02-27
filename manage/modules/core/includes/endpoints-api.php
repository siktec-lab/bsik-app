<?php

use \Siktec\Bsik\Std;
use \Siktec\Bsik\Api\EndPoint\ApiEndPoint;
use \Siktec\Bsik\Api\AdminApi;
use \Siktec\Bsik\Render\Pages\AdminPage;
use \Siktec\Bsik\Api\Input\Validate;
use \Siktec\Bsik\Privileges as Priv;

/*********************************************************************************/
/*****************  save module settings  ******************************************/
/*********************************************************************************/
$map_apis_policy = new Priv\RequiredPrivileges();
$map_apis_policy->define(
    new Priv\Default\PrivModules(view : true, endpoints : true),
    new Priv\Default\PrivCore(view : true, settings: true)
);

AdminApi::register_endpoint(new ApiEndPoint(
    module      : "core",
    name        : "map_platform_api",
    working_dir     : dirname(__FILE__).DS."..",
    allow_global    : true,
    allow_external  : true,
    allow_override  : false,
    policy          : $map_apis_policy,
    params      : [
    ],
    filter      : [
    ],
    validation  : [
    ],
    method : function(AdminApi $Api, array $args, ApiEndPoint $Endpoint) {

        $modules    = [];
        $endpoints  = [];
        $failed     = 0;
        $active     = 0;

        //Set flags to load anything - will ignore checks of front, global, external:
        $Api::$ignore_visibility = true;
        //Load all endpoints:
        foreach (AdminPage::$modules->get_all_installed() as $module_name) {
            //All installed known modules:
            $module =  AdminPage::$modules->module_installed($module_name);
            //include static endpoints:
            if (Std::$fs::file_exists("modules", [$module["path"], "module-api.php"])) {
                $modules[] = $module_name;
                $path = Std::$fs::path_to("modules", [$module["path"], "module-api.php"])["path"];
                try { 
                    include_once $path;
                } catch(Throwable $e) {
                    //This is an error thrown in the endpoint code - don't fail just log this:
                    $Endpoint->log_error(
                        message : "Error while loading endpoints while scanning.", 
                        context : [
                            "loaded_module" => $module_name,
                            "endpoint_file" => $path,
                            "error"         => $e->getMessage(),
                            "line"          => $e->getLine()
                        ]
                    );
                    $failed++;
                }
            }
            //Include dynamic endpoints 
            //TODO: add here the check and include of the dynamic endpoint loading - maybe not per module maybe it should be a single file dynamic loader.
        }

        //Get the endpoints parts:
        foreach ($Api->get_all_registered_endpoints() as $endpoint_name) {
            $found = $Api->get_registered_endpoint($endpoint_name);
            if (!is_null($found)) {
                //Group by modules:
                if (!array_key_exists($found->module, $endpoints)) {
                    $endpoints[$found->module] = [];
                }
                $endpoints[$found->module][] = [
                    "module"        => $found->module,
                    "name"          => $endpoint_name,
                    "describe"      => $found->describe,
                    "disk_location" => $found->working_dir,
                    "protected"     => !$found->protected,
                    "exposure"      => [
                        "external"  => $found->external,
                        "global"    => $found->global,
                        "front"     => $found->front
                    ],
                    "allowed"       => $found->policy->has_privileges($Api::$issuer_privileges),
                    "policy"        => $found->policy->all_granted(false),
                    "params"        => [
                        "expected"  => $found->params,
                        "rules"     => $found->conditions
                    ]
                ];
                $active++;
            }
        }

        //Set answer:
        $Api->request->answer_data([
            "scanned_modules" => $modules,
            "endpoints"       => $endpoints,
            "total_found"     => $active,
            "total_failed"    => $failed //TODO: this can be used for later "health check" of the system
        ]);

        return true;
    }
));
