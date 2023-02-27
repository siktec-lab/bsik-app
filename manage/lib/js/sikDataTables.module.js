/******************************************************************************/
// Created by: SIKTEC.
// Release Version : 1.0.0
// Creation Date: 2021-03-17
// Copyright 2021, SIKTEC.
/******************************************************************************/
/*****************************      Changelog       ****************************
1.0.0:
    -> initial
*******************************************************************************/
import * as $$ from './utils.module.js';
import * as SikCore from './core.module.js';

export var formatters = {};

export function get(url, req, params) {
    //console.log(params);
    return SikCore.apiRequest(
        url,
        req,
        params.data, {
            error: function(xhr, status, message) {
                //console.log(arguments);
                if (typeof params.error == 'function')
                    params.error(xhr, status, message);
            },
            success: function(res) {
                //console.log(res);
                if (typeof params.success == 'function')
                    params.success(res.data);
            }
        }
    );
}