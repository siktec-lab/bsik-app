<?php

require_once __DIR__ . DIRECTORY_SEPARATOR .'..' . DIRECTORY_SEPARATOR . 'bsik.php';

// Force no time limit
set_time_limit(0);

// Force no memory limit
ini_set('memory_limit', '540M');

// For mysql make sure that the connection will not be closed connect_timeout, wait_timeout, interactive_timeout, allow_persistent
ini_set('mysql.connect_timeout', 28800);
ini_set('mysql.allow_persistent', 1);

/*
    Make sure that the mysql server has the following settings:
    wait_timeout=28800
    net_read_timeout=90
    net_write_timeout=90
    interactive_timeout=28800
    connect_timeout=10
*/

// Add your own commands:

use \Siktec\Bsik\Std;
use \Siktec\Bsik\Base;
use \Siktec\Bsik\App\Cli;
use \Siktec\Bsik\Tools\Profiler;

const CLI_COMMANDS_FOLDER = __DIR__ ;
const CLI_WORKING_FOLDER  = __DIR__ . DIRECTORY_SEPARATOR . '..';

// Load the configuration:
Base::configure($conf); // Load the configuration

// $prof_full = new Profiler();
// $prof_full->start();
// $prof_sub = new Profiler();

// Initialize the CLI application of the BSIK framework
$bsik_cli = new Cli\Run(cwd : CLI_WORKING_FOLDER);

// Automatically load all commands from the cli folder that start with an underscore
$list_commands = Std::$fs::list_folder(CLI_COMMANDS_FOLDER);
foreach ($list_commands ?? [] as $file) {
    /** @var SplFileInfo $file  */
    if ($file->isFile() && $file->getExtension() === 'php' && str_starts_with( $file->getFilename(), "_")) {

        // Load the command class
        $class = ltrim($file->getBasename('.' . $file->getExtension()), "_" );
        require_once $file->getPathname();

        // Add the command to the CLI application:
        if (class_exists($class)) {
            $bsik_cli->cli->add(
                command : new $class( 
                    cwd : CLI_WORKING_FOLDER
                ), 
                alias   : $class::ALIAS
            );
        }
    }
}

// Handle the CLI application with default argv or custom argv
$bsik_cli->handle(
    argv : null // $_SERVER['argv']
);
    
    
// print "Full: ~" . $prof_full->end(precision: 4) . " sec" . PHP_EOL;