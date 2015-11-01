<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

cp_db_remove();
cp_options_remove();

function cp_options_remove()
{
    delete_option('web_push_project_number');
    delete_option('web_push_api_key');
    delete_option('web_push_debuger');
    delete_option('web_push_icon');
    delete_option('web_push_post_types');
}

function cp_db_remove()
{

    global $wpdb;

    $tables = array('push_subscribers', 'push_notifications');

    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . $table);
    }

}
