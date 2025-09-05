<?php

if (!defined("ABSPATH")) {
    exit();
}

/*
Plugin Name: wp-toDo
Plugin URI: https://wp-code.eu
Description: To-Do List plugin for WordPress
Version: 1.0
Author: Mihail Prohorov
Author URI: https://wp-code.eu
*/

define("WPTODO_PLUGIN_DIR", plugin_dir_path(__FILE__));

class wpToDo
{
    public function __construct()
    {
        add_action("init", array($this, "init"));
    }

    public function init()
    {
        load_plugin_textdomain("wp-toDo", false, WPTODO_PLUGIN_DIR . "languages");
    }
}


if (class_exists("wpToDo")) {
    $wpToDo = new wpToDo();
}
