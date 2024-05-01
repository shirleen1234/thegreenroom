<?php
defined('ABSPATH') or die('No script kiddies please!');
if (isset($_POST['command']) && $_POST['command'] === 'send-feature-request') {
check_admin_referer('send-feature-request_' . $pluginManagerInstance->get_plugin_slug(), '_wpnonce_send_feature_request');
$name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : "";
$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : "";
$description = isset($_POST['description']) ? wp_kses_post(stripslashes($_POST['description'])) : "";
$workaround = isset($_POST['workaround']) ? wp_kses_post(stripslashes($_POST['workaround'])) : "";
$attachments = isset($_POST['attachments']) ? wp_kses_post(stripslashes($_POST['attachments'])) : "";
if ($email && $description) {
$subject = 'Feature request from '. $pluginNameForEmails .' plugin';
$message = 'We received a feature request to the '. $pluginNameForEmails .' plugin from <strong>'. $name .' ('. $email .', url: '. get_option('siteurl') .')</strong>:<br /><br /><strong>'. $description .'</strong><br /><br />Current workaround: <br /><br /><strong>'. $workaround .'</strong>';
if ($attachments) {
$message .= '<br />Attached urls: <br />- '. str_replace("\n", '<br />- ', $attachments);
}
ob_start();
include $pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'troubleshooting.php';
$troubleshootingData = ob_get_clean();
$message .= '<br /><br />Troubleshooting:<br />'. nl2br(str_replace("\t", "&nbsp;&nbsp;&nbsp;&nbsp;", $troubleshootingData));
wp_mail('support@trustindex.io', $subject, $message, [ 'From: '. $email, 'Content-Type: text/html; charset=UTF-8' ]);
}
exit;
}
?>
<h1 class="ti-header-title"><?php echo __('Feature request', 'customer-reviews-collector-for-woocommerce'); ?></h1>

<div class="plugin-head"><?php echo __('Missing a feature?', 'customer-reviews-collector-for-woocommerce'); ?></div>
<p class="size-16">
<?php echo __('Anything you are missing in our product?', 'customer-reviews-collector-for-woocommerce'); ?><br />
<?php echo __('Drop a message here to let us know!', 'customer-reviews-collector-for-woocommerce'); ?>
</p>
<div class="card"><div class="card-body ti-feature-request"><form method="post" enctype="multipart/form-data">
<?php wp_nonce_field('send-feature-request_' . $pluginManagerInstance->get_plugin_slug(), '_wpnonce_send_feature_request' ); ?>
<input type="hidden" name="command" value="send-feature-request" />
<div class="ti-form-group">
<label><?php echo __('Please describe the feature you need', 'customer-reviews-collector-for-woocommerce'); ?>*</label>
<textarea class="form-control" name="description" rows="3" placeholder="<?php echo __('The more detail you can share, the better.', 'customer-reviews-collector-for-woocommerce'); ?>"></textarea>
</div>
<div class="ti-form-group">
<label>
<?php echo __('Attach images', 'customer-reviews-collector-for-woocommerce'); ?>
(<?php echo sprintf(__('use %s for image share', 'customer-reviews-collector-for-woocommerce'), '<a href="https://snipboard.io/" target="_blank">snipboard.io</a>'); ?>)
</label>
<textarea class="form-control" name="attachments" rows="3" placeholder="<?php echo __('URL of images (each on a separate line)', 'customer-reviews-collector-for-woocommerce'); ?>"></textarea>
</div>
<div class="ti-form-group">
<label><?php echo __('Please describe your current workaround', 'customer-reviews-collector-for-woocommerce'); ?></label>
<textarea class="form-control" name="workaround" rows="3" placeholder="<?php echo __('If you have one - otherwise leave it blank.', 'customer-reviews-collector-for-woocommerce'); ?>"></textarea>
</div>
<div class="ti-form-group">
<label><?php echo __('Your name', 'customer-reviews-collector-for-woocommerce'); ?></label>
<input type="text" class="form-control" name="name" placeholder="<?php echo __('The more detail you can share, the better.', 'customer-reviews-collector-for-woocommerce'); ?>" />
</div>
<div class="ti-form-group">
<label><?php echo __('Your email address', 'customer-reviews-collector-for-woocommerce'); ?>*</label>
<input type="text" class="form-control" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" />
</div>
<p class="size-16"><?php echo __('Thanks for taking the time - we will get back to you as soon as possible to ask a few clarifying question or to give you an update.', 'customer-reviews-collector-for-woocommerce'); ?></p>
<div class="row"><div class="col justify-content-end d-flex">
<a href="#" class="btn btn-primary btn-send-feature-request ti-tooltip-left ti-tooltip ti-toggle-tooltip">
<?php echo __('Send feature request', 'customer-reviews-collector-for-woocommerce') ;?>
<span class="ti-tooltip-message">
<span style="color: #00ff00; margin-right: 2px">✓</span>
<?php echo __('Feature request sent', 'customer-reviews-collector-for-woocommerce'); ?>
</span>
</a>
</div>
</div></form>
</div>
</div>