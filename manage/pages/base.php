<?php

/******************************  includes  *****************************/

use \Siktec\Bsik\Std;
use \Siktec\Bsik\Trace;
use \Siktec\Bsik\Users\User;
use \Siktec\Bsik\Render\Pages\AdminPage;
use \Siktec\Bsik\CoreSettings;

/******************************  intellisense  *****************************/
/** @var AdminPage $APage */
/** @var User $User */

/******************************  Guard  *****************************/
/* TODO: improve that guard this is old stuff and with autoload will probably fail */
if (!isset($conf)) {
    include_once CoreSettings::$path["manage"]."/error/?p=main&code=10";
    die();
}

/******************************  Set Meta - required  *****************************/
//TODO: restore that from loaded module settings
// $APage->meta->set("lang",         $APage->settings["lang"]       ?? "en");
// $APage->meta->set("charset",      $APage->settings["charset"]    ?? "utf8");
// $APage->meta->set("title",        $APage->settings["title"]      ?? "");
// $APage->meta->set("author",       $APage->settings["author"]     ?? "");
// $APage->meta->set("description",  $APage->settings["desc"]       ?? "");
// $APage->meta->set("lang",         $APage->settings["lang"]       ?? "en");

Trace::add_trace("Required META set done.", __FILE__.__LINE__);

/******************************  Set Meta - optional  *****************************/
//TODO: what is this? restore that from loaded module settings?
// foreach($APage->settings["addmeta"] ?? [] as $opm) {
//     $APage->meta->add($opm);
// }
// Trace::add_trace("Optional META extend done.", __FILE__.__LINE__, "Total: ".count($APage->settings["addmeta"] ?? []));

/******************************  Store Important values  *****************************/
$APage->store("plat-logo", CoreSettings::$url["manage-lib"]."/img/logo.svg");

/******************************  Set Body tag  *****************************/
$APage->body_tag("style=''");

/******************************  Set Includes  *****************************/
//Auto load global libs + required module libs:
$loaded_libs = $APage->load_libs($global = true);

//Module content:
/******************************  Module content  *****************************/

//Empty on errors / Exception will be logged by the method:
$module_content = $APage->render_module(args : []);

Trace::add_trace("Rendered Module View", __FILE__.__LINE__, $APage::$module->current_view->name);
Trace::add_trace("Loaded View Privileges", __FILE__.__LINE__, $APage::$module->current_view->priv->all_defined());

//Load themes files:
/* SH: added - 2021-04-03 => Add theme control from DB */
if ($generic_lib = Std::$fs::file_exists("themes", ["base-dark", "theme.css"])) {
    $APage->include("head", "css", "link", ["name" => $generic_lib["url"]]);
    Trace::add_trace("Loaded theme plat stylesheet.", __FILE__.__LINE__);
}
//Load module generic files (js, css):
if ($generic_lib = Std::$fs::file_exists("modules", [AdminPage::$module->module_name, "module.css"])) {
    $APage->include("head", "css", "link", ["name" => $generic_lib["url"]]);
    Trace::add_trace("Loaded generic module stylesheet.", __FILE__.__LINE__);
}
if ($generic_lib = Std::$fs::file_exists("modules", [AdminPage::$module->module_name, "module.js"])) {
    $APage->include("head", "js", "link", ["name" => $generic_lib["url"]]); // Always keep in head - gives more control on needed predefined function declaration
    Trace::add_trace("Loaded generic module script.", __FILE__.__LINE__);
}
if ($generic_lib = Std::$fs::file_exists("modules", [AdminPage::$module->module_name, "logic.module.js"])) {
    $APage->include("head", "js", "link", ["name" => $generic_lib["url"]]); // Always keep in head - gives more control on needed predefined function declaration
    Trace::add_trace("Loaded generic module script.", __FILE__.__LINE__);
}


/******************************  Set Side Menu  *****************************/
$APage->load_menu();
Trace::add_trace("Parsed defined menu entries ", __FILE__.__LINE__);


/******************************  Render Page  *****************************/

//Build html / Head / Meta / includes:
$doc_head = $APage->render_block("header", "HeaderBlock", []);
Trace::add_trace("Loaded & Render Header structure", __FILE__.__LINE__);

//Close document + bottom includes:
$doc_end = $APage->render_block("footer", "FooterBlock", []);
Trace::add_trace("Loaded & Render End of document structure", __FILE__.__LINE__);

//Top bar:
$doc_admin_bar = $APage->render_block("topbar", "TopBarBlock", []);
Trace::add_trace("Loaded & Render Admin Top Bar", __FILE__.__LINE__);

//Side Menu:
$doc_side_menu = $APage->render_block("sidemenu", "SideMenuBlock", []);
Trace::add_trace("Loaded & Render side-menu structure", __FILE__.__LINE__);

//Module header:
$doc_module_header = $APage->render_template("module_header", [
    "module_name"   => AdminPage::$module->settings->get("title") ?? "",
    "module_which"  => AdminPage::$module->current_view->settings->get("title", "No title"),
    "module_desc"   => AdminPage::$module->current_view->settings->get("description", "No description")
]);
Trace::add_trace("Loaded & Render module content header", __FILE__.__LINE__);

//print the page:
print $APage->render_template("page", [
    "doc_head"          => $doc_head,
    "doc_admin_bar"     => $doc_admin_bar,
    "doc_side_menu"     => $doc_side_menu,
    "doc_module_header" => $doc_module_header,
    "module_content"    => $module_content,
    "brand"             => "BSIK by SIKTEC - V.1.0.1",
    "extra_html"        => $APage->html_container,
    "doc_end"           => $doc_end,
    "demo_notification" => false
]);
