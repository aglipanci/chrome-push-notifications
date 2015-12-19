<?php if (!defined('ABSPATH')) {
    exit;
}
// Exit if accessed directly

class WPChromePush
{

    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function init()
    {

        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->hooks();
    }

    /**
     * All hooks registration goes here.
     * @return void
     */
    private function hooks()
    {
        add_action('init', array($this, 'pullNotifications'));
        add_action('admin_menu', array($this, 'registerMenues'));
        add_action('transition_post_status', array($this, 'onPostStatusChange'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'registerPushJs'));
        add_action('wp_ajax_nopriv_pn_register_device', array($this, 'registerDevice'));
        add_action('wp_ajax_pn_register_device', array($this, 'registerDevice'));
        add_action('wp_head', array($this, 'manifestFile'));
        add_action('add_meta_boxes', array($this, 'addMetaBox'));

        if (!$this->checkSSL()) {
            add_action('admin_notices', array($this, 'checkSiteConfigNotice'));
        }

    }

    /**
     * Manifest link in header
     * @return [type] [description]
     */
    public function manifestFile()
    {
        echo '<link rel="manifest" href="manifest.json">';
    }

    /**
     * ServiceWorker (sw.js) creation
     * @return void
     */
    public static function writeServiceWorker()
    {
        $tmp_sw = file_get_contents(CHROME_PUSH_PLUGIN_DIR . 'assets/js/tmp/sw.js.tmp');
        $tmp_sw = str_replace('DEXIE_PATH', self::fixHttpsURL(CHROME_PUSH_PLUGIN_URL) . 'assets/js/library/Dexie.min.js', $tmp_sw);
        $tmp_sw = str_replace('SUB_PATH', get_option('siteurl') . '/?subId=', $tmp_sw);
        $tmp_sw = str_replace('ICON_PATH', get_option('web_push_icon'), $tmp_sw);
        $tmp_sw = str_replace('DEBUG_VAR', true, $tmp_sw);
        $form_url = 'admin.php?page=chrome-push';
        self::writeFile($form_url, $tmp_sw, 'sw.js');

    }

    /**
     * Admin menus registration
     * @return void
     */
    public function registerMenues()
    {
        add_menu_page('Chrome Web Push', 'Chrome Push', 'manage_options', 'chrome-push', array($this, 'registerSettingsPage'), 'dashicons-cloud');
        add_submenu_page('chrome-push', 'Settings', 'Settings', 'manage_options', 'chrome-push', array($this, 'registerSettingsPage'));
        add_submenu_page('chrome-push', 'Subscribers', 'Subscribers', 'manage_options', 'chrome-push-subscribers', array($this, 'subscriptionStatistics'));
        add_submenu_page('chrome-push', 'Notifications', 'Notifications', 'manage_options', 'chrome-push-notifications', array($this, 'notificationStatistics'));
        add_submenu_page('chrome-push', 'New message', 'New message', 'manage_options', 'chrome-push-new-message', array($this, 'composePush'));
    }

    /**
     * Add each notifications on the queue to all users
     * @param  Array $data Notification data array
     * @return void
     */
    private function putNotificationOnQueue($data)
    {

        global $wpdb;
        $notifications_table_name = $wpdb->prefix . 'push_notifications';
        $push_subscribers_table = $wpdb->prefix . 'push_subscribers';

        $time = date("Y-m-d H:i:s");

        $wpdb->insert($notifications_table_name,
            array(
                'notification' => json_encode($data),
                'created_at' => $time,
            )
        );

        $notification_id = $wpdb->insert_id;
        $subscribers = $wpdb->get_results('SELECT id, notifications FROM ' . $push_subscribers_table);

        foreach ($subscribers as $subscriber) {
            $existing_notifications = is_array(json_decode($subscriber->notifications)) ? json_decode($subscriber->notifications) : array();
            $existing_notifications[] = $notification_id;
            $result = $wpdb->update(
                $push_subscribers_table,
                array(
                    'notifications' => json_encode($existing_notifications),
                ),
                array(
                    'id' => $subscriber->id,
                )

            );
        }
    }

