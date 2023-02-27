
export function onActions(actions = "click", data = "action", events) {
    //Register events:
    $(document).on(actions, `[${data}]`, function(ev) {
        let operation = $(this).attr(data);
        let eventtype = ev.type;
        if (events.hasOwnProperty(`${eventtype} ${operation}`)) {
            events[`${eventtype} ${operation}`].call(this, ev);
        }
    });
}

export function getConfirmation(content = "Please Confirm", confirmed = function(){}, rejected = function(){}, withmodal = null) {
    let modal = withmodal ? withmodal : Bsik.modals.confirm;
    if (!modal) {
        console.log("confiramtion modal not available");
        return;
    }
    let $modal = $(modal._element);
    let $body    = $modal.find(`div.modal-body`).eq(0);
    let $confirm = $modal.find(`button.confirm-action-modal`).eq(0);
    let $reject = $modal.find(`button.reject-action-modal`).eq(0);
    if ($confirm.length && $reject.length) {
        $body.html(content);
        $confirm.off("click.confirmation").on("click.confirmation", { modal: modal }, confirmed);
        $reject.off("click.confirmation").on("click.confirmation", { modal: modal }, rejected);
        modal.show();
    } else {
        console.log("confiramtion modal buttons not available");
    }
}

export function isVisible(ele) {
    var style = window.getComputedStyle(ele);
    return style.width !== "0" &&
        style.height !== "0" &&
        style.opacity !== "0" &&
        style.display !== 'none' &&
        style.visibility !== 'hidden';
}

/* SLIDE UP */
export let slideUp = (target, duration = 500, callback = function() {}) => {

    target.style.transitionProperty = 'height, margin, padding';
    target.style.transitionDuration = duration + 'ms';
    target.style.boxSizing = 'border-box';
    target.style.height = target.offsetHeight + 'px';
    target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    window.setTimeout(() => {
        target.style.display = 'none';
        target.style.removeProperty('height');
        target.style.removeProperty('padding-top');
        target.style.removeProperty('padding-bottom');
        target.style.removeProperty('margin-top');
        target.style.removeProperty('margin-bottom');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
        callback(target);
    }, duration);
}

/* SLIDE DOWN */
export let slideDown = (target, duration = 500, callback = function() {}) => {

    target.style.removeProperty('display');
    let display = window.getComputedStyle(target).display;
    if (display === 'none') display = 'block';
    target.style.display = display;
    let height = target.offsetHeight;
    target.style.overflow = 'hidden';
    target.style.height = 0;
    target.style.paddingTop = 0;
    target.style.paddingBottom = 0;
    target.style.marginTop = 0;
    target.style.marginBottom = 0;
    target.offsetHeight;
    target.style.boxSizing = 'border-box';
    target.style.transitionProperty = "height, margin, padding";
    target.style.transitionDuration = duration + 'ms';
    target.style.height = height + 'px';
    target.style.removeProperty('padding-top');
    target.style.removeProperty('padding-bottom');
    target.style.removeProperty('margin-top');
    target.style.removeProperty('margin-bottom');
    window.setTimeout(() => {
        target.style.removeProperty('height');
        target.style.removeProperty('overflow');
        target.style.removeProperty('transition-duration');
        target.style.removeProperty('transition-property');
        callback(target);
    }, duration);
}

/* TOGGLE */
export var slideToggle = (target, duration = 500) => {
    if (window.getComputedStyle(target).display === 'none') {
        return slideDown(target, duration);
    } else {
        return slideUp(target, duration);
    }
}

/**
 * @param {String} HTML representing a single element
 * @return {Element}
 */
export function createElement(html) {
    var template = document.createElement('template');
    html = html.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = html;
    return template.content.firstChild;
}

/**
 * @param {String} HTML representing any number of sibling elements
 * @return {NodeList} 
 */
export function createElements(html) {
    var template = document.createElement('template');
    template.innerHTML = html;
    return template.content.childNodes;
}


function normalizeData(val) {
    if (val === 'true') return true;
    if (val === 'false') return false;
    if (val === Number(val).toString())
        return Number(val);
    if (val === '' || val === 'null')
        return null;
    return val;
}
function normalizeDataKey(key) {
    return key.replace(/[A-Z]/g, chr => `-${chr.toLowerCase()}`);
}
export const objAttr = {
    setDataAttribute(element, key, value, pref = 'bs') {
        element.setAttribute(`data-${pref}-${normalizeDataKey(key)}`, value);
    },
    removeDataAttribute(element, key, pref = 'bs') {
        element.removeAttribute(`data-${pref}-${normalizeDataKey(key)}`);
    },
    getDataAttributes(element, pref = 'bs') {
        element = $(element);
        if (!element.length) { return {}; }
        element = $(element)[0];
        const attributes = {};
        const regexp = new RegExp(`^${pref}`, 'i')
        Object.keys(element.dataset).filter(key => key.startsWith(pref)).forEach(key => {
        let pureKey = key.replace(regexp, '');
        pureKey = pureKey.charAt(0).toLowerCase() + pureKey.slice(1, pureKey.length);
        attributes[pureKey] = normalizeData(element.dataset[key]);
        });
        return attributes;
    },
    getDataAttribute(element, key, pref = 'bs') {
        return normalizeData(element.getAttribute(`data-${pref}-${normalizeDataKey(key)}`));
    },
    offset(element) {
        const rect = element.getBoundingClientRect();
        return {
        top: rect.top + document.body.scrollTop,
        left: rect.left + document.body.scrollLeft
        };
    },
    position(element) {
        return {
        top: element.offsetTop,
        left: element.offsetLeft
        };
    }
};

export function timeFromNow(date, nowDate = Date.now(), rft = new Intl.RelativeTimeFormat(undefined, { numeric: "auto" })) {
    const SECOND = 1000;
    const MINUTE = 60 * SECOND;
    const HOUR = 60 * MINUTE;
    const DAY = 24 * HOUR;
    const WEEK = 7 * DAY;
    const MONTH = 30 * DAY;
    const YEAR = 365 * DAY;
    const intervals = [
        { ge: YEAR, divisor: YEAR, unit: 'year' },
        { ge: MONTH, divisor: MONTH, unit: 'month' },
        { ge: WEEK, divisor: WEEK, unit: 'week' },
        { ge: DAY, divisor: DAY, unit: 'day' },
        { ge: HOUR, divisor: HOUR, unit: 'hour' },
        { ge: MINUTE, divisor: MINUTE, unit: 'minute' },
        { ge: 30 * SECOND, divisor: SECOND, unit: 'seconds' },
        { ge: 0, divisor: 1, text: 'just now' },
    ];
    const now = typeof nowDate === 'object' ? nowDate.getTime() : new Date(nowDate).getTime();
    const diff = now - (typeof date === 'object' ? date : new Date(date)).getTime();
    const diffAbs = Math.abs(diff);
    for (const interval of intervals) {
        if (diffAbs >= interval.ge) {
            const x = Math.round(Math.abs(diff) / interval.divisor);
            const isFuture = diff < 0;
            return interval.unit ? rft.format(isFuture ? x : -x, interval.unit) : interval.text;
        }
    }
}

export function trimChars(string, character) {
    const arr = Array.from(string);
    const first = arr.findIndex(char => char !== character);
    const last = arr.reverse().findIndex(char => char !== character);
    return (first === -1 && last === -1) ? '' : string.substring(first, string.length - last);
}