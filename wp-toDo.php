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
        add_action("admin_menu", array($this, "admin_menu"));
        register_activation_hook(__FILE__, array($this, "create_table"));
    }

    public function init()
    {
        load_plugin_textdomain("wp-toDo", false, WPTODO_PLUGIN_DIR . "languages");
    }

    public function create_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "todo";

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            description text,
            status varchar(20) NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function admin_menu()
    {
        add_menu_page(
            "wp-toDo",
            "wp-toDo",
            "manage_options",
            "wp-toDo",
            array($this, "admin_page"),
            "dashicons-list-view",
            25
        );
    }

    public function admin_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "todo";

        if (isset($_POST["submit_todo"])) {
            $wpdb->insert($table_name, [
                "title" => sanitize_text_field($_POST["title"]),
                "description" => sanitize_textarea_field($_POST["description"]),
                "status" => sanitize_text_field($_POST["status"]),
            ]);
            echo '<div class="updated"><p>Задачата е добавена успешно!</p></div>';
        }

?>
        <div class="wrap">
            <h1>To-Do List</h1>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th><label for="title"><? _e("Title", "wp-toDo") ?></label></th>
                        <td><input type="text" name="title" required></td>
                    </tr>
                    <tr>
                        <th><label for="description"><? _e("Description", "wp-toDo") ?></label></th>
                        <td><textarea name="description" rows="5"></textarea></td>
                    </tr>
                    <tr>
                        <th><label for="status"><? _e("Status", "wp-toDo") ?></label></th>
                        <td>
                            <select name="status">
                                <option value="pending"><? _e("Pending", "wp-toDo") ?></option>
                                <option value="done"><? _e("Done", "wp-toDo") ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <input type="submit" name="submit_todo" class="button button-primary" value="<? _e("Add Task", "wp-toDo") ?>">
            </form>
        </div>
<?php
    }
}


if (class_exists("wpToDo")) {
    $wpToDo = new wpToDo();
}
