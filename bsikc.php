<?php

require_once __DIR__ . '/bsik.php';


// Add your own commands:

use \Siktec\Bsik\Base;
use \Siktec\Bsik\App\Cli;
use \Siktec\Bsik\Tools\Profiler;

Base::configure($conf); // Load the configuration

// require_once __DIR__ . '/cli/InstallModuleCommand.php';

// $prof_full = new Profiler();
// $prof_full->start();
// $prof_sub = new Profiler();

// Initialize the CLI application of the BSIK framework
$bsik_cli = new Cli\Run(cwd : __DIR__);


// $bsik_cli->cli->add(
//     command : new Siktec\Bsik\App\Cli\Commands\InstallModuleCommand( 
//         cwd : __DIR__,
//         folder_path : null,
//         as_json : false
//     ), 
//     alias   : Siktec\Bsik\App\Cli\Commands\InstallModuleCommand::ALIAS
// );

// Handle the CLI application with default argv or custom argv
$bsik_cli->handle(
    argv : null // $_SERVER['argv']
);
    
    
// print "Full: ~" . $prof_full->end(precision: 4) . " sec" . PHP_EOL;