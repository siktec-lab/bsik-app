<?php

use \Siktec\Bsik\Builder\Components;
use \Siktec\Bsik\Module\Modules;
use \Siktec\Bsik\Module\Module;
use \Siktec\Bsik\Module\ModuleView;
use \Siktec\Bsik\Privileges as Priv;
use \Siktec\Bsik\Objects\SettingsObject;

/****************************************************************************/
/*******************  local Includes    *************************************/
/****************************************************************************/
//require_once "includes".DS."components.php";


/****************************************************************************/
/*******************  required privileges for module / views    *************/
/****************************************************************************/
$module_policy = new Priv\RequiredPrivileges();
$module_policy->define(
    new Priv\Default\PrivAccess(manage : true)
);

/****************************************************************************/
/*******************  Register Module  **************************************/
/****************************************************************************/

Modules::register_module_once(new Module(
    name          : "dashboard",
    privileges    : $module_policy,
    views         : ["dashboard"],
    default_view  : "dashboard"
)); 

/****************************************************************************/
/*******************  View - dashboard  *************************************/
/****************************************************************************/

Modules::module("dashboard")->register_view(
    view : new ModuleView(
        name        : "dashboard",
        privileges  : null,
        settings    : new SettingsObject([
            "title"         => "",
            "description"  => "Welcome to your dashboard - Take a close look, add and customize your dashboard widgets",
        ])
    ),
    render      : function() {

        /** @var Module $this */
        
        $count_listings     = 25;  //$APage::$db->getValue("listings", "count(*)");
        $count_scraped      = 152; //$APage::$db->where("status", 2)->getValue("listings", "count(*)");
        $count_published    = 100; //$APage::$db->where("published", 2)->getValue("listings", "count(*)");
        $count_categories   = 5;   //$APage::$db->where("status", 2)->groupBy("amazon_category")->getValue("listings", "count(*)");
    
        $stat_imported      = Components::summary_card("imported", $count_listings, "fas fa-chart-bar", "yellow");
        $stat_scraped       = Components::summary_card("scraped",    $count_scraped, "fas fa-chart-bar", "info");
        $stat_published     = Components::summary_card("published", $count_published, "fas fa-chart-bar", "warning");
        $stat_categories    = Components::summary_card("categories", $count_categories, "fas fa-chart-bar", "danger");
    
        //Controls:
        $stats = <<<HTML
            <div class="container pt-3 pb-3">
                <h2>Scraper Data-Base Summary:</h2>
                <div class="row">
                    <div class="col-xl-3 col-lg-6">
                        {$stat_imported}
                    </div>
                    <div class="col-xl-3 col-lg-6">
                        {$stat_scraped}
                    </div>
                    <div class="col-xl-3 col-lg-6">
                        {$stat_published}
                    </div>
                    <div class="col-xl-3 col-lg-6">
                        {$stat_categories}
                    </div>
                </div>
            </div>
        HTML;
        
        //Return Templated:
        return <<<HTML
            <div class='container'>
                {$stats}
            </div>
        HTML;

    }
);
