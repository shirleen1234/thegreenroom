<?php
defined('ABSPATH') or die('No script kiddies please!');
$autoUpdates = get_option('auto_update_plugins', []);
$pluginSlug = "customer-reviews-collector-for-woocommerce/customer-reviews-collector-for-woocommerce.php";
if ($tiCommand === 'auto-update') {
check_admin_referer('ti-auto-update');
if (!in_array($pluginSlug, $autoUpdates)) {
$autoUpdates []= $pluginSlug;
update_option('auto_update_plugins', $autoUpdates, false);
}
header('Location: admin.php?page='. $_page .'&tab=troubleshooting');
exit;
}
else if ($tiCommand === 're-create') {
check_admin_referer('ti-recreate');
$updateChecked = (float)get_option($pluginManagerInstance->get_option_name('update-version-check'), 0);
$pluginManagerInstance->uninstall();
if ($updateChecked) {
update_option($pluginManagerInstance->get_option_name('update-version-check'), $updateChecked);
}
$pluginManagerInstance->activate();
header('Location: admin.php?page='. $_page);
exit;
}
$yesIcon = '<span class="dashicons dashicons-yes-alt"></span>';
$noIcon = '<span class="dashicons dashicons-dismiss"></span>';
$pluginUpdated = ($pluginManagerInstance->get_plugin_current_version() <= "4.5");
?>
<div class="plugin-head"><?php echo __('Troubleshooting', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="plugin-body">
<div class="card">
<div class="card-body">
<p class="size-16"><strong><?php echo __('If you have any problem, you should try these steps:', 'customer-reviews-collector-for-woocommerce'); ?></strong></p>
<ul class="ti-troubleshooting-checklist">
<li>
<?php echo __('Trustindex plugin', 'customer-reviews-collector-for-woocommerce'); ?>
<ul>
<li>
<?php echo __('Use the latest version:', 'customer-reviews-collector-for-woocommerce') .' '. ($pluginUpdated ? $yesIcon : $noIcon); ?>
<?php if (!$pluginUpdated): ?>
<a href="/wp-admin/plugins.php"><?php echo __('Update', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php endif; ?>
</li>
<li>
<?php echo __('Use automatic plugin update:', 'customer-reviews-collector-for-woocommerce') .' '. (in_array($pluginSlug, $autoUpdates) ? $yesIcon : $noIcon); ?>
<?php if (!in_array($pluginSlug, $autoUpdates)): ?>
<a href="<?php echo wp_nonce_url('?page='. $_page .'&tab=troubleshooting&command=auto-update', 'ti-auto-update'); ?>"><?php echo __('Enable', 'customer-reviews-collector-for-woocommerce'); ?></a>
<div class="alert alert-sm alert-warning"><?php echo __('You should enable it, to get new features and fixes automatically, right after they published!', 'customer-reviews-collector-for-woocommerce'); ?></div>
<?php endif; ?>
</li>
</ul>
</li>
<li>
<?php
$pluginUrl = 'https://wordpress.org/support/plugin/' . $pluginManagerInstance->get_plugin_slug();
$screenshotUrl = 'https://snipboard.io';
$screencastUrl = 'https://streamable.com/upload-video';
$pastebinUrl = 'https://pastebin.com';
echo sprintf(__('If the problem/question still exists, please create an issue here: %s', 'customer-reviews-collector-for-woocommerce'), '<a href="'. $pluginUrl .'" target="_blank">'. $pluginUrl .'</a>');
?>
<br />
<?php echo __('Please help us with some information:', 'customer-reviews-collector-for-woocommerce'); ?>
<ul>
<li><?php echo __('Describe your problem', 'customer-reviews-collector-for-woocommerce'); ?></li>
<li><?php echo sprintf(__('You can share a screenshot with %s', 'customer-reviews-collector-for-woocommerce'), '<a href="'. $screenshotUrl .'" target="_blank">'. $screenshotUrl .'</a>'); ?></li>
<li><?php echo sprintf(__('You can share a screencast video with %s', 'customer-reviews-collector-for-woocommerce'), '<a href="'. $screencastUrl .'" target="_blank">'. $screencastUrl .'</a>'); ?></li>
<li><?php echo sprintf(__('If you have an (webserver) error log, you can copy it to the issue, or link it with %s', 'customer-reviews-collector-for-woocommerce'), '<a href="'. $pastebinUrl .'" target="_blank">'. $pastebinUrl .'</a>'); ?></li>
<li><?php echo __('And include the information below:', 'customer-reviews-collector-for-woocommerce'); ?></li>
</ul>
</li>
</ul>
<textarea class="ti-troubleshooting-info" readonly><?php include $pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'troubleshooting.php'; ?></textarea>
<div class="row">
<div class="col justify-content-end d-flex">
<a href=".ti-troubleshooting-info" class="btn btn-primary btn-copy2clipboard ti-pull-right ti-tooltip ti-toggle-tooltip ti-tooltip-left">
<?php echo __('Copy to clipboard', 'customer-reviews-collector-for-woocommerce') ;?>
<span class="ti-tooltip-message">
<span style="color: #00ff00; margin-right: 2px">✓</span>
<?php echo __('Copied', 'customer-reviews-collector-for-woocommerce'); ?>
</span>
</a>
</div>
</div>
</div>
</div>
<div class="row">
<div class="col plugin-subtitle"><?php echo __('Re-create plugin', 'customer-reviews-collector-for-woocommerce'); ?></div>
</div>
<div class="card">
<div class="card-body">
<p class="size-16"><?php echo __('Re-create the database tables of the plugin.<br />Please note: this removes all settings and invitations.', 'customer-reviews-collector-for-woocommerce'); ?></p>
<div class="row">
<div class="col justify-content-end d-flex">
<a href="<?php echo wp_nonce_url('?page='. $_page .'&tab=troubleshooting&command=re-create', 'ti-recreate'); ?>" class="btn btn-primary ti-btn-loading-on-click"><?php echo __('Re-create plugin', 'customer-reviews-collector-for-woocommerce'); ?></a>
</div>
</div>
</div>
</div>
<div class="row">
<div class="col plugin-subtitle"><?php echo __('Translation', 'customer-reviews-collector-for-woocommerce'); ?></div>
</div>
<div class="card">
<div class="card-body">
<p class="size-16">
<?php echo __('If you notice an incorrect translation in the plugin text, please report it here:', 'customer-reviews-collector-for-woocommerce'); ?>
 <a href="mailto:support@trustindex.io">support@trustindex.io</a>
</p>
</div>
</div>
</div>
