<?php

use \Siktec\Bsik\Render\Blocks\Block;
use \Siktec\Bsik\Render\Templates\Template;

class TopBarBlock extends Block {

    /** 
     * The header default values / settings
     * @override
     */
    public array $defaults   = [
        "plat_logo" => ""
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
        $this->settings["plat_logo"] = $this->Page->get("plat-logo");
        $this->settings["plat_user_email"] = $this->Page->get("plat-user-email");
        $this->settings["plat_user_fname"] = $this->Page->get("plat-user-fname");
        $this->settings["plat_user_lname"] = $this->Page->get("plat-user-lname");
        $this->settings["plat_admin_url_base"] = $this->Page->get("plat-admin-url-base");
        
        return $this->engine->render("topbar", $this->settings);
    }

}