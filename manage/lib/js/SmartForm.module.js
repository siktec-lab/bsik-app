'use strict';

class SmartForm {
    
    defaults = {
        class_invalid            : "is-invalid",
        class_valid              : "is-valid",
        class_general_feedback   : "general-feedback",
        exclude_class            : "smart-form-exclude",
        feedback_template        : `<div class="general-feedback"><span class="material-icons show-invalid">error_outline</span><span class="material-icons show-valid">check_circle_outline</span><span class="message">testing smart forms</span></div>`,
        validate                 : [],
        map                      : {},
        exclude                  : []
    };

    options = {};

    el = {
        form         : null,
        inputs       : null,
        feedback     : null
    };

    constructor(_form, opt = {}) {

        //Set options:
        this.setOptions(opt);

        //build:
        this.el.form = this.buildForm(_form);
        if (this.el.form === null) {
            console.error("Cant build SmartForm - FORM element is required", _form);
        } else {
            //init reset:
            this.resetValidation();
            //Save to data:
            this.el.form.SmartForm = this;
        }
    }
    
    setOptions(opt) {
        this._extend(this.options, this.defaults, opt);
    }

    buildForm(_form) {
        //Form:
        if (_form && typeof _form === 'object' && _form.constructor.name === 'HTMLFormElement') {
            // Scan for inputs:
            this.el.inputs   = _form.elements;
            this.el.feedback = _form.querySelector("." + this.options.class_general_feedback);
            if (!this.el.feedback) {
                this.el.feedback = this._feedbackTemplate();
                _form.prepend(this.el.feedback);
            }
            return _form;
        }
        return null;
    }

    getFieldEle(identifier, mapped = true) {
        identifier = mapped ? this._getKeyByValue(this.options.map, identifier) : identifier;
        let byName = this.el.form.querySelector(`[name='${identifier}']`);
        let byId   = this.el.form.querySelector(`[id='${identifier}']`);
        return byName === null ? byId : byName;
    }
    setFieldState(identifier, state = "", set = true, mapped = true) {
        const field = this.getFieldEle(identifier, mapped);
        state = state.trim().toLowerCase();
        if (field) {
            switch(state) {
                case "disabled": {
                    field.disabled = set;
                } break;
                case "checked": {
                    field.checked = set;
                } break;
                case "focus": {
                    if (set) {
                        field.focus({preventScroll : false});
                    } else {
                        field.blur();
                    }
                } break;
                case "valid": {
                    if (set === false) {
                        field.classList.remove(this.options.class_valid);
                        field.classList.add(this.options.class_invalid);
                    } else if (set === true) {
                        field.classList.remove(this.options.class_invalid);
                        field.classList.add(this.options.class_valid);
                    } else {
                        field.classList.remove(this.options.class_invalid, this.options.class_valid);
                    }
                } break;
                default:
                    return false;
            }
            return true;
        }
        return null;
    }
    getFieldState(identifier, state = null, mapped = true) { 
        //State => "disabled", "checked", "focused", "valid"; 
        const field = this.getFieldEle(identifier, mapped);
        let returnState = state.trim().toLowerCase();
        if (field) {
            switch(returnState) {
                case "disabled": {
                    return field.disabled;
                }
                case "checked": {
                    return field.checked;
                }
                case "focus": {
                    return field.hasFocus();
                }
                case "valid": {
                    let valid = field.classList.contains(this.options.class_valid);
                    let invalid = field.classList.contains(this.options.class_invalid);
                    if (valid) return true;
                    if (invalid) return false;
                    return null;
                }
                default:
                    return null;
            }
        }
        return null;
    }

    resetForm(removeValidation = true) {
        if (this.el.form && this.el.form.constructor.name === 'HTMLFormElement') {
            this.el.form.reset();
            if (removeValidation) {
                this.resetValidation();
            }
        }
    }

    resetValidation() {
        let invalid = this.el.form.querySelectorAll("." + this.options.class_invalid);
        let valid = this.el.form.querySelectorAll("." + this.options.class_valid);
        //Reset fields:
        for (const field of invalid) {
            field.classList.remove(this.options.class_invalid);
        }
        for (const field of valid) {
            field.classList.remove(this.options.class_valid);
        }
    }

    validate(rules = null, setStates = true) {
        
        let tests = {};
        
        //Definition of the form:
        rules = rules ?? this.options.validate;

        //get the data mapped and excluded:
        let data = this.getData();
        
        //Validate:
        for (const rule of rules) {
            //Get field value:
            if (!data.hasOwnProperty(rule.field)) {
                console.warn(`field not found in form - got '${rule.field}'`);
                tests[rule.field] = false;
            } else if (typeof rule.rule === 'string') {
                if (this.Validation.hasOwnProperty(rule.rule)) {
                    let args = rule.args || [];
                    if (this.Validation[rule.rule].call(this, data[rule.field], args) !== true) {
                        tests[rule.field] = false;
                    }
                } else {
                    console.warn(`required validation rule is not defined - got '${rule.rule}'`);
                    tests[rule.field] = false;
                }
            } else if (typeof rule.rule === 'function') {
                if (rule.rule.call(this, data[rule.field], ...rule.args) !== true) {
                    tests[rule.field] = false;
                }
            }
        }
        
        //If expose validation errors:
        if (setStates) this.resetValidation();

        //return:
        let is_valid = true;
        for (const [field, result] of Object.entries(tests)) {
            if (!result) {
                is_valid = false;
                //set state
                if (setStates) {
                    this.setFieldState(field, "valid", false);
                }
            }
        }
        //Answer:
        return is_valid;
    }

