<?php
/*
Plugin Name: Customer Reviews Collector for WooCommerce
Plugin URI: https://wordpress.org/plugins/customer-reviews-collector-for-woocommerce/
Description: Collect reviews on Google, Facebook, Yelp, Trustindex and other platforms automatically, with the help of our system.
Tags: collect, Woocommerce reviews, customer reviews, Google reviews, review plugin
Version: 4.5
Author: Trustindex.io <support@trustindex.io>
Author URI: https://www.trustindex.io/
Contributors: trustindex
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: customer-reviews-collector-for-woocommerce
Domain Path: /languages
Donate link: https://www.trustindex.io/prices/
*/
/*
You should have received a copy of the GNU General Public License
along with Review widget addon for Divi. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/
/*
Copyright 2019 Trustindex Kft (email: support@trustindex.io)
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once plugin_dir_path( __FILE__ ) . 'trustindex-collector-plugin.class.php';
$trustindex_collector = new TrustindexCollectorPlugin(__FILE__, "4.5");
register_activation_hook(__FILE__, [ $trustindex_collector, 'activate' ]);
register_deactivation_hook(__FILE__, [ $trustindex_collector, 'deactivate' ]);
add_action('plugins_loaded', [ $trustindex_collector, 'load' ]);
add_action('woocommerce_order_status_changed', function($id) {
global $trustindex_collector;
global $wpdb;
$order = new WC_Order($id);
$triggerEvent = str_replace('wc-', '', get_option($trustindex_collector->get_option_name('trigger-event'), $trustindex_collector->get_default_settings()['trigger-event']));
$frequency = (int)get_option($trustindex_collector->get_option_name('frequency'), $trustindex_collector->get_default_settings()['frequency']);
$excludeEmails = get_option($trustindex_collector->get_option_name('exclude-emails'), $trustindex_collector->get_default_settings()['exclude-emails']);
if ($trustindex_collector->is_campaign_active() && $order->has_status($triggerEvent)) {
$orderData = $order->get_data();
$email = strtolower($orderData['billing']['email']);
$customerFullName = trim($orderData['billing']['first_name'] .' '. $orderData['billing']['last_name']);
foreach ($excludeEmails as $excludeEmail) {
if (trim($excludeEmail) && strpos($email, $excludeEmail) !== FALSE) {
return false;
}
}
if (!$customerFullName) {
if ($customer = new WC_Customer(get_post_meta($id, '_customer_user', true))) {
$customerFullName = $customer->get_display_name();
}
}
if ($email && !$trustindex_collector->is_email_unsubscribed($email) && !$trustindex_collector->isRequestExists($email, $id)) {
$tableName = $trustindex_collector->get_tablename('schedule_list');
$savedInvites = 0;
if ($frequency) {
$wpdb->get_results("SELECT id FROM `$tableName` WHERE `email` LIKE '$email' AND TIMESTAMPDIFF(DAY, created_at, NOW()) <= ". 30 * $frequency);
$savedInvites = $wpdb->num_rows;
}
if (!$savedInvites) {
$triggerDelay = (int)get_option($trustindex_collector->get_option_name('trigger-delay'), $trustindex_collector->get_default_settings()['trigger-delay']);
$timestamp = time() + ($triggerDelay * 86400);
if (!$triggerDelay) {
$trustindex_collector->sendMail($email, [ 'customer_full_name' => $customerFullName ], $trustindex_collector->register_schedule_sent($email, $id, null, $customerFullName));
}
else {
$date = date('Y-m-d H:i:s');
$wpdb->insert($tableName, [
'email' => $email,
'name' => $customerFullName,
'order_id' => $id,
'timestamp' => $timestamp,
'created_at' => $date
]);
$wpdb->update($tableName, [ 'hash' => md5($wpdb->insert_id . '-' . $date) ], [ 'id' => $wpdb->insert_id ]);
}
}
}
}
});
add_action('init', function() {
global $trustindex_collector;
if (!isset($trustindex_collector) || is_null($trustindex_collector)) {
if (!class_exists('TrustindexCollectorPlugin')) {
require_once plugin_dir_path( __FILE__ ) . 'trustindex-collector-plugin.class.php';
}
$trustindex_collector = new TrustindexCollectorPlugin(__FILE__, "4.5");
}
if (!wp_next_scheduled($trustindex_collector->get_schedule_cronname())) {
wp_schedule_event(time(), 'hourly', $trustindex_collector->get_schedule_cronname());
}
});
add_action($trustindex_collector->get_schedule_cronname(), function() {
global $trustindex_collector;
global $wpdb;
$schedules = $trustindex_collector->get_pending_schedules();
foreach ($schedules as $s) {
$customerFullName = "";
if ($order = new WC_Order($s->order_id)) {
$orderData = $order->get_data();
$customerFullName = trim($orderData['billing']['first_name'] .' '. $orderData['billing']['last_name']);
}
if (!$customerFullName) {
if ($customer = new WC_Customer(get_post_meta($s->order_id, '_customer_user', true))) {
$customerFullName = $customer->get_display_name();
}
}
if (!$s->hash) {
$s->hash = md5($s->id . '-' . $s->created_at);
$wpdb->update($trustindex_collector->get_tablename('schedule_list'), [ 'hash' => $s->hash ], [ 'id' => $s->id ]);
}
$trustindex_collector->sendMail($s->email, [ 'customer_full_name' => $customerFullName ], $s->hash);
$trustindex_collector->register_schedule_sent($s->email, $s->order_id, $s->id);
}
});
add_action('init', array($trustindex_collector, 'unsubscribe'));
add_action('admin_menu', [ $trustindex_collector, 'add_setting_menu' ], 10);
add_filter('plugin_action_links', [ $trustindex_collector, 'add_plugin_action_links' ], 10, 2);
add_filter('plugin_row_meta', [ $trustindex_collector, 'add_plugin_meta_links' ], 10, 2);
add_action('init', [ $trustindex_collector, 'output_buffer' ]);
add_action('admin_enqueue_scripts', [ $trustindex_collector, 'add_scripts' ]);
add_filter('script_loader_tag', function($tag, $handle) {
if (strpos($tag, 'trustindex.io/loader.js') !== false && strpos($tag, 'defer async') === false) {
$tag = str_replace(' src', ' defer async src', $tag );
}
return $tag;
}, 10, 2);
add_action('wp_ajax_'. $trustindex_collector->get_email_template_action(), function() {
global $trustindex_collector;
if (!isset($_POST['email-text']) || !isset($_POST['email-footer-text']) || !isset($_POST['platform-url'])) {
global $wp_query;
$wp_query->set_404();
status_header(404);
exit;
}
echo $trustindex_collector->getEmailHtml(stripslashes($_POST['email-text']), stripslashes($_POST['email-footer-text']), $_POST);
exit;
});
add_action('parse_request', function() {
global $trustindex_collector;
global $wpdb;
$rating = isset($_GET['rating']) ? (int)$_GET['rating'] : 3;
$reviewLink = $trustindex_collector->get_random_platform_url();
$tableName = $trustindex_collector->get_tablename('schedule_list');
if (isset($_GET[ $trustindex_collector->get_response_key() ])) {
$hash = sanitize_text_field($_GET[ $trustindex_collector->get_response_key() ]);
$res = $wpdb->get_results('SELECT * FROM `'. $tableName .'` WHERE hash LIKE "'. $hash .'" LIMIT 1', ARRAY_A);
$mode = sanitize_text_field(isset($_GET['mode']) ? $_GET['mode'] : 'clicked');
if (count($res) !== 1) {
global $wp_query;
$wp_query->set_404();
status_header(404);
exit;
}
$schedule = $res[0];
$key = $mode . '_at';
if (in_array($key, [ 'opened_at', 'clicked_at' ]) && empty($schedule[ $key ])) {
$data = [];
$data[ $key ] = date('Y-m-d H:i:s');
if ($key === 'clicked_at') {
$data['opened_at'] = date('Y-m-d H:i:s');
}
$wpdb->update($tableName, $data, [ 'id' => $schedule['id'] ]);
}
if ($mode === 'clicked') {
if ($rating <= 3) {
header('Location: '. $trustindex_collector->get_feedback_url($hash, $rating));
}
else {
header('Location: '. $reviewLink);
}
}
exit;
}
else if (isset($_GET[ $trustindex_collector->get_feedback_key() ])) {
if ($hash = sanitize_text_field($_GET[ $trustindex_collector->get_feedback_key() ])) {
$res = $wpdb->get_results('SELECT * FROM `'. $tableName .'` WHERE hash LIKE "'. $hash .'" LIMIT 1', ARRAY_A);
if (count($res) != 1) {
global $wp_query;
$wp_query->set_404();
status_header(404);
exit;
}
$schedule = $res[0];
$isTest = false;
if (isset($_POST['text'])) {
$text = wp_kses_post(stripslashes($_POST['text']));
$wpdb->update($tableName, [
'feedback' => $text,
'feedback_at' => date('Y-m-d H:i:s')
], [ 'id' => $schedule['id'] ]);
$message = "
Hi,<br /><br />
Instead of a negative rating, this message was written by one of your customers.<br />
<strong>Please respond to your customer regarding the problem as soon as possible.</strong><br /><br />
Name: <strong>". $schedule['name'] ."</strong><br />
E-mail: <strong>". $schedule['email'] ."</strong><br />
Message: <strong>$text</strong><br />";
if ($schedule['order_id']) {
$orderUrl = admin_url('post.php?post='. $schedule['order_id'] .'&action=edit');
$message .= 'Order ID: <a href="'. $orderUrl .'" target="_blank">'. $schedule['order_id'] .'</a><br />';
}
$message .= "<br />
Thank you for your help.<br /><br />
Best regards,<br />
Trustindex Team";
$wcMailer = WC()->mailer();
$wcMailer->send(get_option('admin_email'), 'Trustindex: Respond to Your Customer', $trustindex_collector->getEmailHtml($message));
exit;
}
}
else {
$schedule = [
'name' => 'John Smith',
'email' => 'example@gmail.com',
'feedback' => ''
];
$isTest = true;
}
$locale = isset($_GET['lang']) ? $_GET['lang'] : get_option($trustindex_collector->get_option_name('support-language'), $trustindex_collector->get_default_settings()['support-language']);
if ($locale === 'en') {
if (function_exists('switch_to_locale')) {
switch_to_locale('en_US');
}
}
else {
unload_textdomain($trustindex_collector->get_plugin_slug(), true);
$moFile = $trustindex_collector->get_plugin_dir() . 'languages' . DIRECTORY_SEPARATOR . $trustindex_collector->get_plugin_slug() . '-' . $locale . '.mo';
load_textdomain($trustindex_collector->get_plugin_slug(), $moFile);
}
include $trustindex_collector->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'feedback.php';
exit;
}
});
add_action('admin_notices', function() {
if (class_exists('Woocommerce')) {
return;
}
echo '<div class="notice notice-error is-dismissible"><p>'. sprintf(__('WooCommerce is not activated, please activate it to use <strong>%s</strong>!', 'customer-reviews-collector-for-woocommerce'), 'Customer Reviews Collector for WooCommerce') .'</p></div>';
});
?>