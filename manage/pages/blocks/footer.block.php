<?php

use \Siktec\Bsik\Render\Blocks\Block;
use \Siktec\Bsik\Render\Templates\Template;

class FooterBlock extends Block {

    /** 
     * The header default values / settings
     * @override
     */
    public array $defalts   = [
        "js_body"       => "",
        "extra_html"    => "",
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
        $this->settings["js_body"]    = $this->Page->render_libs("js", "body", false);
        return $this->engine->render("footer", $this->settings);
    }

}


