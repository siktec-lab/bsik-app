<?php

use \Siktec\Bsik\Builder\Components;
use \Siktec\Bsik\Render\Templates\Template;

/****************************************************************************/
/******** component creates a badge list row  *******************************/
/****************************************************************************/

Components::register_once("badges_list", function(array $badges, string $color = "secondary", array $colors = []) {
    $badges_html = [];
    foreach ($badges as $badge) {
        $badges_html[] = sprintf(
            "<span class='badge bg-%s'>%s</span>",
            isset($colors[$badge]) ? $colors[$badge] : $color,
            ucfirst($badge)
        );
    }
    return implode(PHP_EOL, $badges_html);
});



/****************************************************************************/
/******** component creates a badge list row  *******************************/
/****************************************************************************/
Components::register_once("modules_list", function(array $modules, $title, Template $eng) {
    
    $title_html    = Components::title($title, attrs : ["class" => "module-title"]);
    $statuses = [0 => "disable", 1 => "active"];
    $modules_data = [];

    foreach($modules as $name => $module) {
        $status = $statuses[$module["status"] ?? 0] ?? $module["status"];
        $data = [
            "name"                  => $module["name"],
            "status"                => $status,
            "updates"               => ($status === "active" && $module["updates"] === 1),
            "status_tag"            => $status === "active" ? "Activated" : "Disabled",
            "status_color"          => strtolower($status),
            "status_button"         => $status === "active" ? "Disable" : "Activate",
            "status_toggle"         => $status === "active" ? $statuses[0] : $statuses[1],
            "version"               => $module["version"] ?? "0.0.0",
            "author"                => $module["info"]["author"]["name"] ?? "Unknown",
            "web"                   => $module["info"]["author"]["web"] ?? "Unknown",
            "tags"                  => Components::badges_list(["general", "new"]),
            "title"                 => $module["info"]["title"] ?? "No Title",
            "description"           => $module["info"]["description"] ?? "No Description",
            "is_core"               => ($module["core"] ?? 0) ? true : false,
            "date_installed"        => $module["created"] ?? "00/00/0000"

        ];
        if ($data["is_core"]) {
            $data["status_tag"]     = "Core Module";
            $data["status_color"]   = "core";
        }
        if ($data["updates"]) {
            $data["status_tag"]     = "Update Pending";
            $data["status_color"]   = "has-update";
        }
        $modules_data[] = $data;
    }
    return $eng->render(
        name : "modules_list", 
        context : [ "modules" => $modules_data, "title_ele" => $title_html ]
    );
});