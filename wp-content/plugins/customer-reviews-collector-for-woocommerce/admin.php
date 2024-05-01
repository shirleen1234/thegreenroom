<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
$pluginManager = 'TrustindexCollectorPlugin';
$pluginManagerInstance = $trustindex_collector;
$pluginNameForEmails = 'Customer Reviews Collector for WooCommerce';
if (!current_user_can('edit_pages')) {
die('The account you\'re logged in to doesn\'t have permission to access this page.');
}
if (!class_exists('Woocommerce')) {
die(__('Activate WooCommerce first!', 'customer-reviews-collector-for-woocommerce'));
}
$tabs = $pluginManager::getPluginTabs();
$selectedTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : null;
if (!in_array($selectedTab, array_values($tabs))) {
$selectedTab = 'dashboard';
}
$_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : null;
$_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $selectedTab;
$tiCommand = isset($_REQUEST['command']) ? sanitize_text_field($_REQUEST['command']) : null;
$settingsState = (int)get_option($pluginManagerInstance->get_option_name('settings-state'), 1);
?>
<div id="trustindex-collector-admin">
<div id="ti-assets-error" class="alert alert-warning alert-hidden"><?php echo __('For some reason, the <strong>CSS</strong> file required to run the plugin was not loaded.<br />One of your plugins is probably causing the problem.', 'customer-reviews-collector-for-woocommerce'); ?></div>
<script type="text/javascript">
window.onload = function() {
let notLoaded = [];
let loadedCount = 0;
let jsFiles = [
{
url: '<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('assets/js/admin.js')); ?>',
id: 'common'
},
{
url: '<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('assets/js/bootstrap.bundle.min.js')); ?>',
id: 'bootstrap'
},
{
url: '<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('assets/js/Chart.min.js')); ?>',
id: 'chart'
}
];
let addElement = function(type, url, callback) {
let element = document.createElement(type);
if (type === 'script') {
element.type = 'text/javascript';
element.src = url;
}
else {
element.type = 'text/css';
element.rel = 'stylesheet';
element.href = url;
element.id = 'trustindex-collector-admin-css';
}
document.head.appendChild(element);
element.addEventListener('load', function() { callback(true); });
element.addEventListener('error', function() { callback(false); });
};
let isCSSExists = function() {
let link = document.getElementById('trustindex-collector-admin-css');
return link && Boolean(link.sheet);
};
let isJSExists = function(id) {
return typeof Trustindex_Collector_JS_loaded != 'undefined' && typeof Trustindex_Collector_JS_loaded[ id ] != 'undefined';
};
let process = function() {
if (loadedCount < jsFiles.length + 1) {
return false;
}
if (notLoaded.length) {
document.getElementById('trustindex-collector-admin').querySelector('.ti-wrapper').remove();
let warningBox = document.getElementById('ti-assets-error');
if (warningBox) {
warningBox.style.display = 'block';
warningBox.querySelector('strong').innerHTML = notLoaded.join(', ');
}
}
};
if (!isCSSExists()) {
addElement('link', '<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('assets/css/admin.css')); ?>', function(success) {
loadedCount++;
if (!success) {
notLoaded.push('CSS');
}
process();
});
}
else {
loadedCount++;
}
jsFiles.forEach(function(js) {
if (!isJSExists(js.id)) {
addElement('script', js.url, function(success) {
loadedCount++;
if (!success) {
if (notLoaded.indexOf('JS') === -1) {
notLoaded.push('JS');
}
}
process();
});
}
else {
loadedCount++;
}
});
};
</script>
<div class="ti-wrapper">
<header class="plugin-header">
<div class="container">
<div class="row align-items-center">
<div class="plugin-title col-12 col-sm">
<h1><?php echo __('Customer Reviews Collector for WooCommerce', 'customer-reviews-collector-for-woocommerce'); ?></h1>
</div>
<div class="logo col-12 col-sm-auto ml-auto">
<img src="<?php echo esc_url($pluginManagerInstance->get_plugin_file_url('assets/img/trustindex.svg')); ?>" alt="">
</div>
</div>
</div>
</header>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
<div class="container">
<ul class="navbar-nav col-12 col-sm-auto">
<?php foreach ($tabs as $tabName => $tab): ?>
<?php if ($tab === 'feature-request'): ?>
</ul>
<ul class="navbar-nav col-12 col-sm-auto ml-auto">
<?php endif; ?>
<li class="nav-item">
<a
href="<?php echo esc_url(admin_url('admin.php?page='. $pluginManagerInstance->get_plugin_slug() .'/admin.php&tab='. $tab)); ?>"
class="nav-link <?php if ($selectedTab === $tab): ?>active<?php endif; ?>"
 <?php if ($selectedTab === $tab): ?>aria-current="page"<?php endif; ?>
>
<?php echo esc_html($tabName); ?>
</a>
</li>
<?php endforeach; ?>
</ul>
</div>
</nav>
<div class="container-lg">
<main class="content">
<?php include($pluginManagerInstance->get_plugin_dir() . 'tabs' . DIRECTORY_SEPARATOR . $selectedTab . '.php'); ?>
</main>
</div>
</div>
</div>