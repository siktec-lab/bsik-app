<?php

use \Siktec\Bsik\StdLib as BsikStd;
use \Siktec\Bsik\Render\Blocks\Block;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\CoreSettings;

/**
 * SideMenuBlock
 * 
 * This class is used to render the side menu block of a specific module.
 * 
 * @package Bsik\Render\Blocks
 * 
 */
class SideMenuBlock extends Block {

    /** 
     * The header default values / settings
     * @override
     */
    public array $defaults   = [
        "menu" => []
    ]; 

    public $Page  = null;

    public function __construct($Page, Template|null $engine, array $settings)
    {
        parent::__construct(settings : $settings, engine : $engine);
        $this->Page = $Page;
        $this->template();
    }
    /** 
     * Manipulate and add templates
     * @override
     */
    public function template() {

    }
    
    /**
     * render
     * the render logic - costum behavior before rendering
     * this will be called by the page controller render method
     * @param  mixed $Page
     * @param  array $values
     * @return string
     */
    public function render() : string {
        
        $current            = $this->Page::$module->module_name;
        $current_sub_menu   = $this->Page::$module->which;
        $menu               = [];
        //Prepare menu:
        foreach ($this->Page->menu as $entry) {
            $parts = BsikStd\Arrays::get_from($entry, ["text", "title", "desc", "icon", "action", "sub"], "");
            $parts["loaded"] = strtolower($parts["action"]) === $current;

            //Save title + desc for later when rendering module:
            if ($parts["loaded"]) {
                $this->Page::$module->header["sub-title"]   = $parts["desc"];
                $this->Page::$module->header["which"]       = "";
                $this->Page::$module->header["title"]       = $parts["title"];
            }

            //Create module base url:
            
            $parts["url"] = CoreSettings::$url["manage"]."/".strtolower($parts["action"]);
            $parts["has_sub"] = false;
            $parts["sub_menu"] = [];

            //If sub menu is defined?
            if (!empty($parts["sub"])) {
                $parts["has_sub"] = true;
                foreach ($parts["sub"] as $sub) {
                    $sub_parts = BsikStd\Arrays::get_from($sub, ["text", "title", "desc", "icon", "action"], "");
                    $sub_parts["loaded"] = $parts["loaded"] && (strtolower($sub_parts["action"]) === $current_sub_menu);
                    if ($sub_parts["loaded"]) {
                        $this->Page::$module->header["sub-title"] = $sub_parts["desc"];
                        $this->Page::$module->header["which"]     = $sub_parts["title"];
                    }
                    $sub_parts["url"] = $parts["url"]."/".$sub_parts["action"];
                    $parts["sub_menu"][] = $sub_parts;
                }
            }
            $menu[] = $parts;
        }
        $this->settings["current"]          = $current;
        $this->settings["current_sub_menu"] = $current_sub_menu;
        $this->settings["menu"]             = $menu;

        return $this->engine->render("sidemenu", $this->settings);
    }

}