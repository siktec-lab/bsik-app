<?php

use \Siktec\Bsik\Trace;
use \Siktec\Bsik\Builder\Components;
use \Siktec\Bsik\Builder\BsikButtons;
use \Siktec\Bsik\Builder\BsikForms;
use \Siktec\Bsik\Builder\BsikIcon;
use \Siktec\Bsik\Module\Modules;
use \Siktec\Bsik\Module\Module;
use \Siktec\Bsik\Module\ModuleView;
use \Siktec\Bsik\Privileges as Priv;
use \Siktec\Bsik\Objects\SettingsObject;

/****************************************************************************/
/*******************  local Includes    *************************************/
/****************************************************************************/
require_once "includes".DS."components.php";


/****************************************************************************/
/*******************  required privileges for module / views    *************/
/****************************************************************************/
$module_policy = new Priv\RequiredPrivileges();
$module_policy->define(
    new Priv\Default\PrivAccess(manage : true)
);

/****************************************************************************/
/*******************  Register Module  **************************************/
/****************************************************************************/

Modules::register_module_once(new Module(
    name          : "core",
    privileges    : $module_policy,
    views         : ["settings", "modules", "endpoints"],
    default_view  : "modules",
    settings      : new SettingsObject(
        defaults : [
            "show-summary-widget" => true,
            "modules-per-page"    => 5
        ],
        options : [
            "show-summary-widget" => "boolean:notempty",
            "modules-per-page"    => [5,10,15,20,50,100,150,200]
        ],
        descriptions : [
            "show-summary-widget" => "Display or hide the summary widget.",
            "modules-per-page"    => "Control haw many modules to show in the Installed modules list.",
        ]
    )
)); 

/****************************************************************************/
/*******************  View - settings  *************************************/
/****************************************************************************/
$view_core_settings_policy = new Priv\RequiredPrivileges();
$view_core_settings_policy->define(
    new Priv\Default\PrivCore(settings: true)
);
Modules::module("core")->register_view(
    view : new ModuleView(
        name        : "settings",
        privileges  : $view_core_settings_policy,
        settings    : new SettingsObject([
            "title"         => "Core Platform Settings",
            "description"   => "View / Change low level core settings.",
        ])
    ),
    render : function() {

        /** @var Module $this */

        // Include confiramtion modal in page:
        // $this->page->additional_html(Components::confirm());

        //////////////////////////////////////////
        // Endpoints List:
        //////////////////////////////////////////
        $settings_list_title = Components::title(text : "Filter Settings", attrs : ["class" => "module-title"]);
        
        $endpoints_list = $this->api->call(
            args     : [ "groups" => [], "array" => true, "object" => true, "flatten" => true ], 
            endpoint : "core.get_system_settings_groups"
        );

        Trace::reg_var("test", $endpoints_list);

        $loader = Components::loader(
            class : "loading-spinner",
            color : "primary",
            size  : "md",
            align : "center",
            show  : false,
            type  : "border",
            text  : "Loading..."    
        );

        $settings_list = $this->page->engine->render(
            name    : "settings_list",
            context : [
                "loading"       => $loader,
                "settings"      => $endpoints_list->answer_data()["flatten"],
                "search"        => ""
            ]
        );

        ////////////////////////////////////////
        // Modal Settings:
        ////////////////////////////////////////
        $this->page->additional_html(Components::modal(
            "group-settings-modal", 
            BsikIcon::fas("fa-cogs")."&nbsp;&nbsp;Edit Settings",
            [
                Components::alert(
                    text : "<span class='alert-message'>alert text</span>",
                    color : "warning",
                    icon : "fas fa-edit",
                    classes : ["edit-settings-alert-info"]
                ),
                "<div class='form-modal-container'></div>"
            ],
            "",
            [
                ["button.btn.btn-secondary", ["data-action" => "save-core-settings"], "Save Core Settings", false],
                ["button.btn.btn-primary",   [],     "Cancel", true],
            ],
            [
                "close-white"    => true,
                "size"     => "lg",
                "backdrop" => "static",
                "keyboard" => "false",
            ]
        ));

        //Template:
        // return <<<HTML
        //     <div class='container'>
        //         {$actions_bar}
        //     </div>
        //     <div class='container sik-form-init'>
        //         {$endpoints_list_title}
        //         {$endpoint_list}
        //     </div>
        // HTML;
        return <<<HTML
            <div class='container'>
                {$settings_list_title}
                {$settings_list}
            </div>
        HTML;
    }
);