    /**
     * Register a new subcriber if it's not in the list
     * @return void
     */
    public function registerDevice()
    {

        if (isset($_POST["regId"]) && !empty($_POST['regId'])) {

            global $wpdb;
            $endpoint = explode('/', sanitize_text_field(rawurldecode($_POST["regId"])));
            $regId = end($endpoint);
            $time = date("Y-m-d H:i:s");
            $subscribers_table = $wpdb->prefix . 'push_subscribers';
            $sql = "SELECT gcm_regid FROM $subscribers_table WHERE gcm_regid='$regId'";
            $result = $wpdb->get_results($sql);

            if (!$result) {
                $sql = "INSERT INTO $subscribers_table (gcm_regid, created_at) VALUES ('$regId', '$time')";
                $q = $wpdb->query($sql);

                echo "Device registered!";

            } else {
                echo 'You\'re already registered';
            }
        }
        exit();
    }

    /**
     * Get all notifications for the provided subscriber ID
     * @return void
     */
    public function pullNotifications()
    {
        if (isset($_GET['subId']) && !empty($_GET['subId'])) {

            global $wpdb;
            $endpoint = explode('/', sanitize_text_field(rawurldecode($_GET["subId"])));
            $subscriber_id = end($endpoint);
            $push_subscribers_table = $wpdb->prefix . 'push_subscribers';
            $push_notifications_table = $wpdb->prefix . 'push_notifications';

            $subscriber = $wpdb->get_row($wpdb->prepare('SELECT notifications FROM ' . $push_subscribers_table . ' WHERE gcm_regid = %s', $subscriber_id));
            $notifications_ids_array = json_decode($subscriber->notifications);
            $notifications_ids = implode(',', $notifications_ids_array);

            $notifications = $wpdb->get_results('SELECT id, notification FROM ' . $push_notifications_table . ' WHERE id IN (' . $notifications_ids . ')');

            //update Hits
            $wpdb->query('UPDATE ' . $push_notifications_table . ' SET hits = hits + 1 WHERE id IN (' . $notifications_ids . ')');

            $respose = array();
            foreach ($notifications as $notification) {
                $notification_data = json_decode($notification->notification);
                $respose['notifications'][] = array(
                    'url' => $notification_data->url,
                    'title' => $notification_data->title,
                    'body' => $notification_data->message,
                    'tag' => md5(rand(4, 7)),
                );
            }

            $result = $wpdb->update(
                $push_subscribers_table,
                array(
                    'notifications' => null,
                ),
                array(
                    'gcm_regid' => $subscriber_id,
                )

            );

            header('Content-Type: application/json');
            echo json_encode($respose);
            exit;

        }

    }

    /**
     * Write File generic function.
     * @param  String $form_url     URL of the form to return if no permissions
     * @param  String $file_content Content of the file to be writen
     * @param  String $filename     Name of the new file
     * @return void
     */
    public static function writeFile($form_url, $file_content, $filename)
    {
        global $wp_filesystem;

        //check_admin_referer('web_push');

        $method = '';
        $context = ABSPATH;

        //$form_url = wp_nonce_url($form_url, 'web_push'); //page url with nonce value

        if (!self::initFilesystem($form_url, $method, ABSPATH)) {
            return false;
        }

        $target_dir = $wp_filesystem->find_folder($context);
        $target_file = trailingslashit($target_dir) . $filename;

        if (!$wp_filesystem->put_contents($target_file, $file_content, FS_CHMOD_FILE)) {
            return new WP_Error('writing_error', 'Error when writing file');
        }
        //return error object

        return $file_content;
    }

    /**
     * Initialization of the FileSystem class
     * @param  String $form_url
     * @param  String $method
     * @param  String $context
     * @param  String $fields
     * @return void
     */
    public static function initFilesystem($form_url, $method, $context, $fields = null)
    {
        global $wp_filesystem;
        include_once ABSPATH . 'wp-admin/includes/file.php';
        if (false === ($creds = request_filesystem_credentials($form_url, $method, false, $context, $fields))) {

            return false;
        }

        if (!WP_Filesystem($creds)) {

            request_filesystem_credentials($form_url, $method, true, $context);
            return false;
        }

        return true; //filesystem object successfully initiated
    }

    /**
     * Register the JS files and vars
     * @return void
     */
    public function registerPushJs()
    {

        wp_register_script('web-push', $this->fixHttpsURL(CHROME_PUSH_PLUGIN_URL) . 'assets/js/push.js', array('jquery'), '1.0', true);

        $data = array(
            'sw_path' => get_option('siteurl') . '/sw.js',
            'reg_url' => get_option('siteurl') . '/?regId=',
            'ajaxurl' => admin_url('admin-ajax.php'),
            'debug' => get_option('web_push_debuger') ? true : false,
        );

        wp_localize_script('web-push', 'pn_vars', $data);
        wp_enqueue_script('web-push');

    }

