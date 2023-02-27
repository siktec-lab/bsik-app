<?php

use \Siktec\Bsik\CoreSettings;
use \Siktec\Bsik\Builder\Components;
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

$view_manage_policy = new Priv\RequiredPrivileges();
$view_manage_policy->define(
    new Priv\Default\PrivUsers(view : true)
);

$view_roles_policy = new Priv\RequiredPrivileges();
$view_roles_policy->define(
    new Priv\Default\PrivRoles(view : true)
);
/****************************************************************************/
/*******************  Register Module  **************************************/
/****************************************************************************/

Modules::register_module_once(new Module(
    name          : "users",
    privileges    : $module_policy,
    views         : ["manage", "roles"],
    default_view  : "manage"
)); 

/****************************************************************************/
/*******************  View - manage  ****************************************/
/****************************************************************************/

Modules::module("users")->register_view(
    view : new ModuleView(
        name        : "manage",
        privileges  : $view_manage_policy,
        settings    : new SettingsObject([
            "title"         => "Manage Users",
            "description"   => "Manage all registered users",
        ])
    ),
    render      : function() {

        /** @var Module $this */

        //Include confiramtion modal in page:
        $this->page->additional_html(Components::confirm());


        //Include some data for js use:
        $this->page->meta->data_object([
            "roles" => $this->db->map("id")->get("bsik_users_roles", null, ["id", "role", "color"])
        ]);

        ////////////////////////////////////////
        // Users Actions:
        ////////////////////////////////////////

        //only expose by privileges:
        $actions_bar   = Components::action_bar(
            actions : [
                $this->page::$issuer_privileges->if("users.create")->then( 
                    do   : ["action" => "open-create-user-modal", "text" => "Create User", "icon" => "fa-user-plus"], 
                    else : [], 
                    args : []
                ),
            ],
            colors : [],
            class : ""
        ); 

        ////////////////////////////////////////
        // Users Modals:
        ////////////////////////////////////////

        //Add a modal to render pages settings objects:
        $roles = $this->db->get("bsik_users_roles", null, "id, role, description");
        $this->page->additional_html(Components::modal(
            id      : "user-create-modal", 
            title   : BsikIcon::fas("fa-user-plus")."&nbsp;&nbsp;Create New User",
            body    : [$this->page->engine->render("user-create-form", ["roles" => $roles])],
            footer  : "",
            buttons : [
                ["button.btn.btn-secondary", ["data-action" => "create-user"], "Create User", false],
                ["button.btn.btn-primary",   [],     "Cancel", true],
            ],
            set     : [
                "close"    => true,
                "size"     => "lg",
                "backdrop" => "static",
                "keyboard" => "false",
            ]
        ));

        ////////////////////////////////////////
        // Users Table:
        ////////////////////////////////////////
        $users_table_title = Components::title(
            text : BsikIcon::fas("users", "", "me-2")."Registered Users", 
            attrs : ["class" => "module-title"]
        );
        $users_table_actions = [];
        
        //Add to table edit actions only if allowed:
        $this->page::$issuer_privileges->if("users.edit")->then(do : function(&$actions) { 
            $actions[] = ["name" => "edit_user_data", "title" => "Edit user data", "icon" => "fas fa-edit"];
        }, args : [&$users_table_actions]);

        $this->page::$issuer_privileges->if("admins.grant")->then(do : function(&$actions) { 
            $actions[] = ["name" => "user_additional_priv", "title" => "Grant additional privileges", "icon" => "fas fa-shield-alt"];
        }, args : [&$users_table_actions]);

        $this->page::$issuer_privileges->if("users.edit")->then(do : function(&$actions) { 
            $actions[] = ["name" => "user_edit_status", "title" => "Change user account status", "icon" => "fas fa-toggle-on"];
        }, args : [&$users_table_actions]);

        $this->page::$issuer_privileges->if("users.delete")->then(do : function(&$actions) { 
            $actions[] = ["name" => "user_delete", "title" => "Delete user account", "icon" => "fas fa-trash"];
        }, args : [&$users_table_actions]);

        $users_table = Components::dynamic_table(
            id                  : "users-table",
            ele_selector        : "table#users-table.table",
            option_attributes   : [
                //"data-toolbar"=>"#toolbar",
                "data-search"           =>"true",
                "data-show-refresh"     =>"true",
                "data-show-toggle"      =>"true",
                "data-show-fullscreen"  =>"false",
                "data-show-columns"     =>"true",
                "data-show-columns-toggle-all"  =>"true",
                //"data-detail-view"    =>"true",
                "data-show-export"      =>"true",
                "data-click-to-select"  =>"true",
                //"data-detail-formatter"       =>"detailFormatter",
                "data-minimum-count-columns"    =>"2",
                "data-show-pagination-switch"   =>"false",
                "data-pagination"       =>"true",
                "data-id-field"         =>"id",
                "data-page-list"        =>"[10, 25, 50, 100, all]",
                "data-show-footer"      =>"false",
                "data-side-pagination"  =>"server",
                "data-search-align"     =>"left"
            ],
            api     : CoreSettings::$url["manage"]."/api/users/",
            table   : "bsik_users", 
            fields  : [
                [
                    "field"             => 'id',
                    "title"             => 'UID',
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true,
                    "halign"            => "center"
                ],
                [
                    "field"             => 'NULL',
                    "title"             => 'Name',
                    "visible"           => true,
                    "searchable"        => false,
                    "sortable"          => false,
                    "halign"            => "center",
                    "align"             => "left",
                    "formatter"         => "Bsik.dataTables.formatters.name"
                ],
                [
                    "field"             => 'first_name',
                    "title"             => 'First Name',
                    "visible"           => false,
                    "searchable"        => true,
                    "sortable"          => true,
                    "halign"            => "center",
                    "align"             => "left"
                ],
                [
                    "field"             => "last_name",
                    "title"             => "Last Name",
                    "visible"           => false,
                    "searchable"        => true,
                    "sortable"          => true,
                    "halign"            => "center",
                    "align"             => "left"
                ],
                [
                    "field"             => "email",
                    "title"             => "Email Address",
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true,
                    "halign"            => "center",
                    "align"             => "left",
                    "formatters"        => "Bsik.dataTables.formatters.email"
                ],
                [
                    "field"             => 'comp_name',
                    "title"             => 'Company',
                    "visible"           => true,
                    "searchable"        => false,
                    "sortable"          => false,
                    "halign"            => "center",
                    "align"             => "left",
                    "formatter"         => "Bsik.dataTables.formatters.company"
                ],
                [
                    "field"             => 'account_status',
                    "title"             => 'Status',
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true,
                    "halign"            => "center",
                    "formatter"         => "Bsik.dataTables.formatters.status"
                ],
                [
                    "field"             => 'role',
                    "title"             => 'User Role',
                    "visible"           => true,
                    "searchable"        => false,
                    "sortable"          => true,
                    "halign"            => "center",
                    "formatter"         => "Bsik.dataTables.formatters.roles"
                ],
                [
                    "field"             => 'priv',
                    "title"             => 'Raw Priv',
                    "visible"           => false,
                    "searchable"        => false,
                    "sortable"          => false
                ],
                [
                    "field"             => 'created',
                    "title"             => 'Created, Seen',
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true,
                    "halign"            => "center",
                    "align"             => "left",
                    "formatter"         => "Bsik.dataTables.formatters.dates"
                ],
                [
                    "field"             => 'last_seen',
                    "title"             => 'Raw Last Seen',
                    "visible"           => false,
                    "searchable"        => true,
                    "sortable"          => false
                ],
                [
                    "field"             => 'operate',
                    "title"             => 'Actions',
                    "clickToSelect"     => false,
                    "events"            => "@js:Bsik.tableOperateEvents", // the function in module js
                    "formatter"         => null // Will use dynamic generated formatter only if operations are defined next
                ]
            ],
            operations : $users_table_actions
        );

        //Template:
        return <<<HTML
            <div class='container'>
                {$actions_bar}
            </div>
            <div class='container sik-form-init'>
                {$users_table_title}
                {$users_table}
            </div>
        HTML;
    }
);

