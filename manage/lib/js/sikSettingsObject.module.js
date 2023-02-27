/******************************************************************************/
// Created by: SIKTEC.
// Release Version : 1.0.1
// Creation Date: 2022-02-03
// Copyright 2022, SIKTEC.
/******************************************************************************/
/*****************************      Changelog       ****************************
1.0.1:
    ->initial
*******************************************************************************/
export default class SettingsParser {

    container       = null;
    settings        = {};
    descriptions    = {};
    options         = {};
    formHtml        = "";
    
    constructor(
        selector, 
        initialize = false // whether to init or this will issue handlers:
    ) {
        this.container = $(selector);
        if (initialize) {
            this.attachHandlers();
        }
    }
    attachHandlers() {

        //Basic behavior of empty input:
        this.container.on("click", ".checkbox-empty-disable-input", function() {
            $(this).closest(".input-group").find(".form-control")
                    .prop("disabled", $(this).prop("checked"))
                    .val(null);
            $(this).closest(".input-group").find(".checkbox-remove-override-input").prop("checked", false);
        });
        
        //Basic behavior of remove input:
        this.container.on("click", ".checkbox-remove-override-input", function() {
            $(this).closest(".input-group").find(".form-control, .form-select")
                    .prop("disabled", $(this).prop("checked"));
            $(this).closest(".input-group").find(".checkbox-empty-disable-input").prop("checked", false);
        });

    }
    loadSettings(endpoint, request_data, success = function(){}, error = null) {
        //Set request:
        var self = this;
        Bsik.core.apiRequest(null, 
            endpoint, 
            request_data, 
            {
                error: function(jqXhr, textStatus, errorMessage) {
                    if (error === null) {
                        let error = jqXhr.responseJSON ? jqXhr.responseJSON.errors || [errorMessage] : [errorMessage];
                        Bsik.notify.error(`Load current settings error - ${error.join()}`, true);
                        console.log(jqXhr.responseJSON);
                    } else {
                        error.call(self, ...arguments);
                    }
                },
                success: function(res) {
                    console.log(res);
                    self.settings     = res.data.settings.values;
                    self.descriptions = res.data.settings.descriptions;
                    self.options      = res.data.settings.options;
                    self.formHtml     = res.data.form;
                    success.call(self);
                }
            }
        );
    }
    getRevisedSettings($form) {
        let entries = $form.find(".input-group");
        let changes = {};
        entries.each(function() {
            let $value  = $(this).find(".form-control, .form-select").eq(0);
            let setting = $value.attr("name");
            let empty   = $value.hasClass("form-control") 
                            ? $(this).find(".checkbox-empty-disable-input").eq(0).prop("checked") 
                            : false;
            let remove  = ($value.hasClass("form-select") || $value.hasClass("form-control") )
                            ? $(this).find(".checkbox-remove-override-input").eq(0).prop("checked") 
                            : false;
            let revised = $value.val() + "";
            let current = $value.attr("data-original");
            if (remove) {
                changes[setting] = "@remove@";
            } else if (empty) {
                changes[setting] = "";
            } else if (revised.length && revised !== current) {
                changes[setting] = revised;
            }
        });
        return changes;
    }
}