    getData(map = null, exclude = null) {
        map = map ?? this.options.map;
        exclude = exclude ?? this.options.exclude;
        let data = this.FormExtend.serialize(
            this.el.form, 
            map,        //Map 
            exclude,    //Exclude
            this.options.exclude_class
        );
        return data;
    }

    feedback(valid, text = "") {
        const message = this.el.feedback.querySelector(".message");
        if (text && message) {
            message.textContent = text;
        }
        this.el.feedback.classList.remove(
            this.options.class_invalid,
            this.options.class_valid
        );
        this.el.feedback.classList.add(
            valid ? this.options.class_valid : this.options.class_invalid
        );
    }
    _feedbackTemplate() {
        var template = document.createElement('template');
        template.innerHTML = this.options.feedback_template.trim();
        return template.content.firstChild;
    }

    Validation = {
        email : email => {
            const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(email).toLowerCase());
        },
        password : (pass, errors = [], len = 6, upper = 1, lower = 1, digits = 1, special = 1) => {
            //Reset:
            errors = [];
            //Validate length:
            if (pass.length < len) {
                errors.push("length");
            }
            //Validate special:
            let pattern_special = new RegExp('[!@#$%^&*?_~.,()\\-\\+=]{'+ special +',}', 'gi');
            if (!pattern_special.test(pass)) {
                errors.push("special");
            }
            //Validate lower:
            let pattern_lower = new RegExp('[a-z]{'+ lower +',}');
            if (!pattern_lower.test(pass)) {
                errors.push("lower");
            }
            //Validate upper:
            let pattern_upper = new RegExp('[A-Z]{'+ upper +',}');
            if (!pattern_upper.test(pass)) {
                errors.push("upper");
            }
            //Validate digits:
            let pattern_digits = new RegExp('[0-9]{'+ digits +',}');
            if (!pattern_digits.test(pass)) {
                errors.push("digits");
            }
            return errors.length === 0;
        },
        minLength : (value, min = 1) => {
           return value.length >= min;
        },
        maxLength : (value, max = 1) => {
            return value.length <= max;
        },
        rangeLength : (value, min = 0, max = 10) => {
            return value.length >= min && value.length <= max;
        },
        min : (value, min = 0) => {
            return parseFloat(value) >= min;
        },
        max : (value, max = 0) => {
            return parseFloat(value) <= max;
        },
        range : (value, min = 0, max = 0) => {
            const v = parseFloat(value);
            return v <= max && v >= min;
        },
        checked : (value) => {
            return value !== null && value !== false && value !== "off";
        },
        not : (value, against = "") => {
            return value.trim() !== against.trim();
        },
        is : (value, against = "") => {
            return value.trim() === against.trim();
        },
        oneOf : (value, stack = []) => {
            return stack.includes(value.trim());
        },
        notOf : (value, stack = []) => {
            return !stack.includes(value.trim());
        },
        numeric : (value) => {isNum
            if (typeof value != "string") return false; // we only process strings!  
            return !isNaN(value) && !isNaN(parseFloat(value));
        }
    };

    FormExtend = {
        serialize: function(form, map = {}, exclude = [], exclude_class = false) {
            let data = {}
            for (var i = 0; i < form.elements.length; i++) {
                
                //Avoid excluded:
                if (form.elements[i].classList.contains(exclude_class))
                    continue;

                const [type, name, value] = this._parseField(form.elements[i]);

                //Avoid name excluded:
                if (exclude.includes(name))
                    continue;

                //Get map name:
                let mapped = name && map.hasOwnProperty(name) ? map[name] : name;

                //Save results:
                if (name && type === 'radio') {    
                    if (value === null && !data.hasOwnProperty(mapped)) {
                        data[mapped] = value;
                    } else if (value !== null) {
                        data[mapped] = value;
                    }
                } else if (name) {
                    data[mapped] = value;
                }
            }
          return data;
        }, 
        _parseField: function(element) {
            const type = element.type.toLowerCase();
            let name = element.name ? element.name : element.id;
            const nodeName = element.nodeName.toLowerCase();
            let value = null;
            switch (nodeName) {
                case "textarea":
                case "input":
                    if (type === "radio" || type === "checkbox") {
                        value = element.checked ? element.value : null;
                    } else {
                        value = element.value;
                    }
                    break;
                case "select":
                    if (type === "select-multiple") {
                        value = [];
                        for (var i = 0; i < element.options.length; i++) {
                            if (element.options[i].selected) {
                                value.push(element.options[i].value);
                            }
                        }
                    } else {
                        value = element.value;
                    }
                    break;
                default:
                    name = false;
                    break;
            }
            return [type, name, value];
        }        
    };
    
    _extend() {
        for(var i=1; i<arguments.length; i++)
            for(var key in arguments[i])
                if(arguments[i].hasOwnProperty(key))
                    arguments[0][key] = arguments[i][key];
        return arguments[0];
    }
    _getKeyByValue(object, value) {
        return Object.keys(object).find(key => object[key] === value) || value;
    }
}


export { SmartForm }