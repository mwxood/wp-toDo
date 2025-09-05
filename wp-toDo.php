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
        add_action("admin_enqueue_scripts", array($this, "admin_enqueue_style"));
    }

    public function init()
    {
        load_plugin_textdomain("wp-toDo", false, WPTODO_PLUGIN_DIR . "languages");
    }

    public function admin_enqueue_style()
    {
        wp_enqueue_style(
            "wp-toDo-admin",
            plugin_dir_url(__FILE__) . "assets/css/admin.css",
            array(),
            "1.0",
            "all"
        );
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
            "Add new Task",
            "Add new Task",
            "manage_options",
            "wp-toDo",
            array($this, "admin_page"),
            "dashicons-list-view",
            25
        );

        add_submenu_page(
            "wp-toDo",
            "All Tasks",
            "All Tasks",
            "manage_options",
            "wp-toDo-all-tasks",
            array($this, "view_tasks_page_html"),
            "dashicons-admin-generic",
            25
        );

        add_submenu_page(
            "wp-toDo",
            "Settings",
            "Settings",
            "manage_options",
            "wp-toDo-settings",
            array($this, "view_settings_html"),
            "dashicons-admin-generic",
            25
        );
    }

    public function view_tasks_page_html()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "todo";

        $tasks = $wpdb->get_results("SELECT * FROM $table_name");

?>

        <?php if ($tasks) : ?>
            <div class="wrap">
                <h1 class="mb-1"><? _e("All Tasks", "wp-toDo") ?></h1>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><? _e("Title", "wp-toDo") ?></th>
                            <th><? _e("Description", "wp-toDo") ?></th>
                            <th><? _e("Status", "wp-toDo") ?></th>
                            <th><? _e("Created At", "wp-toDo") ?></th>
                            <th><? _e("Updated At", "wp-toDo") ?></th>
                            <th><? _e("Actions", "wp-toDo") ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tasks as $task) : ?>
                            <tr>
                                <td><?php echo esc_html($task->title); ?></td>
                                <td><?php echo esc_html($task->description); ?></td>
                                <td><?php echo esc_html($task->status); ?></td>
                                <td><?php echo esc_html(date("Y-m-d", strtotime($task->created_at))); ?></td>
                                <td><?php echo esc_html(date("Y-m-d", strtotime($task->updated_at))); ?></td>
                                <td>
                                    <a class="button button-primary" href="<?php echo esc_url(add_query_arg("action", "edit", "")); ?>"><? _e("Edit", "wp-toDo") ?></a>
                                    <a class="button danger-button" href="<?php echo esc_url(add_query_arg("action", "delete", "")); ?>"><? _e("Delete", "wp-toDo") ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else : ?>
            <div class="wrap">
                <h1 class="mb-1"><?php _e("No tasks found.", "wp-toDo"); ?></h1>
            </div>
        <?php endif; ?>
    <?php

    }

    public function view_settings_html() {}

    public function admin_page()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "todo";

        if (isset($_POST["submit_todo"])) {
            $wpdb->insert($table_name, [
                "title" => sanitize_text_field($_POST["title"]),
                "description" => sanitize_textarea_field($_POST["description"]),
                "status" => sanitize_text_field($_POST["status"]),
                "created_at" => current_time("mysql"),
                "updated_at" => current_time("mysql"),
            ]);
            echo '<div class="updated"><p>' . _e("Task added successfully!", "wp-toDo") . '</p></div>';
        }

    ?>
        <div class="wrap">
            <h1><? _e("Add Task", "wp-toDo") ?></h1>
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
