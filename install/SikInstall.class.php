<?php
/******************************************************************************/
// Created by: Shlomi Hassid.
// Release Version : 1.0.1
// Creation Date: date
// Copyright 2020, Shlomi Hassid.
/******************************************************************************/
/*****************************      Changelog       ****************************
1.0.1:
    ->creation - initial
*******************************************************************************/
if (!defined("DS")) define("DS", DIRECTORY_SEPARATOR);

class SikInstall {

    public $adjust_root = DS."..".DS;
    public $root = ".";
    public $folders = [];

    public function __construct() {
        //Create objects:
        $this->folders= (object)$this->folders;
        //Set root:
        $this->root = __DIR__.$this->adjust_root;
    }

    public function set_core_folder(string $name, string $path, bool $clear) {
        $this->folders->{$name} = (object)[
            "path"  => $path,
            "dirs"  => [],
            "clear" => $clear
        ];
    }

    public function add_to_folder(string $folder, string $dir, string $from, string $copy = ".") {
        $this->folders->{$folder}->dirs[] = [
            "name" => $dir,
            "from" => $from,
            "copy" => $copy
        ];
    }

    public function folder_exists(string $path) {
        return is_dir($path);
    }

    public function make_core_folders() {
        foreach ($this->folders as $fname => $f) {
            if (!$this->folder_exists($f->path)) {
                $this->message("Folder [{$fname}] missing - creating folder.", true);
                mkdir($f->path);
            } else {
                $this->message("Folder [{$fname}] found.", true);
            }
        }
    }

    public function clean_destinations() {
        foreach ($this->folders as $fname => $f) {
            if ($f->clear) {
                $this->clear_folder($f->path, false);
                $this->message("Folder [{$fname}] truncated.", true);
            } else {
                $this->message("Folder [{$fname}] removing child directories.", true);
                foreach ($f->dirs as $dname) {
                    if ($this->folder_exists($f->path.$dname["name"])) {
                        $this->clear_folder($f->path.$dname["name"], true);
                        $this->message("Folder [{$f->path}{$dname["name"]}] removed.", true);
                    }
                }
            }
        }
    }

    public function create_destinations() {
        foreach ($this->folders as $fname => $f) {
            $this->message("Working on folder [{$fname}] creating child's...", true);
            foreach ($f->dirs as $dname) {
                mkdir($f->path.$dname["name"]);
                $this->message("Folder [{$dname["name"]}] created in [{$fname}].", true);
            }
        }
    }

    public function move_destinations() {
        foreach ($this->folders as $fname => $f) {
            $this->message("Working on folder [{$fname}] creating files...", true);
            foreach ($f->dirs as $dname) {
                $dest = $f->path.$dname["name"];
                $source = $dname["from"];
                $this->message("Copying files [{$dname["copy"]}] to folder [{$fname}][{$dname["name"]}]", true);
                switch ($dname["copy"][0]) {
                    case ".": {
                        $dir = dir($source);
                        while (false !== $entry = $dir->read()) {
                            // Skip pointers
                            if ($entry == '.' || $entry == '..') continue;
                            $this->xcopy(rtrim($source, DS).DS.ltrim($entry, DS), rtrim($dest, DS).DS.ltrim($entry, DS));
                        }
                    } break;
                    case "$": {
                        $dir = dir($source);
                        $ends_with = ltrim($dname["copy"], "$");
                        $length = strlen( $ends_with );
                        if($length) {
                            while (false !== $entry = $dir->read()) {
                                // Skip pointers
                                if ($entry == '.' || $entry == '..') continue;
                                if (substr($entry, -$length) === $ends_with) {
                                    $this->xcopy(rtrim($source, DS).DS.ltrim($entry, DS), rtrim($dest, DS).DS.ltrim($entry, DS));
                                }
                            }
                        }
                    } break;
                    case "^": {
                        $dir = dir($source);
                        $startswith = ltrim($dname["copy"], "^");
                        while (false !== $entry = $dir->read()) {
                            // Skip pointers
                            if ($entry == '.' || $entry == '..') continue;
                            if (strpos($entry, $startswith) === 0) {
                                $this->xcopy(rtrim($source, DS).DS.ltrim($entry, DS), rtrim($dest, DS).DS.ltrim($entry, DS));
                            }
                        }
                    } break;
                    case "*": {
                        $dir = dir($source);
                        $has_str = ltrim($dname["copy"], "*");
                        while (false !== $entry = $dir->read()) {
                            // Skip pointers
                            if ($entry == '.' || $entry == '..') continue;
                            if (strpos($entry, $has_str) !== false) {
                                $this->xcopy(rtrim($source, DS).DS.ltrim($entry, DS), rtrim($dest, DS).DS.ltrim($entry, DS));
                            }
                        }
                    } break;
                    case "=": {
                        $dir = dir($source);
                        $is_str = explode('|',ltrim($dname["copy"], "="));
                        if (count($is_str)) {
                            while (false !== $entry = $dir->read()) {
                                // Skip pointers
                                if ($entry == '.' || $entry == '..') continue;
                                if (in_array($entry, $is_str)) {
                                    $this->xcopy(
                                        rtrim($source, DS).DS.ltrim($entry, DS), 
                                        rtrim($dest, DS).DS.ltrim($entry, DS)
                                    );
                                }
                            }
                        }
                    } break;
                    default: $this->message("ERROR ON DIR [{$source}] : Copy operation unknown [{$dname["copy"]}]", true);
                }
            }
        }
    }
    public function clear_folder(string $dir, bool $self = false) {
        $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ( $ri as $file ) {
            $file->isDir() ?  rmdir($file) : unlink($file);
        }
        if ($self) {
            rmdir($dir);
        }
    }
    public function xcopy($source, $dest, $permissions = 0755)
    {
        $sourceHash = $this->hash_directory($source);
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        // Simple copy for a file
        if (is_file($source)) {
            $file = explode(DS, $source);
            //print 
            //return copy($source, $dest);
            return copy($source, is_dir($dest) ? rtrim($dest, DS).DS.end($file) : $dest);
        }
        // Make destination directory
        if (!is_dir($dest)) {
            mkdir($dest, $permissions, true);
        }
        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // Deep copy directories
            if($sourceHash != $this->hash_directory($source.DS.$entry)){
                $this->xcopy($source.DS.$entry, $dest.DS.$entry, $permissions);
            }
        }
        // Clean up
        $dir->close();
        return true;
    }
    
    function hash_directory($directory) {
        if (!is_dir($directory)){ return false; }
        $files = array();
        $dir = dir($directory);
        while (false !== ($file = $dir->read())){
            if ($file != '.' and $file != '..') {
                if (is_dir($directory . DS . $file)) { $files[] = $this->hash_directory($directory . DS . $file); }
                else { $files[] = md5_file($directory . DS . $file); }
            }
        }
        $dir->close();
        return md5(implode('', $files));
    }

    public function message($mes, bool $sub = false) {
        if (!$sub) print " ~ BSIK Says -> ".$mes.PHP_EOL;
        else       print "             -> ".$mes.PHP_EOL;
    }
}