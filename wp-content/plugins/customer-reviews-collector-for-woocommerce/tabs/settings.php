<?php
defined('ABSPATH') or die('No script kiddies please!');
function save_settings_state($newState)
{
global $settingsState;
global $pluginManagerInstance;
if ($settingsState <= $newState) {
update_option($pluginManagerInstance->get_option_name('settings-state'), $newState, false);
}
}
if ($tiCommand === 'save-platform-url') {
check_admin_referer('ti-collector-save-platform-url');
$pluginManagerInstance->save_option_from_request('platform-url', 'array');
save_settings_state(2);
exit;
}
else if ($tiCommand === 'save-trigger') {
check_admin_referer('ti-collector-save-trigger');
$pluginManagerInstance->save_option_from_request('trigger-delay');
$pluginManagerInstance->save_option_from_request('trigger-event');
$pluginManagerInstance->save_option_from_request('frequency');
$pluginManagerInstance->save_option_from_request('exclude-emails', 'email-array');
save_settings_state(3);
exit;
}
else if ($tiCommand === 'save-email-settings') {
check_admin_referer('ti-collector-save-email-settings');
$pluginManagerInstance->save_option_from_request('email-sender');
$pluginManagerInstance->save_option_from_request('email-sender-email');
$pluginManagerInstance->save_option_from_request('email-subject');
$pluginManagerInstance->save_option_from_request('email-text', 'text');
$pluginManagerInstance->save_option_from_request('email-footer-text', 'text');
if (isset($_REQUEST['logo-image']) && $_REQUEST['logo-image']) {
if ($_REQUEST['logo-image'] === 'delete') {
$file = wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $pluginManagerInstance->get_email_logo_filename();
if (file_exists($file)) {
unlink($file);
}
}
else {
$pluginManagerInstance->uploadLogoImage($_REQUEST['logo-image']);
}
}
save_settings_state(4);
exit;
}
else if ($tiCommand === 'save-negative-invitation') {
check_admin_referer('ti-collector-save-negative-invitation');
update_option($pluginManagerInstance->get_option_name('campaign-active'), 1, false);
save_settings_state(4);
exit;
}
else if ($tiCommand === 'save-support-language') {
check_admin_referer('ti-save-support-language');
$pluginManagerInstance->save_option_from_request('support-language');
exit;
}
else if ($tiCommand === 'test-email') {
check_admin_referer('ti-test-email');
$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : "";
if ($email) {
if (isset($_POST['email-text'])) {
$_POST['email-text'] = wp_kses_post(stripslashes($_POST['email-text']));
}
if (isset($_POST['email-footer-text'])) {
$_POST['email-footer-text'] = wp_kses_post(stripslashes($_POST['email-footer-text']));
}
$_POST['customer_full_name'] = 'Test Customer';
if (isset($_POST['logo-image']) && $_POST['logo-image'] !== 'delete') {
$_POST['logo-image'] = $pluginManagerInstance->uploadLogoImage($_POST['logo-image'], 'ti-collector-email-logo-tmp.png');
if ($_POST['logo-image']) {
$_POST['logo-image'] .= '?' . time();
}
}
$_POST['email-sender'] = wp_unslash($_POST['email-sender']);
$pluginManagerInstance->sendMail($email, $_POST);
}
exit;
}
$tiSettings = [];
foreach ($pluginManagerInstance->get_default_settings() as $name => $default) {
$tiSettings[ $name ] = get_option($pluginManagerInstance->get_option_name($name), $default);
}
$supportLocales = array_map(function($tmp) use($pluginManagerInstance) {
return str_replace($pluginManagerInstance->get_plugin_slug() . '-', '', $tmp);
}, get_available_languages($pluginManagerInstance->get_plugin_dir() . 'languages'));
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'translation-install.php');
$translations = wp_get_available_translations();
$supportLanguages = [];
foreach ($supportLocales as $lIndex) {
$supportLanguages[ $lIndex ] = isset($translations[ $lIndex ]) ? $translations[ $lIndex ]['english_name'] : $lIndex;
}
$supportLanguages['en'] = 'English';
asort($supportLanguages);
?>
<div class="plugin-head"><?php echo __('Set up automatic review requests', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="plugin-body">
<div class="accordion accordion-flush accordion-check" id="ti-settings">
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button disabled<?php if ($settingsState !== 1): ?> collapsed<?php endif; ?><?php if ($settingsState > 1): ?> done<?php endif; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#ti-platform-url" aria-expanded="false" aria-controls="ti-platform-url">
<div class="acc-check"><span></span></div>
<?php echo __('Where do you want to collect reviews', 'customer-reviews-collector-for-woocommerce'); ?>
</button>
</h2>
<form id="ti-platform-url" method="post" class="accordion-collapse collapse<?php if ($settingsState === 1): ?> show<?php endif; ?>" data-bs-parent="#ti-settings" data-platform-urls='<?php echo json_encode($tiSettings['platform-url']); ?>'>
<input type="hidden" name="command" value="save-platform-url" />
<?php wp_nonce_field('ti-collector-save-platform-url'); ?>
<div class="accordion-body">
<div class="alert alert-danger alert-hidden alert-url-invalid"><?php echo __('URL is invalid', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="alert alert-danger alert-hidden alert-percent-invalid"><?php echo __('The total percentage must be 100%%', 'customer-reviews-collector-for-woocommerce'); ?></div>
<p class="text-muted mb-4">
<strong><?php echo __('Connect your Google Business Profile to get your own Google review writing form URL.', 'customer-reviews-collector-for-woocommerce'); ?></strong><br />
<?php echo __('This URL will be inserted into the review request email.', 'customer-reviews-collector-for-woocommerce'); ?><br />
<?php echo __('Alternatively you can enter the URL of any review platform by selecting the custom option.', 'customer-reviews-collector-for-woocommerce'); ?>
</p>
<div id="platform-urls"></div>
<a href="#" class="link-clean btn-add-platform-url">
<span class="dashicons dashicons-plus-alt" style="margin-top: 2px"></span> <?php echo __('add URL', 'customer-reviews-collector-for-woocommerce'); ?>
</a>
<p class="text-muted platform-urls-info d-none"><?php echo __('Each link will appear in the email according to %% probability that you define.', 'customer-reviews-collector-for-woocommerce'); ?></p>
<div class="row">
<div class="col-12 col-sm justify-content-end d-flex">
<?php if ($settingsState >= 2): ?>
<a href="#" class="btn btn-success btn-next btn-default-disabled btn-no-next-step"><?php echo __('Save changes', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php else: ?>
<a href="#" class="btn btn-success btn-next"><?php echo __('Next', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php endif; ?>
</div>
</div>
</div>
</form>
</div>
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button disabled<?php if ($settingsState !== 2): ?> collapsed<?php endif; ?><?php if ($settingsState > 2): ?> done<?php endif; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#ti-trigger" aria-expanded="false" aria-controls="ti-trigger">
<div class="acc-check"><span></span></div>
<?php echo __('How to send e-mail request', 'customer-reviews-collector-for-woocommerce'); ?>
</button>
</h2>
<form id="ti-trigger" method="post" class="accordion-collapse collapse<?php if ($settingsState === 2): ?> show<?php endif; ?>" data-bs-parent="#ti-settings">
<input type="hidden" name="command" value="save-trigger" />
<?php wp_nonce_field('ti-collector-save-trigger'); ?>
<div class="accordion-body">
<div class="alert alert-danger alert-hidden alert-email-invalid"><?php echo __('Invalid email in exclude list', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="row">
<label class="col-12 col-sm-4 col-md-2 col-form-label"><?php echo __('Send', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm-auto">
<select class="form-select" name="trigger-delay">
<option value="0"<?php if ((int)$tiSettings['trigger-delay'] === 0): ?> selected<?php endif; ?>><?php echo __('immediately', 'customer-reviews-collector-for-woocommerce'); ?></option>
<?php foreach ([ 1, 2, 3, 4, 5, 6, 7, 14, 21 ] as $days): ?>
<option value="<?php echo esc_attr($days); ?>"<?php if ((int)$tiSettings['trigger-delay'] === $days): ?> selected<?php endif; ?>><?php echo sprintf(__('after %s days', 'customer-reviews-collector-for-woocommerce'), $days); ?></option>
<?php endforeach; ?>
</select>
</div>
<label class="col-12 col-sm-auto col-form-label"><?php echo __('if order is', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-auto">
<select class="form-select" name="trigger-event">
<?php foreach (wc_get_order_statuses() as $status => $name): ?>
<option value="<?php echo esc_attr($status); ?>"<?php if ($tiSettings['trigger-event'] === $status): ?> selected<?php endif; ?>>
<?php echo esc_html($name); ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="row">
<label class="col-12 col-sm-4 col-md-2 col-form-label"><?php echo __('Frequency', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm-auto">
<select class="form-select" name="frequency">
<option value="0"<?php if ((int)$tiSettings['frequency'] === 0): ?> selected<?php endif; ?>><?php echo __('After each order', 'customer-reviews-collector-for-woocommerce'); ?></option>
<?php foreach ([ 1, 2, 3, 4, 5, 6 ] as $months): ?>
<option value="<?php echo esc_attr($months); ?>"<?php if ((int)$tiSettings['frequency'] === $months): ?> selected<?php endif; ?>><?php echo sprintf(__('No more than 1 email in %d month', 'customer-reviews-collector-for-woocommerce'), $months); ?></option>
<?php endforeach; ?>
</select>
</div>
</div>
<div class="row">
<label class="col-12 col-sm-4 col-md-2 col-form-label"><?php echo __('Exclude email(s)', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<textarea class="form-control" rows="2" name="exclude-emails" id="ti-exclude-emails"><?php echo implode(', ', $tiSettings['exclude-emails']); ?></textarea>
<p class="text-muted mt-1"><?php echo __('Separate emails with comma. If you want to add an email domain, use the @example-domain.com format.', 'customer-reviews-collector-for-woocommerce'); ?>
</p>
</div>
</div>
<div class="row">
<div class="col justify-content-end d-flex">
<?php if ($settingsState >= 3): ?>
<a href="#" class="btn btn-success btn-next btn-default-disabled btn-no-next-step"><?php echo __('Save changes', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php else: ?>
<a href="#" class="btn btn-success btn-next"><?php echo __('Next', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php endif; ?>
</div>
</div>
</div>
</form>
</div>
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button disabled<?php if (!($settingsState === 3 || $pluginManagerInstance->is_campaign_active())): ?> collapsed<?php endif; ?><?php if ($settingsState > 3): ?> done<?php endif; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#ti-email-settings" aria-expanded="false" aria-controls="ti-email-settings">
<div class="acc-check"><span></span></div>
<?php echo __('E-mail customization', 'customer-reviews-collector-for-woocommerce'); ?>
</button>
</h2>
<form id="ti-email-settings" method="post" class="accordion-collapse collapse<?php if ($settingsState === 3 || $pluginManagerInstance->is_campaign_active()): ?> show<?php endif; ?>" data-bs-parent="#ti-settings">
<input type="hidden" name="command" value="save-email-settings" />
<?php wp_nonce_field('ti-collector-save-email-settings'); ?>
<div class="accordion-body">
<div class="alert alert-danger alert-hidden alert-empty"><?php echo __('Fill all fields', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="alert alert-danger alert-hidden alert-email-invalid"><?php echo __('Sender e-mail is invalid', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="row">
<label class="col-12 col-sm-4 col-md-3 col-form-label"><?php echo __('Sender name', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<input type="text" class="form-control" name="email-sender" value="<?php echo esc_attr($tiSettings['email-sender']); ?>" placeholder="<?php echo __('Sender name', 'customer-reviews-collector-for-woocommerce'); ?>" autocomplete="new-password" />
</div>
</div>
<div class="row">
<label class="col-12 col-sm-4 col-md-3 col-form-label"><?php echo __('Sender e-mail', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<input type="text" class="form-control" name="email-sender-email" value="<?php echo esc_attr($tiSettings['email-sender-email']); ?>" placeholder="<?php echo __('Sender e-mail', 'customer-reviews-collector-for-woocommerce'); ?>" />
<small class="text-danger" style="display: none"><?php echo __("Sender e-mail must be on the site's domain address so that the e-mails do not end up in spam.", 'customer-reviews-collector-for-woocommerce'); ?></small>
</div>
</div>
<div class="row">
<label class="col-12 col-sm-4 col-md-3 col-form-label"><?php echo __('E-mail subject', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<input type="text" class="form-control" name="email-subject" value="<?php echo esc_attr($tiSettings['email-subject']); ?>" placeholder="<?php echo __('E-mail subject', 'customer-reviews-collector-for-woocommerce'); ?>" autocomplete="new-password" />
</div>
</div>
<div class="row">
<?php $hasLogoImage = file_exists(wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . $pluginManagerInstance->get_email_logo_filename()); ?>
<label class="col-12 col-sm-4 col-md-3 col-form-label"><?php echo __('E-mail logo', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<input name="logo-image" id="ti-collector-email-logo-image-input" type="hidden" />
<input type="file" accept=".jpg, .png" style="display: none">
<a
href="#"
class="btn btn-success btn-email-logo-upload"
data-browse-text="<?php echo __('Browse image', 'customer-reviews-collector-for-woocommerce'); ?>"
data-change-text="<?php echo __('Change image', 'customer-reviews-collector-for-woocommerce'); ?>">
<?php if ($hasLogoImage): ?>
<?php echo __('Change image', 'customer-reviews-collector-for-woocommerce'); ?>
<?php else: ?>
<?php echo __('Browse image', 'customer-reviews-collector-for-woocommerce'); ?>
<?php endif; ?>
</a>
<a href="#" class="btn btn-danger btn-email-logo-delete<?php if (!$hasLogoImage): ?> d-none<?php endif; ?>"><?php echo __('Delete', 'customer-reviews-collector-for-woocommerce'); ?></a>
</div>
</div>
<div class="email-config mt-4 mb-4">
<nav>
<div class="nav nav-tabs" id="nav-tab" role="tablist">
<button class="nav-link active" id="ti-email-preview-tab" data-bs-toggle="tab" data-bs-target="#ti-email-preview" type="button" role="tab" aria-controls="ti-email-preview" aria-selected="false"><?php echo __('E-mail preview', 'customer-reviews-collector-for-woocommerce'); ?></button>
<button class="nav-link" id="ti-edit-email-tab" data-bs-toggle="tab" data-bs-target="#ti-edit-email" type="button" role="tab" aria-controls="ti-edit-email" aria-selected="true"><?php echo __('Edit e-mail', 'customer-reviews-collector-for-woocommerce'); ?></button>
</div>
</nav>
<div class="tab-content" id="nav-tabContent">
<div class="tab-pane fade show active" id="ti-email-preview" role="tabpanel" aria-labelledby="email-prev-tab">
<iframe class="preview-iframe" id="ti-email-preview-iframe" data-url="<?php echo $pluginManagerInstance->get_email_template_url(); ?>"></iframe>
</div>
<div class="tab-pane fade" id="ti-edit-email" role="tabpanel" aria-labelledby="edit-email-tab">
<?php if (function_exists('wp_editor')): ?>
<?php wp_editor($tiSettings['email-text'], 'email-text', [
'media_buttons' => false,
'tinymce' => [
'toolbar1' => 'undo,redo,fontsizeselect,bold,italic,underline,alignleft,aligncenter,alignright,hr',
'toolbar2' => '',
'toolbar3' => '',
'toolbar4' => ''
],
'wpautop' => true
]); ?>
<?php else: ?>
<textarea class="form-control" id="email-text" name="email-text" rows="15"><?php echo esc_textarea($tiSettings['email-text']); ?></textarea>
<?php endif; ?>
<div class="p-4">
<div class="row">
<label class="col-12 col-sm-4 col-md-3 col-form-label"><?php echo __('E-mail footer text', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<textarea class="form-control" name="email-footer-text" placeholder="<?php echo __('E-mail footer text', 'customer-reviews-collector-for-woocommerce'); ?>"><?php echo esc_textarea($tiSettings['email-footer-text']); ?></textarea>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="alert alert-test-email alert-danger alert-hidden alert-test-email-invalid"><?php echo __('E-mail is invalid', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="alert alert-test-email alert-success alert-hidden"><?php echo __('E-mail sent', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="row">
<label class="col-12 col-sm-auto col-form-label"><?php echo __('Send test e-mail to', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-12 col-sm">
<input type="email" id="ti-test-email-input" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>" />
</div>
<div class="col-12 col-sm-auto">
<a href="#" class="btn btn-light btn-send-test-email" data-nonce="<?php echo wp_create_nonce('ti-test-email'); ?>"><?php echo __('Send', 'customer-reviews-collector-for-woocommerce'); ?></a>
</div>
</div>
<div class="row">
<div class="col justify-content-end d-flex">
<?php if ($settingsState > 3 || $pluginManagerInstance->is_campaign_active()): ?>
<a href="#" class="btn btn-success btn-next btn-default-disabled btn-no-next-step"><?php echo __('Save changes', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php else: ?>
<a href="#" class="btn btn-success btn-next"><?php echo __('Next', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php endif; ?>
</div>
</div>
</div>
</form>
</div>
<div class="accordion-item">
<h2 class="accordion-header">
<button class="accordion-button disabled<?php if ($settingsState !== 4 || $pluginManagerInstance->is_campaign_active()): ?> collapsed<?php endif; ?><?php if ($pluginManagerInstance->is_campaign_active()): ?> done<?php endif; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#ti-negative-invitation" aria-expanded="false" aria-controls="ti-negative-invitation">
<div class="acc-check"><span></span></div>
<?php echo __('Avoid negative reviews with intelligent invitation system', 'customer-reviews-collector-for-woocommerce'); ?>
</button>
</h2>
<form id="ti-negative-invitation" method="post" class="accordion-collapse collapse<?php if ($settingsState === 4 && !$pluginManagerInstance->is_campaign_active()): ?> show<?php endif; ?>" data-bs-parent="#ti-settings">
<input type="hidden" name="command" value="save-negative-invitation" />
<?php wp_nonce_field('ti-collector-save-negative-invitation'); ?>
<div class="accordion-body">
<p><?php echo __('If your client chooses 1, 2 or 3 stars, we do not redirect them to write a review right away. Instead, we offer the option to contact your customer support directly to resolve the issue.', 'customer-reviews-collector-for-woocommerce'); ?></p>
<div class="row g-2">
<label for="" class="col-12 col-sm-4 col-md-3 col-form-label"><?php echo __('Feedback form language', 'customer-reviews-collector-for-woocommerce'); ?></label>
<div class="col-auto">
<select class="form-select feedback-form-language" data-nonce="<?php echo wp_create_nonce('ti-save-support-language'); ?>">
<?php foreach ($supportLanguages as $lIndex => $name): ?>
<option value="<?php echo $lIndex; ?>" <?php if ($tiSettings['support-language'] === $lIndex): ?>selected<?php endif; ?>><?php echo $name; ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-auto">
<a href="<?php echo $pluginManagerInstance->get_feedback_url(); ?>" target="_blank" class="btn btn-light" id="ti-feedback-form-url">
<?php echo __('Preview feedback form', 'customer-reviews-collector-for-woocommerce'); ?>
<span class="dashicons dashicons-external"></span>
</a>
</div>
</div>
<?php if (!$pluginManagerInstance->is_campaign_active()): ?>
<div class="row">
<div class="col justify-content-end d-flex">
<a href="<?php echo esc_url('?page='. $_page .'&tab=dashboard'); ?>" class="btn btn-success btn-next btn-finish"><?php echo __('Start e-mail campaign'); ?></a>
</div>
</div>
<?php endif; ?>
</div>
</form>
</div>
</div>
</div>
<div id="platform-url-template" class="row g-2 platform-url d-none">
<div class="col-12 col-sm col-input">
<input
type="text"
name="platform-url[][url]"
class="form-control"
data-placeholder="<?php echo __('click to connect Google URL', 'customer-reviews-collector-for-woocommerce'); ?>"
data-placeholder-custom="<?php echo sprintf(__('type a custom URL (e.g. %s)', 'customer-reviews-collector-for-woocommerce'), 'https://example.com'); ?>"
/>
<select class="form-select">
<option value="google">Google</option>
<option value="custom"><?php echo __('Custom', 'customer-reviews-collector-for-woocommerce'); ?></option>
</select>
<img src="https://cdn.trustindex.io/assets/platform/Google/icon.svg" class="source-icon" alt="Google" />
<a
href="#"
class="btn btn-success"
data-change-text="<?php echo __('Change', 'customer-reviews-collector-for-woocommerce'); ?>"
data-connect-text="<?php echo __('Connect', 'customer-reviews-collector-for-woocommerce'); ?>"></a>
</div>
<div class="col-auto col-percent">
<select class="form-select" name="platform-url[][percent]" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo __('Review URL probability', 'customer-reviews-collector-for-woocommerce'); ?>">
<?php for ($i = 0; $i <= 100; $i+=5): ?>
<option value="<?php echo $i; ?>"><?php echo $i; ?>%</option>
<?php endfor; ?>
</select>
</div>
<div class="col-auto col-preview">
<button type="button" class="btn btn-light btn-test-review-link" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo __('Preview URL', 'customer-reviews-collector-for-woocommerce'); ?>" disabled>
<span class="dashicons dashicons-external"></span>
</button>
</div>
<div class="col-auto col-remove">
<a href="#" class="btn btn-light btn-remove-platform-url" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo __('Delete URL', 'customer-reviews-collector-for-woocommerce'); ?>">
<span class="dashicons dashicons-remove"></span>
</a>
</div>
</div>
<!-- Source Connect Modal -->
<div class="modal fade" id="modal-source-import" tabindex="-1" role="dialog">
<div class="modal-dialog modal-lg" role="document">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title"><?php echo __('Google Business Profile name or location', 'customer-reviews-collector-for-woocommerce'); ?></h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __('Close', 'customer-reviews-collector-for-woocommerce'); ?>"></button>
</div>
<div class="modal-body">
<iframe alt-src="https://admin.trustindex.io/integration/wordpressSourceConnect?type=google" scrolling="no" style="width: 100%; height: 0; overflow: hidden; border: 0" allowfullscreen="true"></iframe>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('Close', 'customer-reviews-collector-for-woocommerce'); ?></button>
<button type="button" class="btn btn-success btn-source-connect btn-loading-animation-on-click" disabled><?php echo __('Select', 'customer-reviews-collector-for-woocommerce'); ?></button>
</div>
</div>
</div>
</div>