/****************************************************************************/
/*******************  View - roles  ****************************************/
/****************************************************************************/

Modules::module("users")->register_view(
    view : new ModuleView(
        name        : "roles",
        privileges  : $view_roles_policy,
        settings    : new SettingsObject([
            "title"         => "Manage Users Roles",
            "description"   => "Set privileges groups as users roles",
        ])
    ),
    render      : function() {

        /** @var Module $this */

        //Include confiramtion modal in page:
        $this->page->additional_html(Components::confirm());

        ////////////////////////////////////////
        // Roles Actions:
        ////////////////////////////////////////
        //only expose by privileges:
        $actions_bar   = Components::action_bar(
            actions : [
                $this->page::$issuer_privileges->if("roles.create", "roles.grant")->then( do : ["action" => "open-create-role-modal", "text" => "New Role", "icon" => "shield"], else : [], args : []),
                ["action" => "open-role-report-modal", "text" => "Roles Report",    "icon" => "fa-file-alt"],
                ["action" => "open-create-role-modal", "text" => "Users Summary",   "icon" => "fa-user-shield"],
            ],
            colors : [],
            class : ""
        ); 

        ////////////////////////////////////////
        // Roles Table:
        ////////////////////////////////////////
        $roles_table_title = Components::title(text : "Defined Roles", attrs : ["class" => "module-title"]);
        $roles_table_actions = [];
        
        //Add to table edit actions only if allowed:
        $this->page::$issuer_privileges->if("roles.edit")->then(do : function(&$actions) { 
            $actions[] = ["name" => "edit_role", "title" => "Edit role name and description", "icon" => "fas fa-edit"];
        }, args : [&$roles_table_actions]);

        //Add to table priv edit actions only if allowed:
        $this->page::$issuer_privileges->if("roles.edit", "roles.grant")->then(do : function(&$actions) { 
            $actions[] = ["name" => "edit_roles_priv", "title" => "Edit role privileges", "icon" => "fas fa-shield-alt"];
        }, args : [&$roles_table_actions]);

        //Add to table view users only if allowed:
        $this->page::$issuer_privileges->if("roles.view")->then(do : function(&$actions) { 
            $actions[] = ["name" => "view_assigned_users", "title" => "View users list of this role group", "icon" => "fas fa-user-friends"];
        }, args : [&$roles_table_actions]);

        //Add to table delete actions only if allowed:
        $this->page::$issuer_privileges->if("roles.delete")->then(do : function(&$actions) { 
            $actions[] = ["name" => "remove_role", "title" => "Delete this role entry", "icon" => "fas fa-trash"];
        }, args : [&$roles_table_actions]);

        //The dynamic table
        $roles_table = Components::dynamic_table(
            id                  : "roles-table",
            ele_selector        : "table#roles-table.table",
            option_attributes   : [
                //"data-toolbar"=>"#toolbar",
                "data-search"           =>"true",
                "data-show-refresh"     =>"true",
                "data-show-toggle"      =>"true",
                "data-show-fullscreen"  =>"false",
                "data-show-columns"     =>"true",
                "data-show-columns-toggle-all"  =>"true",
                //"data-detail-view"    =>"true",
                "data-show-export"      =>"false",
                "data-click-to-select"  =>"true",
                //"data-detail-formatter"       =>"detailFormatter",
                "data-minimum-count-columns"    =>"2",
                "data-show-pagination-switch"   =>"false",
                "data-pagination"       =>"true",
                "data-id-field"         =>"id",
                "data-page-list"        =>"[10, 25, 50, 100, all]",
                "data-show-footer"      =>"false",
                "data-side-pagination"  =>"server",
                "data-search-align"     =>"left"
            ],
            api     : CoreSettings::$url["manage"]."/api/users/",
            table   : "bsik_users_roles", 
            fields  : [
                [
                    "field"             => 'id',
                    "title"             => 'ID',
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true
                ],
                [
                    "field"             => "role",
                    "title"             => "Role",
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true,
                    "formatter"         => "Bsik.dataTables.formatters.role_name"
                ],
                [
                    "field"             => "description",
                    "title"             => "Description",
                    "visible"           => true,
                    "searchable"        => true,
                    "sortable"          => true,
                    "align"             => "left"
                ],
                [
                    "field"             => 'tags',
                    "title"             => 'Granted Privileges',
                    "searchable"        => false,
                    "sortable"          => false,
                    "formatter"         => "Bsik.dataTables.formatters.role_priv_tags"
                ],
                [
                    "field"             => 'color',
                    "title"             => 'Color',
                    "visible"           => false,
                    "searchable"        => false,
                    "sortable"          => false,
                ],
                [
                    "field"             => 'operate',
                    "title"             => 'Actions',
                    "clickToSelect"     => false,
                    "width"             => 150,
                    "events"            => "@js:Bsik.tableOperateEvents", // the function in module js
                    "formatter"         => null // Will use dynamic generated formatter only if operations are defined next
                ]
            ],
            operations : $roles_table_actions
        );

        ////////////////////////////////////////
        // Role Modals:
        ///////////////////////////////////////
        $loader = Components::loader(
            class : "loading-spinner",
            color : "primary",
            size  : "md",
            align : "center",
            show  : false,
            type  : "border",
            text  : "Loading..."    
        );

        //Edit privileges modal:
        $priv_form_header = Components::alert(
            text    : "Currently editing: <span class='role-name'>name</span><span class='role-description'>description</span>",
            color   : "warning",
            icon    : "fas fa-shield-alt",
            dismiss : false,
            //classes : ["mt-3"]
        );
        $this->page->additional_html(Components::modal(
            "roles-editing-modal", 
            "Edit role group privileges",
            [
                $priv_form_header, 
                $loader, 
                "<div class='container-fluid priv-form-container'>Form....</div>"
            ],
            "",
            [
                ["button.btn.btn-secondary", ["data-action" => "save-priv-edits"], "Save Changes", false],
                ["button.btn.btn-primary",   [],     "Cancel", true],
            ],
            [
                "close"    => false,
                "size"     => "lg",
                "backdrop" => "static",
                "keyboard" => "false",
            ]
        ));

        //Create new role modal:
        $create_form_header = Components::alert(
            text    : "Add a custom role with its related data and privileges group - users who will be assigned with this role will inherit the role privileges.",
            color   : "info",
            icon    : "fas fa-shield-alt",
            dismiss : false,
            //classes : ["mt-3"]
        );
        $emptypriv = Components::all_empty_privileges();
        $emptypriv_form = Components::privileges_form_check(
            $this->page->engine, 
            "createrole_priv_form", 
            $emptypriv->all_privileges(), 
            $emptypriv->all_meta()
        );
        $dataForm = $this->page->engine->render("role-data-form", [
            "id" => "create-role-data-form"
        ]);

        $this->page->additional_html(Components::modal(
            "role-create-modal", 
            "Create new role",
            [
                $create_form_header,
                "<div class='container-fluid modal-sub-header'><h4>Name and Metadata:</h4></div>",
                "<div class='container-fluid roledata-form-container'>{$dataForm}</div>",
                "<div class='container-fluid modal-sub-header'><h4>Set Privileges:</h4></div>",
                "<div class='container-fluid rolepriv-form-container'>{$emptypriv_form}</div>"
            ],
            "",
            [
                ["button.btn.btn-secondary.text-white", ["data-action" => "save-new-role"], "Create Role", false],
                ["button.btn.btn-primary.text-white",   [],     "Cancel", true],
            ],
            [
                "close"    => false,
                "size"     => "lg",
                "backdrop" => "static",
                "keyboard" => "false",
            ]
        ));

        //Edit role data modal:
        $role_edit_form_header = Components::alert(
            text    : "Currently editing: <span class='role-name'>name</span>",
            color   : "warning",
            icon    : "fas fa-user-shield",
            dismiss : false,
            //classes : ["mt-3"]
        );
        $editDataForm = $this->page->engine->render("role-data-form", [
            "id" => "edit-role-data-form"
        ]);
        $this->page->additional_html(Components::modal(
            "role-edit-modal", 
            "Edit Role",
            [
                $role_edit_form_header,
                "<div class='container-fluid modal-sub-header'><h4>Name and Metadata:</h4></div>",
                "<div class='container-fluid roledata-form-container'>{$editDataForm}</div>"
            ],
            "",
            [
                ["button.btn.btn-secondary", ["data-action" => "save-edited-role"], "Save Role", false],
                ["button.btn.btn-primary",   [],     "Cancel", true],
            ],
            [
                "close"    => false,
                "size"     => "lg",
                "backdrop" => "static",
                "keyboard" => "false",
            ]
        ));

        //Render Template:
        return $this->page->engine->render("roles-view", [
            "actions_bar"       => $actions_bar,
            "roles_table_title" =>$roles_table_title,
            "roles_table"       =>$roles_table,
        ]);
    }
);
