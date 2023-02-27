'use strict';

class ButtonState {
    
    defaults = {

        spinner_identifier : ".spinner-border",
        spinner_template   : `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true" style="display:none"></span>`,
        add_state_class    : true,
        state_class_prefix : "button-state", 
        default_state      : null
    };
    options = {};
    el = {
        btn         : null,
        spinner     : null
    };
    states = {
        "active" : function(current) {
            this._hideSpinner();
            this.el.btn.disabled = false;
        },
        "disable" : function(current) {
            this._hideSpinner();
            this.el.btn.disabled = true;
        },
        "loading" : function(current) {
            this._showSpinner();
            this.el.btn.disabled = true;
        }
    };
    currentState = null;
    constructor(_btn, opt = {}, states = {}) {

        //Set options:
        this.setOptions(opt);

        //Set options:
        this.setStates(states);

        this.el.btn = _btn;

        this.buildBtn();

        //Save to data:
        this.el.btn.ButtonState = this;
    }
    
    setOptions(opt) {
        this._extend(this.options, this.defaults, opt);
    }

    setStates(states) {
        this._extend(this.states, states);
    }

    buildBtn() {
        this.el.spinner = this.el.btn.querySelector(this.options.spinner_identifier);
        if (!this.el.spinner) {
            this.el.spinner = this._spinnerTemplate();
            this.el.btn.prepend(this.el.spinner);
        }
        //load initial state:
        this.state(
            this._checkState()
        );
    }
    state(state = "") {
        let set = this.states.hasOwnProperty(state) 
                    ? state
                    : this.options.default_state;
        if (set !== null) {
            this.states[set].call(this, this.currentState);
            this.currentState = set;
            this._removeAllstateClasses();
            this.el.btn.classList.add(`${this.options.state_class_prefix}-${set}`);
        }
    }
    _spinnerTemplate() {
        var template = document.createElement('template');
        template.innerHTML = this.options.spinner_template.trim();
        return template.content.firstChild;
    }
    _extend() {
        for(var i=1; i<arguments.length; i++)
            for(var key in arguments[i])
                if(arguments[i].hasOwnProperty(key))
                    arguments[0][key] = arguments[i][key];
        return arguments[0];
    }
    _showSpinner() {
        if (this.el.spinner.style.display === "none") {
            this.el.spinner.style.display = null;
        }
    }
    _hideSpinner() {
        this.el.spinner.style.display = "none";
    }
    _text(txt) {
        for (let node of this.el.btn.childNodes) {
            if (node.nodeType == 3 && node.textContent.trim() !== "") {
                node.nodeValue = txt;
                return;
            }
        }
        let textNode = document.createTextNode(txt);
        this.el.btn.appendChild(textNode);
    }
    _removeAllstateClasses() {
        this.el.btn.classList.remove(...Object.keys(this.states).map( c => `${this.options.state_class_prefix}-${c}`));
    }
    _checkState() {
        for (const state of Object.keys(this.states)) {
            if (this.el.btn.classList.contains(`${this.options.state_class_prefix}-${state}`)) {
                return state;
            }
        }
        return null;
    }
}


export { ButtonState }