/****************************************************************************/
/*******************  View - modules  ***************************************/
/****************************************************************************/
$view_modules_policy = new Priv\RequiredPrivileges();
$view_modules_policy->define(
    new Priv\Default\PrivModules(view : true)
);
Modules::module("core")->register_view(
    view : new ModuleView(
        name        : "modules",
        privileges  : $view_modules_policy,
        settings    : new SettingsObject([
            "title"         => "Manage Modules",
            "description"   => "Installation and management of BSIK modules",
        ])
    ),
    render      : function() {

        /** @var Module $this */

        //Include confiramtion modal in page:
        $this->page->additional_html(Components::confirm());

        ////////////////////////////////////////
        // Stats:
        ////////////////////////////////////////
        $count_installed    = $this->db->getValue("bsik_modules", "count(*)");
        $count_activated    = $this->db->where("status", 1)->getValue("bsik_modules", "count(*)");
        $count_notactive    = $this->db->where("status", 0)->getValue("bsik_modules", "count(*)");
        $count_hasupdate    = $this->db->where("status", 2)->getValue("bsik_modules", "count(*)");
        $stat_installed     = Components::summary_card("Installed", $count_installed, "fas fa-gem", "yellow");
        $stat_activated     = Components::summary_card("Activated", $count_activated, "fas fa-store", "info");
        $stat_notactive     = Components::summary_card("Disabled", $count_notactive, "fas fa-store-slash", "warning");
        $stat_hasupdate     = Components::summary_card("Has Updates", $count_hasupdate, "fas fa-history", "danger");

        //Controls:
        $stats = <<<HTML
            <div class="container pt-3 pb-3 module-settings-stats-container">
                <div class="row">
                    <div class="col-xl-3 col-lg-6 mb-3 mb-xl-3">
                        {$stat_installed}
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-3 mb-xl-3">
                        {$stat_activated}
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-3 mb-xl-3">
                        {$stat_notactive}
                    </div>
                    <div class="col-xl-3 col-lg-6 mb-3 mb-xl-3">
                        {$stat_hasupdate}
                    </div>
                </div>
            </div>
        HTML;

        ////////////////////////////////////////
        // install new:
        ////////////////////////////////////////
        $install_title = Components::title("Install Modules", attrs : ["class" => "module-title"]);
        $upload_field  = BsikForms::file("input-manual-module", "input-manual-module", "", "sm");
        $upload_button = BsikButtons::button("upload-module-btn", "Upload & Install", "primary", "md", "button", ["data-action" => "upload-module-btn"], [], "border-sm");

        $install = <<<HTML
            <div class="container pt-3 pb-3 module-settings-install-container sik-form-init">
                <div class="row">
                    {$install_title}
                    <div class="col">
                        {$upload_field}
                    </div>
                    <div class="col">
                        {$upload_button}
                    </div>
                </div>
            </div>
        HTML;

        ////////////////////////////////////////
        // Modal Settings:
        ////////////////////////////////////////
        $this->page->additional_html(Components::modal(
            "module-settings-modal", 
            BsikIcon::fas("fa-cogs")."&nbsp;&nbsp;Update Settings",
            [
                Components::alert(
                    text : "<span class='alert-message'>alert text</span>",
                    color : "warning",
                    icon : "fas fa-edit",
                    classes : ["edit-settings-alert-info"]
                ),
                "<div class='form-modal-container'></div>"
            ],
            "",
            [
                ["button.btn.btn-secondary", ["data-action" => "save-module-settings"], "Save Module Settings", false],
                ["button.btn.btn-primary",   [],     "Cancel", true],
            ],
            [
                "close-white"    => true,
                "size"     => "lg",
                "backdrop" => "static",
                "keyboard" => "false",
            ]
        ));

        ////////////////////////////////////////
        // installed:
        ////////////////////////////////////////
        
        $modules_request = $this->api->call(endpoint : "core.get_installed");
        Trace::reg_var("loaded modules", $modules_request);

        $installed_modules = Components::modules_list(
            $modules_request->answer_code() === 200 ? $modules_request->answer_data() : [],
            "Manage Installed Modules",
            $this->page->engine
        );

        //Template:
        return <<<HTML
            <div class='container'>
                {$stats}
                {$install}
                {$installed_modules}
            </div>
        HTML;

    }
);


/****************************************************************************/
/*******************  View - endpoints  *************************************/
/****************************************************************************/
$view_endpoints_policy = new Priv\RequiredPrivileges();
$view_endpoints_policy->define(
    new Priv\Default\PrivModules(view : true, endpoints: true)
);
Modules::module("core")->register_view(
    view : new ModuleView(
        name        : "endpoints",
        privileges  : $view_endpoints_policy,
        settings    : new SettingsObject([
            "title"         => "Explore Core API",
            "description"   => "Explore and analyze application endpoints from all installed modules.",
        ])
    ),
    render : function() {

        /** @var Module $this */

        //Include confiramtion modal in page:
        $this->page->additional_html(Components::confirm());


        ////////////////////////////////////////
        // Endpoints Actions:
        ////////////////////////////////////////

        //only expose by privileges:
        $actions_bar   = Components::action_bar(
            actions : [
                $this->page::$issuer_privileges->if("modules.endpoints")->then( 
                    do   : ["action" => "refresh-endpoints-list", "text" => "Scan Endpoints", "icon" => "fa-binoculars"], 
                    else : [], 
                    args : []
                ),
                $this->page::$issuer_privileges->if("modules.endpoints")->then( 
                    do   : ["action" => "open-create-dynamic-endpoint", "text" => "Create Endpoint", "icon" => "fa-code-branch"], 
                    else : [], 
                    args : []
                )
            ],
            colors : [],
            class : ""
        ); 

        ////////////////////////////////////////
        // Endpoints List:
        ////////////////////////////////////////
        $endpoints_list_title = Components::title(text : "Platform Endpoints", attrs : ["class" => "module-title"]);
        
        $endpoints_list = $this->api->call(
            args     : [], 
            endpoint : "core.map_platform_api"
        );

        $loader = Components::loader(
            class : "loading-spinner",
            color : "primary",
            size  : "md",
            align : "center",
            show  : false,
            type  : "border",
            text  : "Loading..."    
        );

        $endpoint_list = $this->page->engine->render(
            name    : "endpoints_list",
            context : [
                "loading"           => $loader,
                "endpoints"         => $endpoints_list->answer_data()["endpoints"]          ?? [],
                "total_found"       => $endpoints_list->answer_data()["total_found"]        ?? 0,
                "total_failed"      => $endpoints_list->answer_data()["total_failed"]       ?? 0,
                "scanned_modules"   => $endpoints_list->answer_data()["scanned_modules"]    ?? []
            ]
        );

        //Template:
        return <<<HTML
            <div class='container'>
                {$actions_bar}
            </div>
            <div class='container sik-form-init'>
                {$endpoints_list_title}
                {$endpoint_list}
            </div>
        HTML;

    }
);