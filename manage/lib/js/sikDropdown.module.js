'use strict';

/*TODO: make this a required extenssion from a git rep */
export default class SikDropdown {
    ele = null;
    dropdown = null;
    el  = {
       input  : null,
       toggle : null,
       list : null 
    };
    
    items   = {};
    count   = 0;
    _cbs    = [];
    defaults = {
        name        : "sik-input",
        value       : "",
        placeholder : "Select Item" 
    };
    options = {};
    constructor(identifier, opt = {}) {
        this.ele = document.querySelector(identifier);
        if (this.ele) {
            //Set options:
            this.setOptions(opt);
            
            //Create hidden input:
            this.el.input = document.createElement("input");
            this.el.input.setAttribute("type", "hidden");
            this.el.input.setAttribute("name", this.options.name);
            this.el.input.setAttribute("value", "");
            this.ele.prepend(this.el.input);

            //Select list:
            this.el.list = this.ele.querySelector(".dropdown-menu");
            this._fillItems();
            //Set toggler:
            this.el.toggle = this.ele.querySelector(".dropdown-toggle");
            this.dropdown  = new bootstrap.Dropdown(this.el.toggle);
            //Set initial value & placeholder:
            this.setValue(this.options.value);
            //Set core handlers:
            this._attachCoreHandlers();
            this.trigger("init");
        } else {
            console.warn("Cant create a Sik Dropdown - selector is invalid");
        }
    }
    setOptions(opt) {
        this._extend(this.options, this.defaults, opt);
    }
    addItem(value, label) {
        if (!this.items.hasOwnProperty(value)) {
            let itemContainer = document.createElement('li');
            itemContainer.innerHTML = `<span class="dropdown-item" data-value="${value}">${label}</span>`;
            this.el.list.appendChild(itemContainer);
            let item = itemContainer.querySelector(".dropdown-item");
            this.count++;
            this.items[value] = {
                el    : item,
                value : value,
                label : item.innerHTML.trim()
            };
            item.addEventListener("click", this.trigger.bind(this, "select"));
        }
    }
    removeItem(value) {
        if (this.items.hasOwnProperty(value)) {
            this.items[value].el.closest("li").remove();
            this.count--;
            delete this.items[value];
            if (this.value() === value) {
                this.setValue(null);
            }
        }
    }
    clear() {
        for (const value in this.items) {
            this.removeItem(value);
        }
    }
    setValue(value, close = true) {
        if (this.items.hasOwnProperty(value)) { 
            this.el.input.setAttribute("value", value);
            this.el.toggle.innerHTML = this.items[value].label;
        } else {
            this.el.input.setAttribute("value", "");
            if (this.options.placeholder) {
                this.el.toggle.innerHTML = this.options.placeholder;
            }
        }
        if (close) this.dropdown.hide();
    }
    value() {
        return this.el.input ? this.el.input.value : null;
    }
    text() {
        let value = this.value();
        if (this.items.hasOwnProperty(value)) {
            return this.items[value].label.trim();
        }
        return "";
    }
    open() {
        if (this.dropdown) {
            this.dropdown.show();
        }
    }
    close() {
        if (this.dropdown) {
            this.dropdown.hide();
        }
    }
    toggle() {
        if (this.dropdown) {
            this.dropdown.toggle();
        }
    }
    attachHandler(ev, cb) {
        this._cbs.push({
           ev : ev,
           fn : cb
        });
    }
    detachHandler(ev) {
        for( let i = 0; i < this._cbs.length; i++){ 
            if ( this._cbs[i].ev === ev) { 
                this._cbs.splice(i, 1); 
            }
        }
    }
    trigger(ev) {
        for (let cb of this._cbs) {
            let event = cb.ev.split(".");
            if (event.length > 1 && event[1] === ev) {
                let [, ...args] = arguments;
                cb.fn.call(this, ...args);
            }
        }
    }
    _fillItems() {
        if (this.el.list) {
            let items = this.el.list.querySelectorAll(".dropdown-item");
            let i = items.length;
            this.count = items.length;
            while (i--) {
                const value = items[i].getAttribute("data-value");
                this.items[value] = {
                    el    : items[i],
                    value : value,
                    label : items[i].innerHTML.trim()
                };
                items[i].addEventListener("click", this.trigger.bind(this, "select"));
            }
        }
    }
    _attachCoreHandlers() {
        this.attachHandler("core.select", function(item = null) {
            if (typeof item === 'object' && 'target' in item) {
                let selected = item.target.hasAttribute("data-value") 
                                ? item.target 
                                : item.target.closest("[data-value]");
                let value = selected ? selected.getAttribute("data-value") : null;
                this.setValue(value);
            }
        });
        this.attachHandler("core.open", function() {
        });
        this.attachHandler("core.close", function() {
        });
        this.attachHandler("core.init", function() {
        });
        //Bind to dropdown:
        this.el.toggle.addEventListener('show.bs.dropdown', this.trigger.bind(this, "open"));
        this.el.toggle.addEventListener('hide.bs.dropdown', this.trigger.bind(this, "close"));
    }
    _extend() {
        for(var i=1; i<arguments.length; i++)
            for(var key in arguments[i])
                if(arguments[i].hasOwnProperty(key))
                    arguments[0][key] = arguments[i][key];
        return arguments[0];
    }
}

//Make it global:
window.SikDropdown = SikDropdown;

//Named export:
export { SikDropdown }