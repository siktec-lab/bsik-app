/******************************  SIDE MENU HANDLERS  *****************************/
import * as SikCore from './core.module.js';

/*****************************  MAIN APP CLASS *********************************************/
window["Bsik"] = {};
let Bsik = window.Bsik;
window.Bsik["core"]             = SikCore;
window.Bsik["userEvents"]       = {};
window.Bsik["modals"]           = { confirm :  null };
window.Bsik["scrollbars"]       = {};