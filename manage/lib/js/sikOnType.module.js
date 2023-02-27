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

export class OnType {

    lastKey = '';
    constructor(input, cb = function(value, event){}, onKey = -1) {
        this.field = document.querySelector(input);
        this.callback = cb;
        this.trigger = onKey;
        this.attach();
    }
    attach() {
        this.field.addEventListener('keyup', this.fire.bind(this));
    }
    detach() {
        this.field.removeEventListener("keyup", this.fire.bind(this));
    }
    fire(ev) {
        this.lastKey = ev.keyCode;
        if (this.trigger === -1) {
            this.callback.call(this, this.getValue(), ev);
        } else if (this.trigger === ev.keyCode) {
            this.callback.call(this, this.getValue(), ev);
        }
    }
    getValue() {
        return this.field.value;
    }
}

