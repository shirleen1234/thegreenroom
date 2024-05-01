<?php
class TrustindexCollectorPlugin
{
private $pluginFilePath;
private $version;
public function __construct($pluginFilePath, $version)
{
$this->pluginFilePath = $pluginFilePath;
$this->version = $version;
}


public function get_plugin_dir()
{
return plugin_dir_path($this->pluginFilePath);
}
public function get_plugin_file_url($file, $addVersioning = true)
{
$url = plugins_url($file, $this->pluginFilePath);
if ($addVersioning) {
$appendMark = strpos($url, '?') === FALSE ? '?' : '&';
$url .= $appendMark . 'ver=' . $this->version;
}
return $url;
}
public function get_plugin_slug()
{
return 'customer-reviews-collector-for-woocommerce';
}


public function activate()
{
include $this->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'activate.php';
update_option($this->get_option_name('update-version-check'), $this->version);
update_option($this->get_option_name('activation-redirect'), 1, false);
}
public function load()
{
$this->loadI18N();
include $this->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'update.php';
if (get_option($this->get_option_name('activation-redirect'))) {
delete_option($this->get_option_name('activation-redirect'));
wp_redirect(admin_url('admin.php?page=' . $this->get_plugin_slug() . '/admin.php'));
exit;
}
}
public function deactivate()
{
update_option($this->get_option_name('active'), '0');
}
public function uninstall()
{
include $this->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'uninstall.php';
if ($timestamp = wp_next_scheduled($this->get_schedule_cronname())) {
wp_unschedule_event($timestamp, $this->get_schedule_cronname());
}
$file = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $this->get_email_logo_filename();
if (file_exists($file)) {
unlink($file);
}
}
public function output_buffer()
{
ob_start();
}
public function get_plugin_current_version()
{
add_action('http_api_curl', function($handle) {
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
}, 10);
$response = wp_remote_get('https://api.wordpress.org/plugins/info/1.2/?action=plugin_information&request[slug]='. $this->get_plugin_slug());
$json = json_decode($response['body'], true);
if (!$json || !isset($json['version'])) {
return false;
}
return $json['version'];
}
public function loadI18N()
{
load_plugin_textdomain($this->get_plugin_slug(), false, $this->get_plugin_slug() . DIRECTORY_SEPARATOR . 'languages');
}


public function get_tablename($name = "")
{
global $wpdb;
return $wpdb->prefix .'trustindex_collector_' . $name;
}
public function is_table_exists($name = "")
{
global $wpdb;
$tableName = $this->get_tablename($name);
return ($wpdb->get_var("SHOW TABLES LIKE '$tableName'") == $tableName);
}
public static function getPluginTabs()
{
return [
__('Dashboard', 'customer-reviews-collector-for-woocommerce') => 'dashboard',
__('Requests', 'customer-reviews-collector-for-woocommerce') => 'requests',
__('Settings', 'customer-reviews-collector-for-woocommerce') => 'settings',
__('Display reviews', 'customer-reviews-collector-for-woocommerce') => 'display-reviews',
__('Unsubscribes', 'customer-reviews-collector-for-woocommerce') => 'unsubscribes',
__('Feature request', 'customer-reviews-collector-for-woocommerce') => 'feature-request',
__('Troubleshooting', 'customer-reviews-collector-for-woocommerce') => 'troubleshooting'
];
}


public function add_setting_menu()
{
$permission = 'edit_pages';
add_submenu_page(
'woocommerce',
'Trustindex.io',
__('Customer Reviews Collector', 'customer-reviews-collector-for-woocommerce') . ' <span class="awaiting-mod">'. __('New', 'customer-reviews-collector-for-woocommerce') .'</span>',
$permission,
$this->get_plugin_slug() . '/admin.php'
);
/*
$title = __('Customer Reviews Collector', 'customer-reviews-collector-for-woocommerce');
$menuSlug = 'trustindex-collector';//$this->get_plugin_slug() . '/admin.php';
add_menu_page(
$title,
$title,
$permission,
$menuSlug,
null,
$this->get_plugin_file_url('assets/img/trustindex-sign-logo.png')
);
foreach(self::getPluginTabs() as $name => $tab)
{
add_submenu_page(
$menuSlug,
$name,
$name,
$permission,
admin_url('admin.php?page='. $this->get_plugin_slug() .'/admin.php&tab='. $tab)
);
}
*/
}
public function add_plugin_action_links($links, $file)
{
if (basename($file) === $this->get_plugin_slug() . '.php') {
if (!class_exists('Woocommerce')) {
return [ '<span style="color: red; font-weight: bold">'. __('Activate WooCommerce first!', 'customer-reviews-collector-for-woocommerce') .'</span>' ];
}
$campaignLink = '<a style="background-color: #1a976a; color: white; font-weight: bold; padding: 3px 8px; border-radius: 4px; position: relative; top: 1px" ';
if ($this->is_campaign_active() || (int)get_option($this->get_option_name('settings-state'), 1) >= 4) {
$campaignLink .= 'href="' . admin_url('admin.php?page=' . $this->get_plugin_slug() . '/admin.php') . '">';
if ($this->is_campaign_active()) {
$campaignLink .= __('Dashboard', 'customer-reviews-collector-for-woocommerce');
}
else {
$campaignLink .= __('Start e-mail campaign', 'customer-reviews-collector-for-woocommerce');
}
$settingsLink = '<a href="' . admin_url('admin.php?page=' . $this->get_plugin_slug() . '/admin.php&tab=settings') . '">' . __('Settings', 'customer-reviews-collector-for-woocommerce') . '</a>';
array_unshift($links, $campaignLink . '</a>', $settingsLink);
}
else {
$campaignLink .= 'href="' . admin_url('admin.php?page=' . $this->get_plugin_slug() . '/admin.php&tab=settings') . '">' . __('Set up invitations', 'customer-reviews-collector-for-woocommerce');
array_unshift($links, $campaignLink . '</a>');
}
}
return $links;
}
public function add_plugin_meta_links($meta, $file)
{
if (basename($file) === $this->get_plugin_slug() . '.php') {
$meta[] = '<a href="'. admin_url('admin.php?page=' . $this->get_plugin_slug() . '/admin.php&tab=display-reviews') .'">'. __('Display reviews', 'customer-reviews-collector-for-woocommerce') . ' →</a>';
$meta[] = '<a href="http://wordpress.org/support/view/plugin-reviews/'. $this->get_plugin_slug() .'" target="_blank" rel="noopener noreferrer">'. __('Rate our plugin', 'customer-reviews-collector-for-woocommerce') . ' <span style="color: #F6BB07; font-size: 1.2em; line-height: 1; position: relative; top: 0.05em;">★★★★★</span></a>';
}
return $meta;
}
public function add_scripts($hook)
{
$tmp = explode('/', $hook);
$currentSlug = array_shift($tmp);
if ($this->get_plugin_slug() === $currentSlug) {
if (file_exists($this->get_plugin_dir() . 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'admin.css')) {
wp_enqueue_style('trustindex-collector-admin', $this->get_plugin_file_url('assets/css/admin.css'));
}
if (file_exists($this->get_plugin_dir() . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'admin.js')) {
wp_enqueue_script('trustindex-collector-admin', $this->get_plugin_file_url('assets/js/admin.js'));
}
if (file_exists($this->get_plugin_dir() . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'bootstrap.bundle.min.js')) {
wp_enqueue_script('trustindex-collector-boostrap', $this->get_plugin_file_url('assets/js/bootstrap.bundle.min.js'));
}
if (file_exists($this->get_plugin_dir() . 'assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'Chart.min.js')) {
wp_enqueue_script('trustindex-collector-chart', $this->get_plugin_file_url('assets/js/Chart.min.js'));
}
}
}


public function get_option_name($optName)
{
if (!in_array($optName, $this->get_option_names())) {
echo "Option not registered in plugin (TrustindexCollector class)";
}
return 'trustindex-collector-' . $optName;
}
public function get_option_names()
{
return [
'active',
'version',
'update-version-check',
'settings-state',
'campaign-active',
'platform-url',
'trigger-delay',
'trigger-event',
'frequency',
'email-sender',
'email-sender-email',
'email-subject',
'email-text',
'email-footer-text',
'rate-us-feedback',
'support-language',
'exclude-emails',
'activation-redirect',
];
}
public function get_default_settings()
{
$mailer = WC()->mailer();;
$domain = get_bloginfo('name');
return [
'platform-url' => [],
'trigger-delay' => 7,
'trigger-event' => 'wc-completed',
'frequency' => 0,
'exclude-emails' => [],
'email-sender' => $mailer->get_from_name(),
'email-sender-email' => $mailer->get_from_address(),
'email-subject' => 'Your opinion matters to '. $domain,
'email-text' => '<p>Dear {{customer_full_name}},</p><p>Thank you for choosing '. $domain .'.</p><p>Our customers\' opinion is important to us, as this way we can increase their satisfaction!</p><p>Please share your experiences with us!</p><p><strong>Click the stars to review us</strong></p><p>{{stars}}</p><p>It\'s only a minute for you, but a huge help for us.</p><p>Thank you in advance,<br />'. $domain .' team</p>',
'email-footer-text' => "You received this email because you made a purchase from $domain website.\nIf you don't want to receive this mail in the future, you can <a>unsubscribe here</a>.",
'support-language' => 'en'
];
}
public function save_option_from_request($name, $type = 'field')
{
$value = "";
if (isset($_REQUEST[ $name ])) {
if ($type === 'array') {
$value = $_REQUEST[ $name ];
}
else if ($type === 'text') {
$value = trim(wp_kses_post(stripslashes($_REQUEST[ $name ])));
}
else if ($type === 'email-array') {
$value = strtolower(preg_replace('/\s/', '', sanitize_text_field($_REQUEST[ $name ])));
$value = preg_replace('/[,;|]+/', ',', $value);
$value = explode(',', $value);
}
else {
$value = trim(sanitize_text_field(wp_unslash($_REQUEST[ $name ])));
}
}
update_option($this->get_option_name($name), $value, false);
}


public function get_response_key()
{
return 'trustindex-collector-response';
}
public function get_response_url($hash = null, $mode = 'clicked')
{
$url = site_url() . '?' . $this->get_response_key();
if ($hash) {
$url .= '=' . $hash;
}
if ($mode) {
$url .= '&mode=' . $mode;
}
return $url;
}
public function get_feedback_key()
{
return 'trustindex-collector-support-feedback';
}
public function get_feedback_url($hash = null, $rating = null)
{
$url = site_url() . '?' . $this->get_feedback_key();
if ($hash) {
$url .= '=' . $hash;
}
if ($rating) {
$url .= '&rating=' . $rating;
}
return $url;
}
public function get_email_template_action()
{
return 'trustindex_collector_email_template';
}
public function get_email_template_url()
{
return admin_url('admin-ajax.php') . '?action='. $this->get_email_template_action();
}
public function get_email_logo_filename()
{
return 'ti-collector-email-logo.png';
}
public function uploadLogoImage($image, $fileName = null)
{
if (!$fileName) {
$fileName = $this->get_email_logo_filename();
}
$wpUploadDir = wp_upload_dir();
$decoded = base64_decode(str_replace(' ', '+', str_replace('data:image/png;base64,', '', $image)));
if (file_put_contents($wpUploadDir['basedir'] . DIRECTORY_SEPARATOR . $fileName, $decoded)) {
return $wpUploadDir['baseurl'] . '/' . $fileName;
}
return false;
}
public function getEmailHtml($tiEmailContent = null, $tiEmailFooterContent = null, $settings = [], $hash = null)
{
if (!isset($settings['customer_full_name'])) {
$settings['customer_full_name'] = '{{customer_full_name}}';
}
ob_start();
$linkBad = $this->get_feedback_url(null, '%star%');
$linkGood = isset($settings['platform-url']) ? $settings['platform-url'][0]['url'] : "";
if ($hash) {
$linkBad = $this->get_response_url($hash) . '&rating=%star%';
$linkGood = $linkBad;
$tiEmailContent .= '<img src="'. $this->get_response_url($hash, 'opened') .'" alt="" />';
}
$starsContent = "";
for ($i = 5; $i >= 1; $i--) {
$starsContent .= '<tr><td style="padding: 4px 0px;"><a href="'. str_replace('%star%', $i, $i >= 4 ? $linkGood : $linkBad) .'"><img width="220" src="https://cdn.trustindex.io/assets/img/email-'. $i .'-star-btn.png" alt=""></a></td></tr>';
}
$tiEmailContent = str_replace([
'{{stars}}',
'{{customer_full_name}}'
], [
'<table cellpadding="0" cellspacing="0" style="table-layout: fixed"><tbody>'. $starsContent .'</tbody></table>',
$settings['customer_full_name']
], $tiEmailContent);
if ($tiEmailFooterContent) {
$tiEmailFooterContent = nl2br($tiEmailFooterContent);
}
$tiEmailFooterContent = str_replace('<a>', '<a href="{{unsubscribe_url}}" target="_blank" style="font-size: inherit; font-family: inherit; color: inherit; text-decoration: underline">', $tiEmailFooterContent);
if (isset($settings['logo-image']) && $settings['logo-image']) {
$logoImage = $settings['logo-image'];
}
else {
$wpUploadDir = wp_upload_dir();
$file = $wpUploadDir['basedir'] . DIRECTORY_SEPARATOR . $this->get_email_logo_filename();
if (file_exists($file)) {
$logoImage = $wpUploadDir['baseurl'] . '/' . $this->get_email_logo_filename() . '?' . filemtime($file);
}
}
include $this->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'email.php';
return ob_get_clean();
}
public function sendMail($email, $settings = [], $hash = null)
{
foreach ($this->get_default_settings() as $name => $default) {
if (!isset($settings[ $name ])) {
$settings[ $name ] = get_option($this->get_option_name($name), $default);
}
}
$oldSenderEmail = $this->get_default_settings()['email-sender-email'];
$oldSenderName = $this->get_default_settings()['email-sender'];
if ($settings['email-sender-email'] && $settings['email-sender-email'] !== $oldSenderEmail) {
update_option('woocommerce_email_from_address', $settings['email-sender-email'], false);
}
if ($settings['email-sender'] && $settings['email-sender'] !== $oldSenderName) {
update_option('woocommerce_email_from_name', $settings['email-sender'], false);
}
$wcMailer = WC()->mailer();
$html = $this->getEmailHtml($settings['email-text'], $settings['email-footer-text'], $settings, $hash);
$html = str_replace('{{unsubscribe_url}}', get_site_url() .'?ti-collector-unsubscribe='. urlencode($email) .'&q='. md5($email), $html);
$wcMailer->send($email, $settings['email-subject'], $html);
if ($settings['email-sender-email'] && $settings['email-sender-email'] !== $oldSenderEmail) {
update_option('woocommerce_email_from_address', $oldSenderEmail, false);
}
if ($settings['email-sender'] && $settings['email-sender'] !== $oldSenderName) {
update_option('woocommerce_email_from_name', $oldSenderName, false);
}
}


public function is_campaign_active()
{
return get_option($this->get_option_name('active'), 0) && get_option($this->get_option_name('campaign-active'), 0);
}
public function get_random_platform_url()
{
$url = "";
$urls = get_option($this->get_option_name('platform-url'), []);
if ($urls) {
if (!is_array($urls)) {
$urls = [[
'url' => $urls,
'percent' => 100
]];
}
$url = $urls[0];
usort($urls, function($a, $b) {
$ap = (int)$a['percent'];
$bp = (int)$b['percent'];
return $ap < $bp ? -1 : ($ap > $bp ? 1 : 0);
});
/*
[
[ ..., 'percent' => 20 ], --> between 1 and 20
[ ..., 'percent' => 30 ], --> between 21 and 50
[ ..., 'percent' => 50 ], --> between 51 and 100
]
*/
$percentMin = 1;
$random = random_int(1, 100);
foreach ($urls as $u) {
$percentMax = $percentMin + (int)$u['percent'] - 1;
if ($random >= $percentMin && $random <= $percentMax) {
$url = $u['url'];
break;
}
$percentMin += (int)$u['percent'];
}
}
return $url;
}
public function get_schedule_cronname()
{
return 'trustindex_collector_cron';
}
public function register_schedule_sent($email, $orderId, $scheduleId = null, $name = "")
{
global $wpdb;
$tableName = $this->get_tablename('schedule_list');
$hash = null;
if ($scheduleId) {
$wpdb->query("UPDATE `$tableName` SET sent = 1 WHERE id = '$scheduleId'");
}
else {
$date = date('Y-m-d H:i:s');
$wpdb->insert($tableName, [
'email' => $email,
'name' => $name,
'order_id' => $orderId,
'timestamp' => time(),
'sent' => 1,
'created_at' => $date
]);
$hash = md5($wpdb->insert_id . '-' . $date);
$wpdb->update($tableName, [ 'hash' => $hash ], [ 'id' => $wpdb->insert_id ]);
}
return $hash;
}
public function get_pending_schedules()
{
global $wpdb;
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
return $wpdb->get_results('SELECT id, email, order_id, hash, created_at FROM `'. $this->get_tablename('schedule_list') .'` WHERE `timestamp` <= '. time() .' AND sent = 0');
}
public function get_schedules($page = 1, $query = "")
{
global $wpdb;
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
$limit = 10;
$sql = "SELECT * FROM `". $this->get_tablename('schedule_list') ."` WHERE email LIKE '%$query%' OR name like '%$query%' ORDER BY `timestamp`";
$total = $wpdb->get_results(str_replace('*', 'COUNT(id) as num', $sql))[0]->num;
$sql .= ' LIMIT ' . (($page - 1) * $limit) . ', ' . $limit;
return (object) [
'total' => $total,
'maxNumPages' => ceil($total / $limit),
'schedules' => $wpdb->get_results($sql)
];
}
public function get_schedule($id)
{
global $wpdb;
$res = $wpdb->get_results('SELECT * FROM `'. $this->get_tablename('schedule_list') .'` WHERE id = '. (int)$id .' LIMIT 1');
if (isset($res[0])) {
return $res[0];
}
return null;
}
public function isRequestExists($email, $orderId)
{
global $wpdb;
$email = sanitize_email($email);
$id = (int)$orderId;
$res = $wpdb->get_results('SELECT id FROM `'. $this->get_tablename('schedule_list') .'` WHERE order_id = "'. $orderId .'" AND email LIKE "'. $email .'" LIMIT 1');
return count($res) === 1;
}


public function is_email_unsubscribed($email)
{
global $wpdb;
$email = sanitize_email($email);
$res = $wpdb->get_results('SELECT id FROM `'. $this->get_tablename('unsubscribes') .'` WHERE email LIKE "'. $email .'" LIMIT 1');
return count($res) === 1;
}
public function unsubscribe()
{
global $wpdb;
if (isset($_GET['ti-collector-unsubscribe'])) {
$email = strtolower(sanitize_email($_GET['ti-collector-unsubscribe']));
$md5 = sanitize_text_field($_GET['q']);
if (!$email || $md5 !== md5($email)) {
header('HTTP/1.0 404 Not Found');
exit;
}
if ($this->is_email_unsubscribed($email)) {
echo 'Email already unsubscribed!';
exit;
}
$wpdb->insert($this->get_tablename('unsubscribes'), [
'email' => $email,
'created_at' => date('Y-m-d H:i:s')
]);
$wpdb->delete($this->get_tablename('schedule_list'), [ 'email' => $email, 'sent' => 0 ]);
echo 'Email unsubscribed successfully!';
exit;
}
}
public function get_unsubscribes($page = 1, $query = "")
{
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
global $wpdb;
$limit = 10;
$sql = "SELECT * FROM `". $this->get_tablename('unsubscribes') ."` WHERE email LIKE '%$query%' ORDER BY `created_at` DESC";
$total = $wpdb->get_results(str_replace('*', 'COUNT(id) as num', $sql))[0]->num;
$sql .= ' LIMIT ' . (($page - 1) * $limit) . ', ' . $limit;
return (object) [
'total' => $total,
'maxNumPages' => ceil($total / $limit),
'unsubscribes' => $wpdb->get_results($sql)
];
}
}
?>