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
        add_action("admin_init", array($this, "deleteTask"));
        add_action("admin_init", array($this, "editTask"));
        add_action('init', array($this, 'register_custom_blocks'));
        add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_assets'));
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

        wp_enqueue_script("wp-toDo-admin", plugin_dir_url(__FILE__) . "assets/js/admin.js", array(), "1.0", true);
    }

    function register_custom_blocks()
    {
        register_block_type(__DIR__ . '/build' . __DIR__ . '/build/blocks/wp-toDo-block');
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
                                    <div class="d-flex">
                                        <a href="#"
                                            class="button button-primary edit-task-button mr-1"
                                            data-id="<?php echo esc_attr($task->id); ?>"
                                            data-title="<?php echo esc_attr($task->title); ?>"
                                            data-description="<?php echo esc_attr($task->description); ?>">
                                            <?php _e("Edit", "wp-toDo"); ?>
                                        </a>

                                        <a href="#" class="button danger-button delete-task-button"
                                            data-task-id="<?php echo esc_attr($task->id); ?>">
                                            <?php _e("Delete", "wp-toDo"); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <dialog id="delete-confirm-dialog">
                                <p><?php _e("Are you sure you want to delete this task?", "wp-toDo"); ?></p>
                                <menu>
                                    <form method="post" id="delete-task-form">
                                        <?php wp_nonce_field("delete_task_" . $task->id, "delete_task_nonce"); ?>
                                        <input type="hidden" name="task_id" id="task-id-field">
                                        <input type="hidden" name="delete_task" value="1">
                                        <button type="submit" id="confirm-delete"><?php _e("Delete", "wp-toDo"); ?></button>
                                    </form>

                                    <button id="cancel-delete" type="button"><?php _e("Cancel", "wp-toDo"); ?></button>
                                </menu>
                            </dialog>

                            <dialog id="edit-task-dialog" class="dialog">
                                <form method="post">
                                    <?php wp_nonce_field("edit_task_action", "edit_task_nonce"); ?>
                                    <input type="hidden" name="task_id" id="edit-task-id">

                                    <label for="edit-task-title" class="d-block mb-1">
                                        <?php _e("Title", "wp-toDo"); ?>
                                    </label>
                                    <input type="text" id="edit-task-title" class="d-block w-full mt-1 mb-1" name="title" required>

                                    <label for="edit-task-status" class="d-block mb-1"><?php _e("Status", "wp-toDo"); ?></label>
                                    <select id="edit-task-status" name="status" class="d-block w-full mt-1 mb-1">
                                        <option value="pending"><?php _e("Pending", "wp-toDo"); ?></option>
                                        <option value="in_progress"><?php _e("In Progress", "wp-toDo"); ?></option>
                                        <option value="done"><?php _e("Done", "wp-toDo"); ?></option>
                                    </select>

                                    <label for="edit-task-description" class="d-block mb-1"><?php _e("Description", "wp-toDo"); ?></label>
                                    <textarea id="edit-task-description" name="description" class="d-block w-full mt-1 mb-1"></textarea>

                                    <menu class="d-flex justify-content-between p-0">
                                        <button type="submit" name="update_task" class="button button-primary">
                                            <?php _e("Update Task", "wp-toDo"); ?>
                                        </button>
                                        <button type="button" id="cancel-edit" class="button">
                                            <?php _e("Cancel", "wp-toDo"); ?>
                                        </button>
                                    </menu>
                                </form>
                            </dialog>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else : ?>
            <div class=" wrap">
                <h1 class="mb-1"><?php _e("No tasks found.", "wp-toDo"); ?></h1>
            </div>
        <?php endif; ?>
    <?php

    }

    public function deleteTask()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "todo";

        if (!empty($_POST["task_id"]) && !empty($_POST["delete_task_nonce"])) {
            $task_id = intval($_POST["task_id"]);

            if (!wp_verify_nonce($_POST["delete_task_nonce"], "delete_task_" . $task_id)) {
                return;
            }

            if (!current_user_can("manage_options")) {
                return;
            }

            $wpdb->delete($table_name, ["id" => $task_id], ["%d"]);

            echo '<div class="updated"><p>' . __("Task deleted successfully!", "wp-toDo") . '</p></div>';
        }
    }

    public function editTask()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "todo";

        if (isset($_POST["update_task"]) && isset($_POST["edit_task_nonce"])) {
            $task_id = intval($_POST["task_id"]);

            if (!wp_verify_nonce($_POST["edit_task_nonce"], "edit_task_action")) {
                return;
            }

            if (!current_user_can("manage_options")) {
                return;
            }

            $title = sanitize_text_field($_POST["title"]);
            $description = sanitize_textarea_field($_POST["description"]);
            $status = sanitize_text_field($_POST["status"]);

            $wpdb->update(
                $table_name,
                ["title" => $title, "description" => $description, "status" => $status],
                ["id" => $task_id],
                ["%s", "%s", "%s"],
                ["%d"]
            );

            echo '<div class="updated"><p>' . __("Task updated successfully!", "wp-toDo") . '</p></div>';
        }
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
            echo '<div class="updated"><p>' . __("Task added successfully!", "wp-toDo") . '</p></div>';
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
                                <option value="in_progress"><? _e("In Progress", "wp-toDo") ?></option>
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