    /**
     * Clean all IDs with error
     * @param  Object $answer
     * @return void
     */
    public function cleanClientIds($answer)
    {

        $allIds = $this->getClientIds();
        $resId = array();
        $errId = array();
        $err = array();
        $can = array();

        global $wpdb;
        $push_subscribers_table = $wpdb->prefix . 'push_subscribers';

        foreach ($answer->results as $index => $element) {
            if (isset($element->registration_id)) {
                $resId[] = $index;
            }
        }

        foreach ($answer->results as $index => $element) {
            if (isset($element->error)) {
                $errId[] = $index;
            }
        }

        for ($i = 0; $i < count($allIds); $i++) {
            if (isset($resId[$i]) && isset($allIds[$resId[$i]])) {
                array_push($can, $allIds[$resId[$i]]);
            }

        }

        for ($i = 0; $i < count($allIds); $i++) {
            if (isset($errId[$i]) && isset($allIds[$errId[$i]])) {
                array_push($err, $allIds[$errId[$i]]);
            }

        }

        if ($err != null) {
            for ($i = 0; $i < count($err); $i++) {
                $s = $wpdb->query($wpdb->prepare("DELETE FROM $push_subscribers_table WHERE gcm_regid=%s", $err[$i]));
            }
        }
        if ($can != null) {
            for ($i = 0; $i < count($can); $i++) {
                $r = $wpdb->query($wpdb->prepare("DELETE FROM $push_subscribers_table WHERE gcm_regid=%s", $can[$i]));
            }
        }
    }

    /**
     * Get user IDs
     * @return Array $clients Array of the client ids
     */
    private function getClientIds()
    {

        global $wpdb;

        $subscribers_table = $wpdb->prefix . 'push_subscribers';
        $clients = array();
        $sql = "SELECT gcm_regid FROM $subscribers_table";
        $res = $wpdb->get_results($sql);
        if ($res != false) {
            foreach ($res as $row) {
                array_push($clients, $row->gcm_regid);
            }
        }

        return $clients;
    }

    /**
     * Send the push message to GCM
     * @param  Array $data Notification data
     * @return void
     */
    public function sendGCM($data)
    {

        $this->putNotificationOnQueue($data);

        $apiKey = get_option('web_push_api_key');
        $url = 'https://android.googleapis.com/gcm/send';
        $id = $this->getClientIds();

        if (empty($id)) {
            return 'No subscribers on your site yet!';
        }

        if (count($id) >= 1000) {
            $newId = array_chunk($id, 1000);
            foreach ($newId as $inner_id) {
                $fields = array(
                    'registration_ids' => $inner_id,
                );
                $headers = array(
                    'Authorization: key=' . $apiKey,
                    'Content-Type: application/json');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $result = curl_exec($ch);
            }
        } else {
            $fields = array(
                'registration_ids' => $id,
            );
            $headers = array(
                'Authorization: key=' . $apiKey,
                'Content-Type: application/json');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
        }

        $answer = json_decode($result);
        curl_close($ch);

        if ($answer) {
            $this->cleanClientIds($answer);
        }

        return $result;
    }

    /**
     * Sent Notification when a new post is created
     * @return void
     */
    public function onPostStatusChange($new_status, $old_status, $post)
    {

        if (($old_status != $new_status && $new_status == 'publish') || ($old_status == 'future' && $new_status == 'publish')) {
            if (isset($_POST['chrome_push_confirm']) && !empty($_POST['chrome_push_confirm']) && !empty($post->post_title) && !empty($post->post_content)) {

                $selected_post_types = get_option('web_push_post_types');
                if (is_array($selected_post_types) && in_array($post->post_type, $selected_post_types)) {
                    $data = array(
                        'title' => $post->post_title,
                        'message' => mb_substr(wp_strip_all_tags(strip_shortcodes($post->post_content)), 0, 120),
                        'url' => get_permalink($post->ID),
                    );

                    $this->sendGCM($data);
                }
            }

        }

    }

    /**
     * Register Settings Admin Page
     * @return void
     */
    public function registerSettingsPage()
    {
        wp_enqueue_media();
        include_once CHROME_PUSH_PLUGIN_DIR . 'views/admin/web-push-settings.php';
    }

