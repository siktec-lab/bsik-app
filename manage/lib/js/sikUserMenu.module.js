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

export class UserMainMenu {
    el      = {};
    loaded  = false;
    events  = {
        open    : {},
        close   : {},
    };
    constructor(
        selector, 
        initialize = false // whether to init or not we need the dom to init.... 
    ) {
        this.selector = selector;

        //Register events:
        this.event("navigate", "main", function(ev, ele, param) {
            window.Bsik.core.redirectPage(param, false, 0, true);
        });
        this.event("avoid", "main", function(ev, ele, param) {
            console.log(ev, ele, param);
        });
        this.event("tagged", "main", function(ev, ele, tag) {
            console.log(ev, ele, tag)
        });
        if (initialize) this.init();
    }
    init() {
        this.el.container   = document.querySelector(this.selector);
        this.el.toggler     = document.querySelector(this.selector + " #toggle");
        this.el.sideData    = document.querySelector(this.selector + " .user-data");
        this.el.avatar      = document.querySelector(this.selector + " .user-avatar > img");
        this.el.menu        = document.querySelector(this.selector + " .menu-list");
        this.el.menuHeader  = document.querySelector(this.selector + " .menu-header");
        this.el.menuItems   = document.querySelectorAll(this.selector + " .menu-item");
        this.loaded = (this.el.container && this.el.toggler && this.el.menu) ? true : false;
        if (this.loaded) {
            //Set default toggler:
            this.el.toggler.addEventListener("change", this._userMenuToggle.bind(this));
            //attach menu item click handler:
            this.el.menu.addEventListener("click", this._userItemClick.bind(this));
        }
        return this.loaded;
    }
    _userItemClick(ev) {
        for (const ele of ev.composedPath()) {
            if (ele === this.el.menu) break;
            if (
                ele.classList.contains("menu-item") &&
                ele.hasAttribute("data-menu-action")
            ) {
                let event = ele.getAttribute("data-menu-action");
                let param = ele.getAttribute("data-param");
                // console.log(event, param);
                this.fire(event, false, ele, param);
                break;
            }
        }
    }
    _userMenuToggle(ev) {
        // console.log(ev);
        this.fire(this.el.toggler.checked ? "open" : "close", false, ev.target || null);
    }
    open() {
        if (!this.loaded) return;
        this.el.toggler.checked = true;
        this.fire("open");
    }

    close() {
        if (!this.loaded) return;
        this.el.toggler.checked = false;
        this.fire("close");
    }

    toggle() {
        if (!this.loaded) return;
        this.el.toggler.checked = !this.el.toggler.checked;
        this.fire(this.el.toggler.checked ? "open" : "close");
    }


    fire(event, scope = false, ele = null, param = "") {
        if (this.events.hasOwnProperty(event)) {
            if (scope && this.events[event].hasOwnProperty(scope)) {
                this.events[event][scope]({ e : event, scope : scope }, ele, param);
            } else {
                for (const [scope, cb] of Object.entries(this.events[event])) {
                    cb({ e : event, scope : scope }, ele, param);
                }
            }
        }
    }
    on(event, scope, cb) {
        if (this.events.hasOwnProperty(event)) {
            this.events[event][scope] = cb.bind(this);
        }
    }

    off(event, scope) {
        if (this.events.hasOwnProperty(event) && events[event].hasOwnProperty(scope)) {
            delete this.events[event][scope];
        }
    }

    event(event, scope, cb) {
        if (!this.events.hasOwnProperty(event)) {
            this.events[event] = {};
        } 
        this.events[event][scope] = cb.bind(this);
    }
    getMenuItem(menuId) {
        return typeof menuId === 'string' ? this.el.menu.querySelector(`[data-menu-id='${menuId}']`) : menuId;
    }
    getTag(menuId) {
        let menuItem = this.getMenuItem(menuId);
        return menuItem ? menuItem.querySelector(`.badge`) : null;
    }
    createTag(menuId, set = 1, bgColor="info", classes = [], countMax = 99) {
        let menuItem = this.getMenuItem(menuId);
        console.log(menuItem);
        if (menuItem) {
            let tag = this.getTag(menuId) ??  menuItem.append(this._createTagElement(set, bgColor, classes, countMax));
            this.setTag(menuItem, set);
            return tag;
        }
        return null;
    }
    setTag(menuId, set = 0) {
        let tag = this.getTag(menuId);
        console.log(tag, set);
        if (tag) {
            if (set <= 0) {
                tag.remove();
            } else {
                let max = tag.getAttribute("data-max");
                tag.textContent = max && parseInt(max) < set ? "+" + max : "" + set;
            }
        } else {
            this.createTag(menuId, set);
        }
    }
    getTagValue(menuId) {
        let tag = this.getTag(menuId);
        if (tag) {
            return parseInt(tag.textContent);
        }
        return null;
    }
    decreaseTag(menuId, by = 0) {
        by = by < 0 ? by * -1 : by;
        this.setTag(menuId, (this.getTagValue(menuId) ?? 0) - by);
    }
    increaseTag(menuId, by = 0) {
        by = by < 0 ? by * -1 : by;
        this.setTag(menuId, (this.getTagValue(menuId) ?? 0) + by);
    }

    _createTagElement(set, bgColor="info", classes = [], countMax = 99) {
        var template = document.createElement('template');
        template.innerHTML = `<span class="badge rounded-pill bg-${bgColor} ${classes.join(' ')}" data-max="${countMax}">
            ${set}
            </span>`;
        return template.content.childNodes.item(0);
    }

}
