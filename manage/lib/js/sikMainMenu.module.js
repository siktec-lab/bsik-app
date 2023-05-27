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

//TODO: more implementation is needed here....
export class MainMenu {
    el      = {};
    menu    = {};
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
        // this.event("navigate", "main", function(ev, ele, param) {
        //     window.Bsik.core.redirectPage(param, false, 0, true);
        // });
        // this.event("avoid", "main", function(ev, ele, param) {
        //     console.log(ev, ele, param);
        // });
        // this.event("tagged", "main", function(ev, ele, tag) {
        //     console.log(ev, ele, tag)
        // });
        if (initialize) this.init();
        // console.log(this.loaded, this.el);
        // console.log(this.menu);
    }
    init() {
        this.el.container   = document.querySelector(this.selector);
        this._parseMenuItems();
        this.loaded = (this.el.container && Object.keys(this.menu).length) ? true : false;
        if (this.loaded) {
            //Set default toggler:
            // this.el.toggler.addEventListener("change", this._userMenuToggle.bind(this));
            // //attach menu item click handler:
            // this.el.menu.addEventListener("click", this._userItemClick.bind(this));
        }
        return this.loaded;
    }
    _parseMenuItems() {
        //Top level:
        let topLevel = this.el.container.querySelectorAll(":scope > li.menu-entry");
        let save = this;
        topLevel.forEach(function(item) {
            let act = item.dataset.menuact;
            save.menu[item.dataset.menuact] = {
                el      : item,
                level   : 1,
                hasSub : item.classList.contains('has-submenu'),
                sub     : {},
                loaded  :  item.classList.contains('is-loaded')
            };
            if (save.menu[item.dataset.menuact].hasSub) {
                let secondLevel = item.querySelectorAll("li.menu-entry");
                secondLevel.forEach(function(sub) {
                    save.menu[item.dataset.menuact].sub[sub.dataset.menuact] = {
                        el      : sub,
                        level   : 2,
                        hasSub  : false,
                        sub     : {},
                        loaded  :  sub.classList.contains('is-loaded')
                    };
                });
            }
        });
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
    getMenu(query) {
        let parts = query.split('@');
        let menuactLevelOne = parts[0];
        if (this.menu.hasOwnProperty(menuactLevelOne)) {
            if (parts.length > 1) {
                if (this.menu[menuactLevelOne].sub.hasOwnProperty(parts[1])) {
                    return this.menu[menuactLevelOne].sub[parts[1]];
                }
            } else {
                return this.menu[menuactLevelOne]
            }
        }
        return null;
    }
    add(menuact, addIn = ".", link = "", title = "", icon = "", text = "", hasSub = false) {
        let insertIn = addIn !== "." ? this.getMenu(addIn) : this.menu;
        if (insertIn !== null) {
            //If its nested? 
            if (addIn !== "." && !insertIn.hasSub) {
                insertIn.hasSub = true;
                insertIn.el.querySelector("a").setAttribute("href", "javascript:void(0)");
                insertIn.el.classList.add('has-submenu');
                let list = document.createElement("ul");
                list.classList.add("entry-sub-menu");
                insertIn.el.append(list);
                insertIn = insertIn.el.querySelector(".entry-sub-menu");
            } else if (addIn !== ".") {
                insertIn = insertIn.el.querySelector(".entry-sub-menu");
            } else {
                insertIn = this.el.container;
            }
            //Create element:
            let toAdd = this._createMenuItem(menuact, link, title, icon, text, hasSub);
            //Append:
            insertIn.append(toAdd);
            //Reparse:
            this._parseMenuItems();
            // console.log(this.menu);
            return true;
        }
        return false;

    }
    
    remove(menuact, from = ".") {
        if (from === "." && this.menu.hasOwnProperty(menuact)) {
            this.menu[menuact].el.remove();
        } else {
            let elFrom = this.getMenu(from);
            if (elFrom !== null && elFrom.sub.hasOwnProperty(menuact)) {
                elFrom.sub[menuact].el.remove();
            }
        }
        //Reparse:
        this._parseMenuItems();
    }

    show() {

    }

    hide() {

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


    _createMenuItem(menuact, link = "", title = "", icon = "", text = "", hasSub = false) {
        var template = document.createElement('template');
        let sub  = hasSub ? "<ul class='entry-sub-menu'></ul>" : "";
        let href = hasSub ? "javascript:void(0)" : link;
        let icon_html = icon[0] === "<" ? icon : `<span class='material-icons-outlined xlg space-4'>${icon}</span>`;
        template.innerHTML = `<li class="menu-entry ${hasSub ? "has-submenu" : ""}" data-menuact="${menuact}" title="${title}">
                <a href="${href}">
                    <span>${icon_html} ${text}</span>
                </a>
                ${sub}
            </li>`;
        return template.content.childNodes.item(0);
    }


}
