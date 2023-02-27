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
/******************************  NOTIFY HANDLERS  *****************************/
let SikNotify = {
    openEle: null,
    messagesContainer: null,
    counterBadge: null,
    init: function() {

        //Elements:
        this.openEle = document.querySelector(".console-messages>span");
        this.messagesContainer = this.openEle.nextElementSibling;
        this.counterBadge = this.openEle.querySelector("em");

        //attach:
        this.openEle.addEventListener("click", () => this.openConsole("slide"));

        //Scan pre assigned messages:
        let messages = this.messagesContainer.querySelectorAll("li");
        messages.forEach((mes) => {
            mes.addEventListener('click', (e) => this.releaseRow(e));
        });
        this.counter();

        //Return instance:
        return this;
    },
    counter: function(add = 0) {
        let messages = this.messagesContainer.querySelectorAll("li");
        let count = add !== 0 ? messages.length + add : messages.length;
        this.counterBadge.textContent = count;
        this.counterBadge.style.display = count > 0 ? 'inline' : 'none';
    },
    closeIfEmpty: function() {
        let messages = this.messagesContainer.querySelectorAll("li");
        if (messages.length < 2 && $$.isVisible(this.messagesContainer)) {
            this.openConsole("slide", 300);
        }
    },
    openConsole: function(effect) { /* fade | slide */
        if ($$.isVisible(this.messagesContainer)) {
            if (effect == "slide") $$.slideUp(this.messagesContainer, 300);
            else this.messagesContainer.fadeOut("fast");
        } else {
            if (this.messagesContainer.querySelectorAll("li").length > 0) {
                if (effect == "slide") $$.slideDown(this.messagesContainer, 300);
                else this.messagesContainer.fadeIn("fast");
            }
        }
    },
    bubble(type, mes, timer = 5000) {
        let timestamp = new Date().toISOString();
        let title = "Information";
        switch (type) {
            case "warn":
                title = "Warning";
                break;
            case "error":
                title = "Error";
                break;
        }
        let tpl = `
            <div class="bsik-notify notify-${type}">
                <span class="notify-title">System ${title}</span>
                <span class="notify-message">${mes}</span>
                <span class="notify-time">${timestamp}</span>
                <span class="notify-release">X</span>
            </div>
        `;
        let bubble = $$.createElement(tpl);
        //let releaseCarret = bubble.querySelector(".notify-release");
        bubble.addEventListener("click", (e) => this.releaseBubble(e));
        document.body.append(bubble);
        $$.slideDown(bubble, 300);
        window.setTimeout(() => { bubble.click(); }, timer);
    },
    info : function(mes, bubble = false) {
        this.message("info", mes, bubble);
    },
    error : function(mes, bubble = false) {
        this.message("error", mes, bubble);
    },
    warn : function(mes, bubble = false) {
        this.message("warn", mes, bubble);
    },
    message: function(type, mes, bubble = false) {
        let timestamp = new Date().toISOString();
        let tpl_console = `<li><span class="notify-${type}">${mes} - <small>${timestamp}</small><i class="fas fa-times release-message"></i></span></li>`;
        let console_message = $$.createElement(tpl_console);
        let releaseCarret = console_message.querySelector(".release-message");
        releaseCarret.addEventListener("click", (e) => this.releaseRow(e));
        this.messagesContainer.prepend(console_message);
        this.counter();
        if (bubble) this.bubble(type, mes);
    },
    releaseRow: function(e) {
        let row = e.currentTarget.closest("li");
        this.counter(-1);
        this.closeIfEmpty();
        $$.slideUp(row, 300, function(el) {
            el.remove();
        });
    },
    releaseBubble: function(e) {
        let bubble = e.currentTarget;
        $$.slideUp(bubble, 300, function(el) {
            el.remove();
        });
    }
};

export { SikNotify };