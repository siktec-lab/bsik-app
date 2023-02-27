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

    /** Global module code: *************************************************/
    console.log(Bsik);
    // Bsik.module[Bsik.loaded.module.name].publish_statuses = [
    //     { "value" : 1, "text" : "draft"  },
    //     { "value" : 2, "text" : "active" }
    // ];
    // Bsik.module[Bsik.loaded.module.name].shipping_templates = JSON.parse($(document).find("meta[name='shipping_templates']").attr("content") || '[]');
    
    /*********************************************************************/
    /** Module Settings: *************************************************/
    /*********************************************************************/

    if (Bsik.loaded.module.sub === "modules") {

        (function($, window, document, Bsik, undefined) {

            console.log(window);
            console.log(Bsik);


            Bsik.modals.settings = new bootstrap.Modal(
                document.getElementById('module-settings-modal'),
                Bsik.core.helpers.objAttr.getDataAttributes("#module-settings-modal")
            );
            Bsik.modals.settingsElement = $(Bsik.modals.settings._element);

            //Settings Loader and Parser:
            let settings = new Bsik.SettingsParser(Bsik.modals.settingsElement, true);

            Bsik.module[Bsik.loaded.module.name].modules = {

                filterList : null,
                /************ Initiate the base js object: ******************/
                uploadFile($btn) {        
                    let file = $("#input-manual-module")[0].files[0];
                    if (!file) {
                        Bsik.notify.bubble("warn", "Select a ZIP module file first.");
                        return;
                    }
                    Bsik.core.apiRequest(null, "core.install_module_uploaded", { module_file : file }, 
                        {
                            error: function(jqXhr, textStatus, errorMessage) {
                                let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                                Bsik.notify.error(`Operation module install failed - ${error.join()}`, true);
                            },
                            success: function(res) {
                                console.log(arguments);
                                let text = "";
                                let type = "error";
                                switch (res.message) {
                                    case "OK" :
                                    case "success" : {
                                        text = "Module installation success refresh the page for the changes to take effect.";
                                        type = "info";
                                    } break;
                                    case "upload_failed" : {
                                        text = `Failed to upload module archive - ${res.errors ? res.errors.join('|') : "unknown"}`;
                                    } break;
                                    case "invalid" : {
                                        text = `Failed to install module archive is not valid - Err {${res.errors ? res.errors.join('|') : "unknown"}}`;
                                    } break;
                                    case "install_error" : {
                                        let errors = [];
                                        if (res.errors && Array.isArray(res.errors)) {
                                            errors = '<br />' + res.errors.join('<br />');
                                        } else if (res.errors && typeof res.errors === 'object') {
                                            for (const err in res.errors) {
                                                errors.push(`${res.errors[err]} in ${err}`);
                                            }
                                            errors = '<br />' + errors.join('<br />');
                                        } else {
                                            errors = 'unknown';
                                        }
                                        text = `Failed to install module files jsonc is not valid - ${errors}`;
                                    } break;
                                    default: {
                                        text = `Unknown error while installing module - Err {${res.errors ? res.errors[0] : "unknown"}}`;
                                    }
                                }
                                Bsik.notify.message(type, text, true);
                            }
                        },
                        true
                    );
                },
                statusModule(id, status, $btn, $list) {
                    $btn.prop("disabled", true);
                    Bsik.core.apiRequest(null, "core.status_module", { module_id : id, module_status : status }, 
                        {
                            error: function(jqXhr, textStatus, errorMessage) {
                                Bsik.notify.error(`Changing module state failed - ${errorMessage} - Refresh page please`, true);
                            },
                            success: function(res) {
                                console.log(res);
                                if (res.data.length) {
                                    let status = res.data[0].status;
                                    let updates = res.data[0].updates;
                                    let $tag = $list.find(".tag-module-status");
                                    let $update = $list.find("button[data-action='update-module']");
                                    //Clear styling:
                                    $tag.removeClass("module-status-disable module-status-active module-status-has-update");
                                    //Update tag:
                                    if (updates === 1 && status === 1) {
                                        $update.show();
                                        $tag.text("Update Pending");
                                        $tag.addClass("module-status-has-update");
                                    } else {
                                        $update.hide();
                                        $tag.text(status === 1 ? "Activated" : "Disabled");
                                        $tag.addClass("module-status-" + (status === 1 ? "active" : "disable"));
                                    }
                                    //Update button:
                                    $btn.text(status === 1 ? "Disable" : "Activate");
                                    $btn.data("current", status === 1 ? "disable" : "active");
                                }
                            },
                            complete: function() {
                                console.log("complete");
                                $btn.prop("disabled", false);
                            }
                        }
                    );
                },
                uninstallModule(id, $btn, $row, finish) {
                    console.log(id, $row);
                    $btn.prop("disabled", true);
                    Bsik.core.apiRequest(null, "core.uninstall_module", { module_name : id }, 
                        {
                            error: function(jqXhr, textStatus, errorMessage) {
                                let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                                Bsik.notify.error(`Operation module uninstall failed - ${error.join()}`, true);
                            },
                            success: function(res) {
                                console.log(arguments);
                                if (res.code === 200) {
                                    //TODO: show which modules were uninstalled...

                                    Bsik.notify.bubble("info", `Uninstalled '${id}' module successfully.`);
                                    
                                    //Remove row:
                                    $row.fadeOut("slow", function() {
                                        $(this).remove();
                                    });

                                    //Remove menu entry:
                                    for (const menuEntry of res.data.menu) {
                                        console.log(menuEntry);
                                        Bsik.menu.remove(menuEntry);
                                    }

                                } else {
                                    //TODO: maybe some other problem?
                                }
                            },
                            complete: function() {
                                $btn.prop("disabled", false);
                                finish.call();
                            }
                        },
                        true
                    );
                },
                openSettingsModal : function($btn, module = "") {
                    //First get the settings:
                    $btn.prop("disabled", true);
                    settings.loadSettings(
                        "core.get_module_settings", 
                        { module_name : module }, 
                        function() {
                            console.log(this, arguments);
                            $btn.prop("disabled", false);
                            //Attach to modal:
                            let $body = this.container.find(`div.form-modal-container`).eq(0);
                            $body.html(this.formHtml);
                            //Set header:
                            let header   = this.container.find(".edit-settings-alert-info > span.alert-message").eq(0);
                            header.html(`You are editing <strong>${module}</strong> module - Inherited and Overridden settings are marked with a tag.`);
                            //Open modal:
                            Bsik.modals.settings.show();
                        }
                    );
                },
                saveModuleSettings : function($btn) {
                    let $form = Bsik.modals.settingsElement.find("#dyn-form-settings");
                    let revised = settings.getRevisedSettings($form);
                    let module_name = $form.data("module");
                    console.log(revised, module_name);
                    // return;
                    $btn.prop("disabled", true);
                    Bsik.core.apiRequest(null, "core.save_module_settings", { 
                            settings    : JSON.stringify(revised),
                            module_name : module_name
                    }, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                            Bsik.notify.error(`Save revised settings error - ${error.join()}`, true);
                            console.log(jqXhr.responseJSON);
                        },
                        success: function(res) {
                            console.log(res);
                            Bsik.notify.bubble("info", `Saved revised settings successfully.`);
                            Bsik.modals.settings.hide();
                        },
                        complete : function() {
                            $btn.prop("disabled", false);
                        }
                    });
                },
            };

            /************* Set user actions **********/
            $.extend(Bsik.userEvents, {
                "click upload-module-btn" : function(e) {
                    Bsik.module.core.modules.uploadFile($(this));
                },
                "click uninstall-module" : function(e) {
                    let $btn = $(this);
                    let $row = $btn.closest(".module-list").eq(0);
                    let id = $row.data("id");
                    Bsik.core.helpers.getConfirmation(
                        `Please confirm the deletion of "<strong>${id}</strong>" module - All the related data will be removed from your system and lost.`,
                        function yes(event) {
                            //event.data.modal.hide();
                            Bsik.module.core.modules.uninstallModule(id, $btn, $row, ()=>{
                                event.data.modal.hide();
                            });
                            //Bsik.module.amazon.publish.publish_single_product(row.id, row.publish_upc, "#publish-que-table");
                        }, 
                        function no(event){
                            event.data.modal.hide();
                        }
                    );
                },
                "click status-module" : function(e) {
                    let $btn    = $(this);
                    let $row    = $btn.closest(".module-list").eq(0);
                    let id      = $row.data("id");
                    let status  = $btn.data("current");
                    Bsik.core.helpers.getConfirmation(
                        `You are about to disable "<strong>${id}</strong>" module - Please confirm this action.`,
                        function yes(event) {
                            event.data.modal.hide();
                            Bsik.module.core.modules.statusModule(id, status, $btn, $row);
                        }, 
                        function no(event){
                            event.data.modal.hide();
                        }
                    );
                },
                "click update-module" : function(e) {
                    let $btn = $(this);
                    let $row = $btn.closest(".module-list").eq(0);
                    let id = $row.data("id");
                    console.log("click", id, $btn, $row);
                },
                "click open-settings-module" : function(e) {
                    let $btn = $(this);
                    let module = $btn.data("module");
                    Bsik.module.core.modules.openSettingsModal($btn, module);
                },
                "click save-module-settings" : function(e) {
                    let $btn = $(this);
                    Bsik.module.core.modules.saveModuleSettings($btn);
                },
            });

            //Attach user actions:
            Bsik.core.helpers.onActions("click change", "data-action", Bsik.userEvents);

            //Set filter field:
            Bsik.module.core.modules.filterList = new Bsik.core.OnType(
                "#filter-installed-list",
                function(value, event) {
                    let search = value.trim().toLowerCase();
                    if (search.length) {
                        $(".module-list").each(function(i,e){
                            if (
                                $(this).find(".module-name").text().toLowerCase().includes(search) || 
                                $(this).find(".module-header").text().toLowerCase().includes(search) ||
                                (
                                    search[0] === ':' &&
                                    (':' + $(this).find(".tag-module-status").text()).toLowerCase().includes(search)
                                )
                            ) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    } else {
                        $(".module-list").show();
                    }
                },
                13 //Enter keycode to execute
            );

        })(jQuery, this, document, window.Bsik);
    }

    /*********************************************************************/
    /** Endpoints View:  *************************************************/
    /*********************************************************************/

    if (Bsik.loaded.module.sub === "endpoints") {

        Bsik.module[Bsik.loaded.module.name].endpoints = {
            createEndpointsList : function(modules) {

                //
                for (const [module, endpoints] in Object.entries(modules)) {
                    console.log(module, endpoints);
                }
            },
            refreshApiList : function($btn) {
                $btn.prop("disabled", true);
                Bsik.core.apiRequest(null, "core.map_platform_api", { }, 
                    {
                        error: function(jqXhr, textStatus, errorMessage) {
                            let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                            Bsik.notify.error(`Refresh Api List error - ${error.join()}`, true);
                            console.log(jqXhr.responseJSON);
                        },
                        success: function(res) {
                            console.log(res);
                            if (res.data.length) {

                            }
                        },
                        complete: function() {
                            $btn.prop("disabled", false);
                        }
                    }
                );
            }
        };


        /************* Set user actions **********/
        $.extend(Bsik.userEvents, {
            "click refresh-endpoints-list" : function(e) {
                console.log("refresh list")
                Bsik.module.core.endpoints.refreshApiList($(this));
            }
        });
        //Attach user actions:
        Bsik.core.helpers.onActions("click change", "data-action", Bsik.userEvents);


        /************* toggle list display **********/
        $(".container-module").on("click", "li.endpoint-expand i", function() {
            let $info = $(this).closest(".endpoint-row").find(".endpoint-more-info").eq(0);
            if ($info.is(":visible")) {
                $info.slideUp(200);
                $(this).removeClass("expanded");
            } else {
                $info.slideDown(200);
                $(this).addClass("expanded");
            }
        });
        //Set filter field:
        Bsik.module.core.endpoints.filterList = new Bsik.core.OnType(
            "#filter-endpoints-list",
            function(value, event) {
                let search = value.trim().toLowerCase();
                if (search.length) {
                    $(".endpoint-row").each(function(i,e){
                        if (
                            $(this).find(".endpoint-name").text().toLowerCase().includes(search) || 
                            $(this).find(".endpoint-description").text().toLowerCase().includes(search) ||
                            (
                                search[0] === ':' &&
                                $(this).find(`.endpoint-${search.slice(1).trim()} .text-success`).length
                            )
                        ) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                } else {
                    $(".endpoint-row").show();
                }
            },
            13 //Enter keycode to execute
        );

    }

    /*********************************************************************/
    /** Settings View:  *************************************************/
    /*********************************************************************/

    if (Bsik.loaded.module.sub === "settings") {

        (function($, window, document, Bsik, undefined) {

            console.log(window);
            console.log(Bsik);


            Bsik.modals.settings = new bootstrap.Modal(
                document.getElementById('group-settings-modal'),
                Bsik.core.helpers.objAttr.getDataAttributes("#group-settings-modal")
            );
            Bsik.modals.settingsElement = $(Bsik.modals.settings._element);

            //Settings Loader and Parser:
            let settings = new Bsik.SettingsParser(Bsik.modals.settingsElement, true);

            Bsik.module[Bsik.loaded.module.name].settings = {

                filterList : null,
                /************ Initiate the base js object: ******************/
                openSettingsEditModal : function($btn, setting = "") {
                    //First get the settings:
                    $btn.prop("disabled", true);
                    settings.loadSettings(
                        "core.get_system_settings_groups", 
                        { settings : [setting], form : true }, 
                        function() {
                            console.log(this, arguments);
                            $btn.prop("disabled", false);
                            //Attach to modal:
                            let $body = this.container.find(`div.form-modal-container`).eq(0);
                            $body.html(this.formHtml);
                            //Set header:
                            let header   = this.container.find(".edit-settings-alert-info > span.alert-message").eq(0);
                            header.html(`You are editing <strong>${setting}</strong> setting - Please be carefull and only edit if you know what you are doing.`);
                            //Open modal:
                            Bsik.modals.settings.show();
                        }
                    );
                },
                saveCoreSettings : function($btn) {
                    let $form = Bsik.modals.settingsElement.find("#dyn-form-settings");
                    let revised = settings.getRevisedSettings($form);
                    let group = $form.data("group");
                    console.log(revised, group);
                    $btn.prop("disabled", true);
                    Bsik.core.apiRequest(null, "core.save_core_settings", { 
                            settings    : JSON.stringify(revised),
                            group       : group
                    }, {
                        error: function(jqXhr, textStatus, errorMessage) {
                            let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                            Bsik.notify.error(`Save revised setting error - ${error.join()}`, true);
                            console.log(jqXhr.responseJSON);
                        },
                        success: function(res) {
                            console.log(res);
                            Bsik.notify.bubble("info", `Saved revised setting successfully.`);
                            Bsik.modals.settings.hide();
                        },
                        complete : function() {
                            $btn.prop("disabled", false);
                        }
                    });
                },
            };

            /************* Set user actions **********/
            $.extend(Bsik.userEvents, {
                "click open-edit-settings-module" : function(e) {
                    let $btn = $(this);
                    let setting = $btn.data("setting");
                    Bsik.module.core.settings.openSettingsEditModal($btn, setting);
                },
                "click save-core-settings" : function(e) {
                    let $btn = $(this);
                    Bsik.module.core.settings.saveCoreSettings($btn);
                },
            });

            //Attach user actions:
            Bsik.core.helpers.onActions("click change", "data-action", Bsik.userEvents);

            /************* toggle list display **********/
            $(".container-module").on("click", "li.group-expand i", function() {
                let $info = $(this).closest(".settings-row").find(".group-settings").eq(0);
                if ($info.is(":visible")) {
                    $info.slideUp(200);
                    $(this).removeClass("expanded");
                } else {
                    $info.slideDown(200);
                    $(this).addClass("expanded");
                }
            });

            //Set filter field:
            Bsik.module.core.settings.filterList = new Bsik.core.OnType(
                "#filter-settings-list",
                function(value, event) {
                    let search = value.trim().toLowerCase();
                    if (search.length) {
                        $(".module-list").each(function(i,e){
                            if (
                                $(this).find(".module-name").text().toLowerCase().includes(search) || 
                                $(this).find(".module-header").text().toLowerCase().includes(search) ||
                                (
                                    search[0] === ':' &&
                                    (':' + $(this).find(".tag-module-status").text()).toLowerCase().includes(search)
                                )
                            ) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    } else {
                        $(".module-list").show();
                    }
                },
                13 //Enter keycode to execute
            );

        })(jQuery, this, document, window.Bsik);
    }

});













// document.addEventListener("DOMContentLoaded", function(event) {

//     /** Global module code: *************************************************/
//     // console.log(Bsik);
//     // Bsik.module[Bsik.loaded.module.name].publish_statuses = [
//     //     { "value" : 1, "text" : "draft"  },
//     //     { "value" : 2, "text" : "active" }
//     // ];
//     // Bsik.module[Bsik.loaded.module.name].shipping_templates = JSON.parse($(document).find("meta[name='shipping_templates']").attr("content") || '[]');

//     /** Module Settings: *************************************************/

//     if (Bsik.loaded.module.sub === "settings") {

//         (function($, window, document, Bsik, undefined) {

//             console.log(window);
//             console.log(Bsik);

//             Bsik.module[Bsik.loaded.module.name].publish = {

//                 /************ Initiate the base js object: ******************/
                

                

                
//             };

//             /************* Add dynamic table operation handler **********/
//             Bsik.tableOperateEvents = {
//                 'click .like': function(e, value, row, index) {
//                     console.log('You click like action, row: ' + JSON.stringify(row));
//                 },
//                 'click .delete': function(e, value, row, index) {
//                     console.log("delete row called!", this);
//                     this.$el.bootstrapTable('remove', {
//                         field: 'id',
//                         values: [row.id]
//                     })
//                 }
//             };
//             /************* Add dynamic table formmaters handler **********/
//             Bsik.dataTables.formatters.inventory = function(value, data_row, index, header) {
//                 return (value >= 10) ? "+10" : value;
//             };
//             /************* Set user actions **********/
//             $.extend(Bsik.userEvents, {
//                 "change listing-set-profit" : function(e) {
//                     let table = $("#publish-que-table").bootstrapTable('getData',{ useCurrentPage : true, includeHiddenRows : true })
//                     let profit = $(this).val();
//                     let row = $(this).closest("tr[data-index]").attr("data-index");
//                     Bsik.module.amazon.publish.save_profit(table[row].id, profit, "#publish-que-table");
//                 },
//             });
//             //Attach user actions:
//             Bsik.core.helpers.onActions("click change", "data-action", Bsik.userEvents);

//         })(jQuery, this, document, window.Bsik);
//     }
// });