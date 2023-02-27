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

export class MaxType {
    counter = 0;
    constructor(
        input, 
        showCounter,
        counterAddClass = "",
        onMax = function(value, event){},
        onType = function(value, event){}
    ) {
        this.field  = typeof input === "string" ? document.querySelector(input) : input;
        this.max    = parseInt(this.field.getAttribute("max-type") ?? 100);
        this.onmax  = onMax;
        this.ontype = onType;
        this.showCounter = showCounter;
        this.counterAddClass = counterAddClass;
        this.listener = this.fire.bind(this);
        this.attach();
        this.field.maxType = this;
        this.addCounter();
        this.fire(null, false);
        
    }
    addCounter() {
        // New Element
        if (!this.showCounter) return;
        this.counterEl = document.createElement("div");
        this.counterEl.classList.add("max-type-counter");
        if (typeof this.counterAddClass === "string" && this.counterAddClass.length)
            this.counterEl.classList.add(this.counterAddClass);
        this.field.after(this.counterEl);
    }
    updateCounter(maxed = false) {
        if (!this.showCounter) return;
        this.counterEl.innerText = this.counter + "/" + this.max;
        if (maxed && !this.counterEl.classList.contains("is-maxed")) {
            this.counterEl.classList.add("is-maxed");
        } else if (!maxed && this.counterEl.classList.contains("is-maxed")) {
            this.counterEl.classList.remove("is-maxed");
        }
    }
    attach() {
        this.field.addEventListener('keyup', this.listener);
    }
    detach = function() {
        this.field.removeEventListener("keyup", this.listener);
        if (this.showCounter) {
            this.counterEl.remove();
        }
        delete this.field.maxType;
    }
    fire(ev, callbacks = true) {
        this.counter = this.field.value.length;
        if (this.field.value.length > this.max) {
            this.counter = this.max;
            this.field.value = this.field.value.slice(0, this.max);
            if (callbacks) 
                this.onmax.call(this, this.field.value, ev);
        } else {
            this.counter = this.field.value.length;
            if (callbacks) 
                this.ontype.call(this, this.field.value, ev);
        }
        this.updateCounter(this.counter === this.max);
    }
}

export function bindAllMaxType(selector, config = {}) {
    let defaults = {
        showCounter      : true,
        counterAddClass  : "",
        onMax            : function(value, event){},
        onType           : function(value, event){},
    };
    if (typeof config !== 'object') config = {};
    let settings = {...defaults, ...config };
    let inputs = document.querySelectorAll(selector);
    for (let i = 0; i < inputs.length; i++) {
        new MaxType(
            inputs[i], 
            settings.showCounter, 
            settings.counterAddClass, 
            settings.onMax,
            settings.onType
        );
    }
}

export function unbindAllMaxType(selector) {
    let inputs = document.querySelectorAll(selector);
    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].hasOwnProperty("maxType")) {
            inputs[i].maxType.detach();
        }
    }
}