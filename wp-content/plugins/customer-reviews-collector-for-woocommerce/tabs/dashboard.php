<?php
defined('ABSPATH') or die('No script kiddies please!');
if ($tiCommand === 'toggle-campaign') {
check_admin_referer('ti-toggle-campaign');
update_option($pluginManagerInstance->get_option_name('campaign-active'), $pluginManagerInstance->is_campaign_active() ? 0 : 1, false);
exit;
}
else if ($tiCommand === 'rate-us-feedback') {
check_admin_referer('ti-rate-us');
$text = isset($_POST['text']) ? trim(wp_kses_post(stripslashes($_POST['text']))) : "";
$email = isset($_POST['email']) ? trim(sanitize_text_field($_POST['email'])) : "";
$star = isset($_REQUEST['star']) ? (int)$_REQUEST['star'] : 1;
update_option($pluginManagerInstance->get_option_name('rate-us-feedback'), $star, false);
if ($star > 3) {
header('Location: https://wordpress.org/support/plugin/'. $pluginManagerInstance->get_plugin_slug() . '/reviews/?rate='. $star .'#new-post');
}
else {
wp_mail('support@trustindex.io', 'Feedback from '. $pluginNameForEmails .' plugin', "We received a <strong>$star star</strong> feedback about the $pluginNameForEmails plugin from $email:<br /><br />$text", [
'From: '. $email,
'Content-Type: text/html; charset=UTF-8'
]);
}
exit;
}
$rateUsFeedback = get_option($pluginManagerInstance->get_option_name('rate-us-feedback'), 0);
wp_enqueue_script('trustindex-js', 'https://cdn.trustindex.io/loader.js', [], false, true);
if ($settingsState >= 4) {
$statistics = [];
require_once(ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'upgrade.php');
foreach ([ 'sent', 'opened', 'clicked' ] as $key) {
$sql = 'SELECT COUNT(`id`) AS `num` FROM `'. $pluginManagerInstance->get_tablename('schedule_list') .'` WHERE DATE(FROM_UNIXTIME(`timestamp`)) >= "%date%" AND sent = 1';
if ($key !== 'sent') {
$sql .= ' AND `'. $key .'_at` IS NOT NULL';
}
$statistics[ $key ]['day'] = $wpdb->get_results(str_replace('%date%', date('Y-m-d'), $sql))[0]->num;
$statistics[ $key ]['week'] = $wpdb->get_results(str_replace('%date%', date('Y-m-d', strtotime('-7 days')), $sql))[0]->num;
$statistics[ $key ]['month'] = $wpdb->get_results(str_replace('%date%', date('Y-m-d', strtotime('-30 days')), $sql))[0]->num;
$statistics[ $key ]['total'] = $wpdb->get_results(str_replace('%date%', '1970-01-01', $sql))[0]->num;
}
foreach ([ 'day', 'week', 'month', 'total' ] as $key) {
$statistics['open-rate'][ $key ] = $statistics['sent'][ $key ] ? round($statistics['opened'][ $key ] / $statistics['sent'][ $key ] * 100) : 0;
$statistics['click-open-rate'][ $key ] = $statistics['opened'][ $key ] ? round($statistics['clicked'][ $key ] / $statistics['opened'][ $key ] * 100) : 0;
$statistics['click-rate'][ $key ] = $statistics['sent'][ $key ] ? round($statistics['clicked'][ $key ] / $statistics['sent'][ $key ] * 100) : 0;
}
$statistics['graph'] = $wpdb->get_results('
SELECT
DATE(`clicked_at`) AS `date`,
COUNT(`id`) AS `clicks`,
SUM(CASE WHEN `feedback` IS NOT NULL THEN 1 ELSE 0 END) AS `negative`,
SUM(CASE WHEN `feedback` IS NULL THEN 1 ELSE 0 END) AS `positive`
FROM `'. $pluginManagerInstance->get_tablename('schedule_list') .'`
WHERE DATE(`clicked_at`) >= "'. date('Y-m-d', strtotime('-30 days')) .'"
GROUP BY DATE(`clicked_at`)
', ARRAY_A);
$statistics['graph'] = array_combine(array_column($statistics['graph'], 'date'), $statistics['graph']);
$d = 0;
while ($d < 30) {
$date = date('Y-m-d', strtotime("-$d days"));
if (!isset($statistics['graph'][ $date ])) {
$statistics['graph'][ $date ] = [
'click' => 0,
'negative' => 0,
'positive' => 0
];
}
$d++;
}
ksort($statistics['graph']);
}
?>
<div class="plugin-body">
<?php if ($settingsState >= 4 && !$rateUsFeedback): ?>
<div class="card ti-rate-us-box">
<div class="card-body">
<h3><?php echo __("How's experience with Trustindex?", 'customer-reviews-collector-for-woocommerce'); ?></h3>
<p><?php echo __('Rate us clicking on the stars', 'customer-reviews-collector-for-woocommerce'); ?></p>
<div class="ti-quick-rating" data-nonce="<?php echo wp_create_nonce('ti-rate-us'); ?>">
<?php for ($i = 5; $i >= 1; $i--): ?><div class="ti-star-check" data-value="<?php echo $i; ?>"></div><?php endfor; ?>
</div>
</div>
</div>
<?php endif; ?>
<div class="card card-mid-grey">
<div class="card-header card-header-lg"><?php echo __('Collect customer reviews automatically and totally free', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="card-body">
<ul class="text-md ti-list">
<li><?php echo __('Collect reviews on Google, Facebook, Yelp, Trustpilot and 100+ other platforms easily.', 'customer-reviews-collector-for-woocommerce'); ?></li>
<li><?php echo __('Avoid negative reviews with our intelligent review invitation system.', 'customer-reviews-collector-for-woocommerce'); ?></li>
</ul>
<?php if ($settingsState < 4): ?>
<a href="<?php echo esc_url('?page='. $_page .'&tab=settings'); ?>" class="btn btn-primary ti-btn-loading-on-click"><?php echo __('Set up invitations', 'customer-reviews-collector-for-woocommerce'); ?></a>
<div class="badge bg-danger badge-for-btn"><?php echo __('Start here', 'customer-reviews-collector-for-woocommerce'); ?></div>
<?php endif; ?>
</div>
</div>
<?php if ($settingsState >= 4): ?>
<div class="card campaign-status">
<div class="card-body">
<div class="row align-items-center">
<div class="col-auto">
<strong><?php echo __('Review request campaign', 'customer-reviews-collector-for-woocommerce'); ?></strong>
</div>
<div class="col-auto">
<div class="form-check form-switch form-switch-md">
<input class="form-check-input" type="checkbox" data-nonce="<?php echo wp_create_nonce('ti-toggle-campaign'); ?>" id="ti-campaign-activate-checkbox" <?php if ($pluginManagerInstance->is_campaign_active()): ?>checked<?php endif; ?>>
<label class="form-check-label" data-on-text="<?php echo __('Enabled', 'customer-reviews-collector-for-woocommerce'); ?>" data-off-text="<?php echo __('Disabled', 'customer-reviews-collector-for-woocommerce'); ?>"></label>
</div>
</div>
<div class="col-auto ml-auto">
<a class="btn btn-light ti-btn-loading-on-click" href="<?php echo esc_url('?page='. $_page .'&tab=settings'); ?>"><?php echo __('Settings', 'customer-reviews-collector-for-woocommerce'); ?></a>
</div>
</div>
</div>
</div>
<div class="row align-items-center">
<div class="col-md plugin-subtitle"><?php echo __('Review requests statistics', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="col-auto ml-auto period-tab-filter nav nav-tabs" role="tablist">
<button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#statistics-row-day" role="tab"><?php echo __('Day', 'customer-reviews-collector-for-woocommerce'); ?></button>
<button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#statistics-row-week" role="tab"><?php echo __('Week', 'customer-reviews-collector-for-woocommerce'); ?></button>
<button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#statistics-row-month" role="tab"><?php echo __('Month', 'customer-reviews-collector-for-woocommerce'); ?></button>
<button type="button" class="nav-link active" data-bs-toggle="tab" data-bs-target="#statistics-row-total" role="tab"><?php echo __('Total', 'customer-reviews-collector-for-woocommerce'); ?></button>
</div>
</div>
<div class="tab-content">
<?php foreach ([ 'day', 'week', 'month', 'total' ] as $key): ?>
<div class="tab-pane fade<?php if ($key === 'total'): ?> show active<?php endif; ?>" role="tabpanel" id="statistics-row-<?php echo $key; ?>">
<div class="row stat-highlight">
<div class="col-sm-4">
<div class="card">
<div class="card-body">
<div class="item">
<div class="icon"><i class="fa fa-paper-plane"></i></div>
<div class="number"><?php echo $statistics['sent'][ $key ]; ?> <small><?php echo __('sent', 'customer-reviews-collector-for-woocommerce'); ?></small></div>
</div>
</div>
</div>
</div>
<div class="col-sm-4">
<div class="card">
<div class="card-body">
<div class="item">
<div class="icon"><i class="fa fa-envelope-open"></i></div>
<div class="number"><?php echo $statistics['opened'][ $key ]; ?> <small><?php echo __('opened', 'customer-reviews-collector-for-woocommerce'); ?></small></div>
<div class="rate"><?php echo sprintf(__('Open rate %s', 'customer-reviews-collector-for-woocommerce'), $statistics['open-rate'][ $key ] . '%'); ?></div>
</div>
</div>
</div>
</div>
<div class="col-sm-4">
<div class="card">
<div class="card-body">
<div class="item">
<div class="icon"><i class="fa fa-star"></i></div>
<div class="number"><?php echo $statistics['clicked'][ $key ]; ?> <small><?php echo __('clicked', 'customer-reviews-collector-for-woocommerce'); ?></small></div>
<div class="rate"><?php echo sprintf(__('Click rate %s', 'customer-reviews-collector-for-woocommerce'), $statistics['click-rate'][ $key ] . '%'); ?></div><br />
<div class="rate"><?php echo sprintf(__('Click to open rate %s', 'customer-reviews-collector-for-woocommerce'), $statistics['click-open-rate'][ $key ] . '%'); ?></div>
</div>
</div>
</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
<div class="row">
<div class="col-md plugin-subtitle"><?php echo __('Last 30 days clicks', 'customer-reviews-collector-for-woocommerce'); ?></div>
</div>
<div class="card">
<div class="card-body">
<canvas id="ti-collector-graph-chart"></canvas>
<script type="text/javascript">
jQuery(document).ready(function() {
<?php $graph = $statistics['graph']; ?>
if (jQuery('#ti-collector-graph-chart').length) {
var chart = jQuery("#ti-collector-graph-chart");
let data = {
labels: <?php echo json_encode(array_keys($graph)); ?>,
datasets: [{
label: "positive",
backgroundColor: 'rgba(34, 180, 132, 0.65)',
borderWidth: 0,
data: <?php echo json_encode(array_column($graph, 'positive')); ?>,
stack: 0,
},{
label: "negative",
backgroundColor: 'rgba(217, 83, 79, 0.65)',
borderColor: '#ccc',
borderWidth: 1,
data: <?php echo json_encode(array_column($graph, 'negative')); ?>,
stack: 0,
}]
};
new Chart(chart, {
type: 'bar',
data: data,
options: {
responsive: true,
legend: { display: false },
tooltips: {
position: 'nearest',
mode: 'index',
displayColors: false
},
scales: {
yAxes: [{
ticks: {
stepSize: 1,
beginAtZero: true,
max: <?php
$clicks = array_column($graph, 'clicks');
echo ($clicks ? max($clicks) : 0) + 1;
?>
}
}]
}
}
});
}
});
</script>
</div>
</div>
<div class="card card-transparent">
<div class="card-body">
<div class="widget-promo">
<h2 class="text-center"><?php echo __('Display your reviews on your website fast and simple', 'customer-reviews-collector-for-woocommerce'); ?></h2>
<p class="size-16 text-center">
<?php echo __("Use our professional widgets on your website and gain your customer's trust!", 'customer-reviews-collector-for-woocommerce'); ?><br />
<?php echo sprintf(__('Choose from %d layouts and %d pre-designed styles.', 'customer-reviews-collector-for-woocommerce'), 40, 25); ?></p>
</div>
<div class="widget-container mb-2">
<div src='https://cdn.trustindex.io/loader.js?67a57e27931285361b531faf66'></div>
<div src='https://cdn.trustindex.io/loader.js?99b1cd77964b8521e65d94ae3e'></div>
</div>
<div class="btn-container center">
<a href="https://www.trustindex.io/ti-redirect.php?a=sys&c=wc-collect-1" target="_blank" class="btn btn-primary"><?php echo __('Create a free Trustindex account', 'customer-reviews-collector-for-woocommerce'); ?></a>
</div>
</div>
</div>
<?php endif; ?>
</div>
<?php if (!$rateUsFeedback): ?>
<div class="modal fade ti-rateus-modal" id="ti-rateus-modal-feedback" data-bs="5" tabindex="-1" role="dialog">
<div class="modal-dialog">
<div class="modal-content">
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __('Close', 'customer-reviews-collector-for-woocommerce'); ?>"></button>
<div class="modal-body">
<div class="ti-rating-textbox">
<div class="ti-quick-rating">
<?php for ($i = 5; $i >= 1; $i--): ?><div class="ti-star-check" data-value="<?php echo $i; ?>"></div><?php endfor; ?>
<div class="clear"></div>
</div>
</div>
<div class="ti-rateus-title"><?php echo __('Thanks for your feedback!<br />Let us know how we can improve.', 'customer-reviews-collector-for-woocommerce') ;?></div>
<input type="text" class="form-control" placeholder="<?php echo __('Contact e-mail', 'customer-reviews-collector-for-woocommerce') ;?>" value="<?php echo $current_user->user_email; ?>" />
<textarea class="form-control" placeholder="<?php echo __('Describe your experience', 'customer-reviews-collector-for-woocommerce') ;?>"></textarea>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-light" data-bs-dismiss="modal"><?php echo __('Close', 'customer-reviews-collector-for-woocommerce'); ?></button>
<button type="button" data-nonce="<?php echo wp_create_nonce('ti-rate-us'); ?>" class="btn btn-primary btn-rateus-support"><?php echo __('Contact our support', 'customer-reviews-collector-for-woocommerce'); ?></button>
</div>
</div>
</div>
</div>
<?php endif; ?>