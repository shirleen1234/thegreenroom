<?php
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
global $wpdb;
$updateChecked = (float)get_option($this->get_option_name('update-version-check'), 0);
$currentVersion = (float)$this->version;
if ($updateChecked < $currentVersion) {

if ($currentVersion >= 2) {
$tableName = $this->get_tablename('schedule_list');
if (count($wpdb->get_results("SHOW COLUMNS FROM `$tableName` LIKE 'hash'")) === 0) {
$wpdb->query("
ALTER TABLE `$tableName`
ADD `hash` VARCHAR(50) NOT NULL AFTER `created_at`,
ADD `opened_at` DATETIME NULL AFTER `hash`,
ADD `clicked_at` DATETIME NULL AFTER `opened_at`,
ADD `feedback` TEXT NOT NULL AFTER `clicked_at`,
ADD `feedback_at` DATETIME NULL AFTER `feedback`
");
}
$url = get_option($this->get_option_name('platform-url'), null);
if ($url && !is_array($url)) {
$urls = [ [ 'url' => $url, 'percent' => 100 ] ];
update_option($this->get_option_name('platform-url'), $urls);
}
if ($text = get_option($this->get_option_name('email-text'), null)) {
$text = str_replace('<p>{{button}}</p>', '<p><strong>Click the stars to review us</strong></p><p>{{stars}}</p>', $text);
update_option($this->get_option_name('email-text'), $text);
}
if ($isActive = get_option($this->get_option_name('campaign-active'), 1)) {
update_option($this->get_option_name('campaign-active'), 1);
}
}

if ($currentVersion >= 3.6) {
$tableName = $this->get_tablename('schedule_list');
$res = $wpdb->get_results("SHOW FULL COLUMNS FROM `$tableName` WHERE Field IN ('name', 'feedback')");
if ($res[0]->Collation !== 'utf8mb4_unicode_520_ci') {
$wpdb->query("ALTER TABLE `$tableName` CHANGE `name` `name` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL");
}
if ($res[1]->Collation !== 'utf8mb4_unicode_520_ci') {
$wpdb->query("ALTER TABLE `$tableName` CHANGE `feedback` `feedback` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL");
}
}

if ($updateChecked < 4.2) {
$urls = get_option($this->get_option_name('platform-url'), []);
$changed = false;
foreach ($urls as $i => $url) {
if (strpos($url['url'], 'writereview?placeid') === FALSE) {
continue;
}
$pageId = preg_replace('/.+placeid=([^&\/]+)/', '$1', $url['url']);
$response = wp_remote_get('https://admin.trustindex.io/api/getPageDetails?platform=google&page_id='. $pageId, [ 'timeout' => 30 ]);
$json = json_decode($response['body'], true);
if (is_wp_error($response) || !$json || !$json['success']) {
continue;
}
$newWriteUrl = $json['result']['write_review_url'];
$urls[ $i ]['url'] = $newWriteUrl;
$changed = true;
}
update_option($this->get_option_name('platform-url'), $urls);
}

if ($updateChecked < 4.4) {
$urls = get_option($this->get_option_name('platform-url'), []);
$changed = false;
foreach ($urls as $i => $url) {
if (strpos($url['url'], 'google.com/maps/place') === FALSE) {
continue;
}
$response = wp_remote_get('https://admin.trustindex.io/api/findPlaceId?url='. urlencode($url['url']), [ 'timeout' => 30 ]);
$json = json_decode($response['body'], true);
if (is_wp_error($response) || !$json || !$json['success']) {
continue;
}
$newWriteUrl = 'https://admin.trustindex.io/api/googleWriteReview?place-id=' . $json['result']['page_id'];
$urls[ $i ]['url'] = $newWriteUrl;
$changed = true;
}
update_option($this->get_option_name('platform-url'), $urls);
}
update_option($this->get_option_name('update-version-check'), $currentVersion);
update_option($this->get_option_name('version'), $currentVersion);
}
?>