<?php

if (!defined("ABSPATH")) {
    exit();
}

/**
 * Uninstall the plugin
 */

function wpToDo_uninstall()
{
    global $wpdb;
    $table_name = $wpdb->prefix . "todo";
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_uninstall_hook(__FILE__, "wpToDo_uninstall");
