/******************************************************************************/
// Created by: SIKTEC.
// Release Version : 1.0.0
// Creation Date: 2021-03-16
// Copyright 2021, SIKTEC.
/******************************************************************************/
/*****************************      Changelog       ****************************
1.0.0:
    ->initial 
*******************************************************************************/
document.addEventListener("DOMContentLoaded", function(event) {

    console.log(Bsik, Bsik.loaded.module);
    /** Global module code: *************************************************/
    // Bsik.module[Bsik.loaded.module.name].publish_statuses = [
    //     { "value" : 1, "text" : "draft"  },
    //     { "value" : 2, "text" : "active" }
    // ];
    // Bsik.module[Bsik.loaded.module.name].shipping_templates = JSON.parse($(document).find("meta[name='shippping_templates']").attr("content") || '[]');
    
    //Attach MaxType for inputs:
    Bsik.core.bindAllMaxType("[max-type]", true);

    /******************************************************************/
    /** Pages Create: *************************************************/
    /******************************************************************/
    if (Bsik.loaded.module.sub === "manage") {

        (function($, window, document, Bsik, undefined) {

            //Set modals:
            Bsik.modals.createUser = new bootstrap.Modal(
                document.getElementById('user-create-modal'),
                Bsik.core.helpers.objAttr.getDataAttributes("#user-create-modal")
            );
            Bsik.modals.createUserElement = $(Bsik.modals.createUser._element);

            //Table Formatters:
            Bsik.dataTables.formatters.name = function(value, data_row, index, header) {
                return Bsik.core.escapeHtml(data_row.first_name + " " + data_row.last_name);
            };
            Bsik.dataTables.formatters.email = function(value, data_row, index, header) {
                return Bsik.core.escapeHtml(value);
            };
            Bsik.dataTables.formatters.company = function(value, data_row, index, header) {
                return value ? `<i class="fas fa-building fw-normal"></i>&nbsp;&nbsp;${ Bsik.core.escapeHtml(value) }` : '-';
            };
            Bsik.dataTables.formatters.status = function(value, data_row, index, header) {
                let status  = ["ACTIVE","VALIDATING","SUSPENDED"][value];
                let color   = ["#00a514e0","#008fd3","#b70008e0"][value];
                let icon    = ["fa-running","fa-paper-plane","fa-user-slash"][value];
                return `
                    <span class="role-name-tag" style="background-color:${color}">
                        <i class="fas ${icon}"></i>
                        ${status}
                    </span>
                `;
            };
            Bsik.dataTables.formatters.roles = function(value, data_row, index, header) {
                if (value && Bsik.loaded.module.data.roles) {
                    let roleName = "<strong>" + (Bsik.loaded.module.data.roles.hasOwnProperty(value) ? Bsik.loaded.module.data.roles[value].role.toUpperCase() : value) + "</strong>";
                    roleName += data_row.priv ? " <i class='fas fa-angle-double-right'></i> OVERRIDDEN" : "";
                    return `
                        <span class="role-name-tag" style="background-color:${ Bsik.loaded.module.data.roles.hasOwnProperty(value) ? Bsik.loaded.module.data.roles[value].color : "transparent" }">
                            <i class="fas fa-user-shield"></i>
                            ${ roleName }
                        </span>
                    `;
                } else if (data_row.priv) {
                    return `
                        <span class="role-name-tag" style="background-color:transparent">
                            <i class="fas fa-user-shield"></i>
                            User Specific
                        </span>
                    `;
                }
                return `Not Assigned`;
            };
            Bsik.dataTables.formatters.dates = function(value, data_row, index, header) {
                let since_seen = data_row.last_seen ? Bsik.core.helpers.timeFromNow(data_row.last_seen) : false;
                return `
                    <small class="text-muted ps-4"><i class='fas fa-calendar'></i>&nbsp;${value}</small>
                    ${since_seen ? "<br /><small class='text-muted ps-4'><i class='fas fa-eye'></i>&nbsp;" + since_seen + "</small>" : ""}
                `;
            };

            Bsik.module[Bsik.loaded.module.name].manage = {

                createForm : $("#create-user-form"),
                createNewUser : function($btn) {
                    let data = Bsik.core.serializeToObject(Bsik.module.users.manage.createForm, [], {
                        "create-input-about"            : "about",
                        "create-input-company-name"     : "company-name",
                        "create-input-password"         : "password",
                        "create-input-confirm-password" : "confirm-password",
                        "create-input-email"            : "email",
                        "create-input-first-name"       : "first-name",
                        "create-input-last-name"        : "last-name",
                        "create-input-role"             : "role",
                        "create-input-status"           : "status"
                    });

                    //Remove previous validation errors:
                    Bsik.modals.createUserElement.find(".is-invalid, .is-valid").each(function(){
                        $(this).removeClass("is-invalid is-valid");
                    });

                    $btn.prop("disabled", true);
                    Bsik.core.apiRequest(null, "users.create_user_account", data, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            console.log(jqXhr.responseJSON);
                            //Handle validation & server errors:
                            if (jqXhr.responseJSON && jqXhr.responseJSON.code === 400 && jqXhr.responseJSON.data) {
                                //Those are validation errors:
                                for (const [field, messages] of Object.entries(jqXhr.responseJSON.data)) {
                                    let $field = Bsik.module.users.manage.createForm.find(`[id='create-input-${field}'`);
                                    if ($field.length && !$.isEmptyObject(messages)) 
                                        $field.addClass("is-invalid");
                                }
                            } else {
                                let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                                Bsik.notify.error(`Create new user error - ${error.join()}`, true);
                            }
                        },
                        success: function(res) {
                            // console.log(res);
                            Bsik.notify.bubble("info", `Created new user account successfully.`);
                            $("#users-table").bootstrapTable('refresh', { silent : true });
                            Bsik.modals.createUser.hide();
                        },
                        complete : function() {
                            $btn.prop("disabled", false);
                        }
                    });
                }
            };

            /***************************************************************/
            // user actions:
            /***************************************************************/
            $.extend(Bsik.userEvents, {
                "click open-create-user-modal" : function(e) {
                    Bsik.modals.createUser.show();
                },
                "click create-user" : function(e) {
                    let $btn = $(this);
                    Bsik.module.users.manage.createNewUser($btn);
                }
            });
            //Attach user actions:
            Bsik.core.helpers.onActions("click change", "data-action", Bsik.userEvents);

            /***************************************************************/
            // Dynamic table actions:
            /***************************************************************/
            //TODO: Build those user table actions please!
            Bsik.tableOperateEvents = {
                'click .edit_user_data': function(e, value, row, index) {
                    console.log("edit_user_data", row); 
                },
                'click .user_additional_priv': function(e, value, row, index) {
                    console.log("user_additional_priv", row); 
                },
                'click .user_edit_status': function(e, value, row, index) {
                    console.log("user_edit_status", row); 
                },
                'click .user_delete': function(e, value, row, index) {
                    console.log("user_delete", row);         
                }
            };

        })(jQuery, this, document, window.Bsik);
    }

    if (Bsik.loaded.module.sub === "roles") {

        (function($, window, document, Bsik, undefined) {

            /***************************************************************/
            // Set modals
            /***************************************************************/

            /**** Edit privileges modal **************************************/
            Bsik.modals.editPrivileges = new bootstrap.Modal(
                document.getElementById('roles-editing-modal'),
                Bsik.core.helpers.objAttr.getDataAttributes("#roles-editing-modal")
            );
            Bsik.modals.editPrivilegesElement = $(Bsik.modals.editPrivileges._element);
            Bsik.modals.editPrivilegesLoader = Bsik.modals.editPrivilegesElement.find(".loading-spinner");
            //When changed privileges checkbox mark it:
            Bsik.modals.editPrivilegesElement.on("change", ".priv-checkbox", function(){
                let saved   = $(this).data("current") === "granted";
                let current = $(this).is(":checked");
                if (saved !== current) {
                    $(this).closest("li").addClass("revised");
                } else {
                    $(this).closest("li").removeClass("revised");
                }
            });

            /**** Create role modal **************************************/
            Bsik.modals.createRole = new bootstrap.Modal(
                document.getElementById('role-create-modal'),
                Bsik.core.helpers.objAttr.getDataAttributes("#role-create-modal")
            );
            Bsik.modals.createRoleElement = $(Bsik.modals.createRole._element);

            //When color picker is used set the value:
            Bsik.modals.createRoleElement.on("change", ".form-control-color", function(){
                $(this).prev("input").val(
                    $(this).val()
                );
            });

            /**** Edit role modal **************************************/
            Bsik.modals.editRole = new bootstrap.Modal(
                document.getElementById('role-edit-modal'),
                Bsik.core.helpers.objAttr.getDataAttributes("#role-edit-modal")
            );
            Bsik.modals.editRoleElement = $(Bsik.modals.editRole._element);

            //When color picker is used set the value:
            Bsik.modals.editRoleElement.on("change", ".form-control-color", function(){
                $(this).prev("input").val(
                    $(this).val()
                );
            });

            /***************************************************************/
            // module specific js
            /***************************************************************/
            Bsik.module.users.roles = {

                //General methods:
                getSelectedPrivileges : function(form) {
                    let $form = $(form);
                    let selected = {};
                    if ($form.length) {
                        let groups = $form.find(".tags");
                        groups.each(function(){
                            let group = $(this);
                            let tags  = group.find(".priv-checkbox");  
                            let privs = {};
                            tags.each(function(){
                                let status = $(this).is(":checked");
                                let name   = $(this).data("privtag");
                                privs[name] = status;
                            });
                            selected[group.data("privgroup")] = privs;
                        });
                    }
                    return selected;
                },

                getRoleFormMeta : function(form) {
                    let $form = $(form);
                    let meta = {};
                    if ($form.length) {
                        let fields = $form.find("[id^='input-role']");
                        fields.each(function(){
                            meta[$(this).attr("name")] = $(this).val();
                        });
                    }
                    return meta; 
                },

                defaultRoleFormData : {
                    role_name : "",
                    role_desc : "",
                    role_color : "",
                },

                setRoleFormMeta : function(form, data = {}) {
                    let meta = $.extend({}, Bsik.module.users.roles.defaultRoleFormData , data);
                    let $form = $(form);
                    if ($form.length) {
                        for (const [field, value] of Object.entries(meta)) {
                            let $field = $form.find(`[name='${field}'`);
                            if ($field.length) 
                                $field.val(value);
                        }
                        //handle color picker:
                        let $picker = $form.find(".form-control-color");
                        if ($picker.length) {
                            if (meta.role_color.length) {
                                $picker.val(meta.role_color);
                            } else {
                                $picker.trigger("change");
                            }
                        }
                    }
                },

                //Edit privileges related:
                openPrivilegesModal : function(row) {
                    console.log(row);

                    //Set values:
                    let $formContainer = Bsik.modals.editPrivilegesElement.find(".priv-form-container");
                    Bsik.modals.editPrivilegesElement.find("span.role-name").text(row.role);
                    Bsik.modals.editPrivilegesElement.find("span.role-description").text(row.description);

                    //Initiate:
                    $formContainer.html("");
                    Bsik.modals.editPrivileges.show();
                    Bsik.modals.editPrivilegesLoader.removeClass("d-none");
                    
                    //Get current privileges form:
                    Bsik.core.apiRequest(null, "users.get_role_privileges", { role : row.id, form: true }, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                            Bsik.notify.error(`Save revised settings error - ${error.join()}`, true);
                            console.log(jqXhr.responseJSON);
                        },
                        success: function(res) {
                            console.log("success", res);
                            if (typeof res.data === 'object' && res.data.hasOwnProperty("form")) {
                                //Append form to body:
                                $formContainer.html(res.data.form);
                                Bsik.modals.editPrivilegesElement.data("roleId", row.id);

                            } else {
                                Bsik.notify.error(`Unknown error occurred can't render privileges form`, true);
                            }
                        },
                        complete : function() {
                            console.log("complete");
                            Bsik.modals.editPrivilegesLoader.addClass("d-none");
                        }
                    });
                },

                saveSelectedEditedPrivileges : function(btn, id, selected) {

                    //Disable save button
                    $(btn).prop("disabled", true);

                    //Get current privileges form:
                    Bsik.core.apiRequest(null, "users.edit_role_privileges", { role : id, priv: selected }, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                            Bsik.notify.error(`Save revised privileges error - ${error.join()}`, true);
                            console.log(jqXhr.responseJSON);
                        },
                        success: function(res) {
                            console.log("success", res);
                            Bsik.notify.info(`Saved revised role privileges`, true);
                            $("#roles-table").bootstrapTable('refresh', { silent : true });
                            Bsik.modals.editPrivileges.hide();
                        },
                        complete : function() {
                            $(btn).prop("disabled", false);
                        }
                    });

                },

                //Create modal related:
                resetCreateRoleModal : function() {
                    Bsik.module.users.roles.removeCreateRoleModalValidationErrors();
                    Bsik.modals.createRoleElement.find(".form-control-color").trigger("change");
                    //Disable checkboxes:
                    Bsik.modals.createRoleElement.find(".priv-checkbox").prop("checked", false);
                },

                removeCreateRoleModalValidationErrors : function() {
                    Bsik.modals.createRoleElement.find(".is-invalid, .is-valid").each(function(){
                        $(this).removeClass("is-invalid is-valid");
                    });
                },

                openCreateRoleModal : function() {
                    //$btn.prop("disabled", true);
                    Bsik.module.users.roles.resetCreateRoleModal();
                    Bsik.module.users.roles.setRoleFormMeta("#create-role-data-form");
                    Bsik.modals.createRole.show();
                },

                submitNewRoleFromModal : function(btn) {
                    
                    //Remove previous validation errors:
                    Bsik.module.users.roles.removeCreateRoleModalValidationErrors();
                    
                    //Disable save button
                    $(btn).prop("disabled", true);


                    let selected = Bsik.module.users.roles.getSelectedPrivileges("#createrole_priv_form");
                    let meta = Bsik.module.users.roles.getRoleFormMeta("#create-role-data-form");
                    
                    console.log(meta, selected);
                    //Get current privileges form:
                    Bsik.core.apiRequest(null, "users.create_new_role", { 
                        role_name   : meta.role_name,
                        role_desc   : meta.role_desc,
                        role_color  : meta.role_color,
                        priv        : selected 
                    }, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            console.log(jqXhr.responseJSON);
                            //Handle validation vs server errors:
                            if (jqXhr.responseJSON && jqXhr.responseJSON.code === 400 && jqXhr.responseJSON.data) {
                                //Those are validation errors:
                                for (const [field, messages] of Object.entries(jqXhr.responseJSON.data)) {
                                    let $field = $("#create-role-data-form").find(`[name='${field}'`);
                                    if ($field.length && !$.isEmptyObject(messages)) $field.addClass("is-invalid");
                                }
                            } else {
                                let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                                Bsik.notify.error(`Create new role error - ${error.join()}`, true);
                            }
                        },
                        success: function(res) {
                            console.log("success", res);
                            if (!$.isEmptyObject(res.errors)) {
                                for (const error of res.errors) {
                                    Bsik.notify.warn(error, true);
                                }
                            } else {
                                Bsik.notify.info(`Created new role successfully.`, true);
                                Bsik.modals.createRole.hide();
                            }
                            $("#roles-table").bootstrapTable('refresh', { silent : true });
                        },
                        complete : function() {
                            $(btn).prop("disabled", false);
                        }
                    });
                },

                //Edit role:
                openEditRoleModal : function(row) {

                    console.log(row);

                    //Set values:
                    Bsik.modals.editRoleElement.find("span.role-name").text(row.role);
                    Bsik.module.users.roles.setRoleFormMeta("#edit-role-data-form", { 
                        role_name   : row.role,
                        role_desc   : row.description ?? "",
                        role_color  : row.color ?? ""
                    });
                    Bsik.modals.editRoleElement.data("roleId", row.id);

                    //Remove previous validation errors:
                    Bsik.modals.editRoleElement.find(".is-invalid, .is-valid").each(function(){
                        $(this).removeClass("is-invalid is-valid");
                    });

                    //Show modal:
                    Bsik.modals.editRole.show();
    
                },

                saveEditedDataRole : function(btn) {
                    
                    //Remove previous validation errors:
                    Bsik.modals.editRoleElement.find(".is-invalid, .is-valid").each(function(){
                        $(this).removeClass("is-invalid is-valid");
                    });

                    //Disable save button
                    $(btn).prop("disabled", true);

                    let meta = Bsik.module.users.roles.getRoleFormMeta("#edit-role-data-form");
                    
                    console.log(meta);

                    //Get current privileges form:
                    Bsik.core.apiRequest(null, "users.save_role_data", { 
                        role_name   : meta.role_name,
                        role_desc   : meta.role_desc,
                        role_color  : meta.role_color,
                        role        : Bsik.modals.editRoleElement.data("roleId") 
                    }, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            console.log(jqXhr.responseJSON);
                            //Handle validation vs server errors:
                            if (jqXhr.responseJSON && jqXhr.responseJSON.code === 400 && jqXhr.responseJSON.data) {
                                //Those are validation errors:
                                for (const [field, messages] of Object.entries(jqXhr.responseJSON.data)) {
                                    let $field = $("#edit-role-data-form").find(`[name='${field}'`);
                                    if ($field.length && !$.isEmptyObject(messages)) $field.addClass("is-invalid");
                                }
                            } else {
                                let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                                Bsik.notify.error(`Save role error - ${error.join()}`, true);
                            }
                        },
                        success: function(res) {
                            console.log("success", res);
                            if (!$.isEmptyObject(res.errors)) {
                                for (const error of res.errors) {
                                    Bsik.notify.warn(error, true);
                                }
                            } else {
                                Bsik.notify.info(`Saved role edits successfully.`, true);
                                Bsik.modals.editRole.hide();
                            }
                            $("#roles-table").bootstrapTable('refresh', { silent : true });
                        },
                        complete : function() {
                            $(btn).prop("disabled", false);
                        }
                    });
                },
                //Delete role procedure:
                deleteDefineRole : function(dynTable, role, roleId, rowId) {
                    Bsik.core.helpers.getConfirmation(
                        `Please confirm the deletion of "<strong>${role}</strong>" role - All the related data will be removed and all granted users will lose their privileges.`,
                        function yes(event) {
                            //api call:
                            Bsik.core.apiRequest(null, "users.delete_role", { role : roleId }, {
                                error: function(jqXhr, textStatus, errorMessage) {
                                    let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                                    Bsik.notify.error(`Delete role error - ${error.join()}`, true);
                                    console.log(jqXhr.responseJSON);
                                },
                                success: function(res) {
                                    Bsik.notify.info(`Deleted role : ${role}`, true);
                                    $("#roles-table").bootstrapTable('refresh', { silent : true });
                                    Bsik.modals.editPrivileges.hide();
                                },
                                complete : function() {
                                    event.data.modal.hide();
                                }
                            });
                        }, 
                        function no(event){
                            event.data.modal.hide();
                        }
                    );
                }
            };

            /***************************************************************/
            // Dynamic table formaters:
            /***************************************************************/
            Bsik.dataTables.formatters.role_name = function(value, data_row, index, header) {
                return `
                    <span class="role-name-tag" style="background-color:${data_row.color ?? "#444444"}">
                        <i class="fas fa-user-shield"></i>
                        ${value.toUpperCase()}
                    </span>
                `;
            };
            Bsik.dataTables.formatters.role_priv_tags = function(value, data_row, index, header) {
                let display = [];
                let groups = Bsik.core.jsonParse(value) ?? {};
                for (const [group, granted] of Object.entries(groups)) {
                    if (granted.length === 0) continue;
                    display.push(`
                        <span class="group-granted-display">
                            <span class="single-group">
                                <i class="fas fa-shield-alt"></i>
                                <strong>${group}</strong>
                                <i class="fas fa-angle-double-right"></i>
                                ${granted.split(',').join(', ')}
                            </span>
                        </span>
                    `);
                }
                return display.join('');
            };

            /***************************************************************/
            // Dynamic table actions:
            /***************************************************************/
            Bsik.tableOperateEvents = {
                'click .edit_role': function(e, value, row, index) {
                    Bsik.module.users.roles.openEditRoleModal(row);
                },
                'click .edit_roles_priv': function(e, value, row, index) {
                    Bsik.module.users.roles.openPrivilegesModal(row);
                },
                'click .view_assigned_users': function(e, value, row, index) {
                    console.log("view_assigned_users",'You click like action, row: ' + JSON.stringify(row));
                },
                'click .remove_role': function(e, value, row, index) {
                    console.log("delete row called!", row);
                    Bsik.module.users.roles.deleteDefineRole(this.$el, row.role, row.id, row.id);
                    
                },
            };

            /***************************************************************/
            // User actions
            /***************************************************************/
            $.extend(Bsik.userEvents, {
                "click open-create-role-modal" : function(e) {
                    Bsik.module.users.roles.openCreateRoleModal($(this));
                },
                'click save-new-role': function(e) {
                    let $savebtn = $(this);
                    Bsik.core.helpers.getConfirmation(
                        `Please confirm the creation of this role.`,
                        function yes(event) {
                            event.data.modal.hide();
                            Bsik.module.users.roles.submitNewRoleFromModal($savebtn);
                        }, 
                        function no(event){
                            event.data.modal.hide();
                        }
                    );
                },
                "click save-priv-edits" : function() {
                    let selected = Bsik.module.users.roles.getSelectedPrivileges("#role_priv_form");
                    Bsik.module.users.roles.saveSelectedEditedPrivileges(
                        $(this),
                        Bsik.modals.editPrivilegesElement.data("roleId"),
                        selected
                    );
                },
                'click save-edited-role': function(e) {
                    let saveBtn = $(this);
                    Bsik.core.helpers.getConfirmation(
                        `Please confirm the edits of this role.`,
                        function yes(event) {
                            event.data.modal.hide();
                            Bsik.module.users.roles.saveEditedDataRole(saveBtn);
                        }, 
                        function no(event){
                            event.data.modal.hide();
                        }
                    );
                },
            });
            //Attach user actions:
            Bsik.core.helpers.onActions("click change", "data-action", Bsik.userEvents);


        })(jQuery, this, document, window.Bsik);
    }
});
