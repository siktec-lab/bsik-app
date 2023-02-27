<?php

use \Siktec\Bsik\Render\Blocks\Block;
use \Siktec\Bsik\Render\Templates\Template;
use \Siktec\Bsik\CoreSettings;

class HeaderBlock extends Block {

    /** 
     * The header default values / settings
     * @override
     */
    public array $defaults   = [
        "doc_type"          => "html",
        "meta_token"        => "",
        "title"             => "title",
        "meta" => [
            "viewport"      => "",
            "charset"      => "",
            "author"        => "",
            "description"   => "",
            "api"           => "",
            "module"        => "",
            "module_sub"    => ""
        ],
        "ex_meta"           => "",
        "favicon" => [
            "path"          => "",
            "name"          => "",
        ],
        "css_libs"          => "",
        "js_libs"           => "",
        "body_tag"          => "",
        "body_css_includes" => "",
        "render-meta-tags"  => true
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
        $this->settings["css_libs"]             = $this->Page->render_libs("css", "head", false);
        $this->settings["js_libs"]              = $this->Page->render_libs("js", "head", false);
        $this->settings["body_css_includes"]    = $this->Page->render_libs("css", "bold", false);
        $this->settings["meta"]                 = $this->Page->meta->defined_metas;
        $this->settings["ex_meta"]              = $this->Page->meta->additional_meta;
        $this->settings["favicon"] = [
            "name" => "favicon", 
            "path" => CoreSettings::$url["manage-lib"]."/img/fav"
        ];
        $this->settings["meta_token"] = $this->Page::$token["meta"];
        $this->settings["body_tag"]   = $this->Page->custom_body_tag;

        return $this->engine->render("header", $this->settings);
    }

}