    /**
     * Register Notifications Statistics
     * @return void
     */
    public function notificationStatistics()
    {

        global $wpdb;
        $push_notifications_table = $wpdb->prefix . 'push_notifications';

        $pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;

        $limit = 10;
        $offset = ($pagenum - 1) * $limit;
        $total = $wpdb->get_var('SELECT COUNT(`id`) FROM ' . $push_notifications_table);
        $num_of_pages = ceil($total / $limit);

        $notifications = $wpdb->get_results('SELECT * FROM ' . $push_notifications_table . ' LIMIT ' . $offset . ', ' . $limit);

        $page_links = paginate_links(array(
            'base' => add_query_arg('pagenum', '%#%'),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $num_of_pages,
            'current' => $pagenum,
        ));

        include_once CHROME_PUSH_PLUGIN_DIR . 'views/admin/web-push-notifics-statistics.php';
    }

    /**
     * Register Notifications Statistics
     * @return void
     */
    public function subscriptionStatistics()
    {

        global $wpdb;
        $push_subscribers_table = $wpdb->prefix . 'push_subscribers';

        $pagenum = isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 1;

        $limit = 2;
        $offset = ($pagenum - 1) * $limit;
        $total = $wpdb->get_var('SELECT COUNT(`id`) FROM ' . $push_subscribers_table);
        $num_of_pages = ceil($total / $limit);

        $subscribers = $wpdb->get_results('SELECT * FROM ' . $push_subscribers_table . ' LIMIT ' . $offset . ', ' . $limit);

        $page_links = paginate_links(array(
            'base' => add_query_arg('pagenum', '%#%'),
            'format' => '',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'total' => $num_of_pages,
            'current' => $pagenum,
        ));

        include_once CHROME_PUSH_PLUGIN_DIR . 'views/admin/web-push-subs-statistics.php';
    }

    /**
     * Register New Push Admin Page
     * @return void
     */
    public function composePush()
    {
        include_once CHROME_PUSH_PLUGIN_DIR . 'views/admin/web-push-new.php';
    }

    /**
     * Registration hook
     * @return void
     */
    public static function installFunctions()
    {
        self::writeServiceWorker();
        self::dbInstall();
    }

    /**
     * Https requirement Notice
     * @return void
     */
    public function checkSiteConfigNotice()
    {
        ?>
	    <div class="error">
	        <p>Your Site URL should be set to HTTPS for Chrome Push Notifications Plugin to work.</p>
	    </div>
	    <?php
}

    /**
     * Check if Site url is set to HTTPS
     * @return void
     */
    public function checkSSL()
    {
        return strpos(get_option('siteurl'), 'https://') !== false;
    }

    /**
     * The function to add Metabox in posts
     */
    public function addMetaBox()
    {

        $screens = get_option('web_push_post_types');

        foreach ($screens as $screen) {

            add_meta_box(
                'chrome_push',
                'Chrome Push Notifications',
                array($this, 'metaboxCallback'),
                $screen,
                'side',
                'high'
            );
        }
    }

    /**
     * The meta box callback function
     * @return void
     */
    public function metaboxCallback()
    {
        echo '<input type="checkbox" id="chrome_push_confirm" name="chrome_push_confirm" value="yes" checked="checked"> Send push notification ';
    }

    /**
     * Fix http to https
     * @param  string $url
     * @return string
     */
    public static function fixHttpsURL($url)
    {
        if (stripos($url, 'http://') === 0) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }

    /**
     * Creating DB Tables
     * @return void
     */
    public static function dbInstall()
    {

        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $push_subscribers_table = $wpdb->prefix . 'push_subscribers';

        if ($wpdb->get_var("SHOW TABLES LIKE '$push_subscribers_table'") != $push_subscribers_table) {

            $sql = "CREATE TABLE " . $push_subscribers_table . " (
	        `id` int(11) NOT NULL AUTO_INCREMENT,
	        `gcm_regid` text,
	        `notifications` text,
	        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	        PRIMARY KEY (`id`)
	        );";

            dbDelta($sql);
        }

        $push_notifications_table = $wpdb->prefix . 'push_notifications';

        if ($wpdb->get_var("SHOW TABLES LIKE '$push_notifications_table'") != $push_notifications_table) {

            $sql = "CREATE TABLE " . $push_notifications_table . " (
	        `id` int(11) NOT NULL AUTO_INCREMENT,
	        `notification` text,
	        `hits` int(11) DEFAULT 0,
	        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	        PRIMARY KEY (`id`)
	        );";

            dbDelta($sql);
        }
    }

}