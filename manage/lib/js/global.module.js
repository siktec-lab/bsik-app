/******************************  SIDE MENU HANDLERS  *****************************/
import * as $$ from './utils.module.js';
import * as SikCore from './core.module.js';
import * as SikLoaded from './loaded.module.js';
import { SikNotify } from './sikNotify.module.js';
import * as SikDataTables from './sikDataTables.module.js';
import * as SikMainMenu from './sikMainMenu.module.js';
import * as SikUser from './sikUserMenu.module.js';
import SettingsParser from './sikSettingsObject.module.js';


/*****************************  MAIN APP CLASS *********************************************/
//Set naming of al the parts:
window["Bsik"] = {};
let Bsik = window.Bsik;
window.Bsik["notify"]           = SikNotify.init();
window.Bsik["core"]             = SikCore;
window.Bsik["loaded"]           = SikLoaded;
window.Bsik["dataTables"]       = SikDataTables;
window.Bsik["menu"]             = null;
window.Bsik["userEvents"]       = {};
window.Bsik["modals"]           = { confirm :  null };
window.Bsik["module"]           = {};
window.Bsik["user"]             = {};
window.Bsik["scrollbars"]       = {};
window.Bsik.SettingsParser = SettingsParser;

(function(){

    //Initialize - scrollbars:
    if (typeof PerfectScrollbar === 'function') {

        let moduleContent = document.querySelector('#module-content');
        if (moduleContent) {
            window.Bsik.scrollbars["module_content"] = new PerfectScrollbar(moduleContent, {
                wheelSpeed: 1,
                wheelPropagation: true,
                minScrollbarLength: 20
            });
        }
        let adminMenuScrllbars = document.querySelector('ul.admin-menu');
        if (adminMenuScrllbars) {
            window.Bsik.scrollbars["admin_menu"] = new PerfectScrollbar(adminMenuScrllbars, {
                wheelSpeed: 1,
                wheelPropagation: true,
                minScrollbarLength: 20
            });
        }
        //update the scroll bars delayed just to make sure containers are visible:
        setTimeout(function(){ 
            for (let [key, scroll] of Object.entries(window.Bsik.scrollbars)) {
                scroll.update();
            }
        }, 1000);
        //Re-update if page size changes:
        window.addEventListener('resize', function(event) {
            for (let [key, scroll] of Object.entries(window.Bsik.scrollbars)) {
                scroll.update();
            }
        }, true);
    }

    //Initialize main menu:
    window.Bsik.menu = new SikMainMenu.MainMenu(".container-side-menu > .admin-menu", true);

    //Initialize user menu:
    //TODO: events here are just for testing:
    window.Bsik.user.menu = new SikUser.UserMainMenu("#main-user-menu", true);
    window.Bsik.user.menu.on("open", "open1", function(ev, el){ console.log("open1", this, ev, el); });
    window.Bsik.user.menu.on("close", "close1", function(ev, el){ console.log("close1", this, ev, el); });
    window.Bsik.user.menu.on("close", "close2", function(ev, el){ console.log("close2", this, ev, el); });
 
 })();

/*****************************  BASIC CORE EVENTS *********************************************/
//Set Bsik defaults:
//The meta will always be visible because scripts are added after them:
// window.Bsik.loaded.module.name = $("meta[name='module']").attr("content");
// window.Bsik.loaded.module.sub = $("meta[name='module-sub']").attr("content");
//Load the module data bridge:
window.Bsik.loaded.load();

//Register confirmation if its set:
if (document.getElementById('bsik-confirm-modal')) {
    Bsik.modals.confirm = new bootstrap.Modal(
        document.getElementById('bsik-confirm-modal'),
        Bsik.core.helpers.objAttr.getDataAttributes("#bsik-confirm-modal")
    );
    Bsik.modals.confirmElement = Bsik.modals.confirm._element;
}

//Modals stacking support:
document.addEventListener("DOMContentLoaded", function(event) {

    $('.bsik-modal').on('hidden.bs.modal', function(event) {
        $(this).removeClass( 'fv-modal-stack' );
        $('body').data( 'fv_open_modals', $('body').data( 'fv_open_modals' ) - 1 );
    });
    $('.bsik-modal').on('shown.bs.modal', function (event) {
        // keep track of the number of open modals
        if ( typeof( $('body').data( 'fv_open_modals' ) ) == 'undefined' ) {
            $('body').data( 'fv_open_modals', 0 );
        }
        // if the z-index of this modal has been set, ignore.
        if ($(this).hasClass('fv-modal-stack')) {
            return;
        }
        $(this).addClass('fv-modal-stack');
        $('body').data('fv_open_modals', $('body').data('fv_open_modals' ) + 1 );
        $(this).css('z-index', 1040 + (10 * $('body').data('fv_open_modals' )));
        $('.modal-backdrop').not('.fv-modal-stack').css('z-index', 1039 + (10 * $('body').data('fv_open_modals')));
        $('.modal-backdrop').not('fv-modal-stack').addClass('fv-modal-stack'); 
    });     

});

//Create module space:
window.Bsik["module"][window.Bsik.loaded.module.name] = {};

//Default handlers:
//TODO: move this to the script
document.addEventListener("DOMContentLoaded", function(event) {

    //Expand menu:
    $(".admin-menu").on("click", "> .menu-entry.has-submenu", function() {
        $(this).toggleClass("open-menu");
    });

    //Load module by menu click:
    $(".admin-menu .menu-entry").not(".has-submenu").on("click", function(e) {
        e.stopPropagation();
        let load = $(this).data("menuact");
        console.log("load module: ", load);
    });

});