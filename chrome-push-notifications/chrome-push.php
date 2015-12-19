<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly
/*
Plugin Name: Chrome Push Notifications
Plugin URI: http://www.github.com/aglipanci
Description: Chrome Push Notifications GCM
Version: 1.1.5
Author: Agli Panci
Author URI: http://www.aglipanci.com/
 */

if (!defined('CHROME_PUSH_PLUGIN_DIR')) {
    define('CHROME_PUSH_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('CHROME_PUSH_PLUGIN_URL')) {
    define('CHROME_PUSH_PLUGIN_URL', plugin_dir_url(__FILE__));
}

require_once CHROME_PUSH_PLUGIN_DIR . 'includes/class-chrome-push.php';

register_activation_hook(__FILE__, array('WPChromePush', 'installFunctions'));

//start the plugin
WPChromePush::init();
