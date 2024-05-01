<?php
defined('ABSPATH') or die('No script kiddies please!');
$listPageIndex = isset($_REQUEST['pi']) ? (int)$_REQUEST['pi'] : 1;
$listPageTerm = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : "";
$pageUrl = "?page=$_page&tab=$_tab&pi=$listPageIndex&q=$listPageTerm";
if (isset($_GET['cancel'])) {
check_admin_referer('ti-invite-cancel');
$id = (int)$_GET['cancel'];
$wpdb->delete($pluginManagerInstance->get_tablename('schedule_list'), [ 'id' => $id ]);
header('Location: '. $pageUrl);
exit;
}
else if (isset($_GET['stop'])) {
$id = (int)$_GET['stop'];
$wpdb->update($pluginManagerInstance->get_tablename('schedule_list'), [ 'timestamp' => 0 ], [ 'id' => $id ]);
header('Location: '. $pageUrl);
exit;
}
else if (isset($_GET['send'])) {
$id = (int)$_GET['send'];
$timestamp = time();
if ($trigger_delay = (int)get_option($pluginManagerInstance->get_option_name('trigger-delay'), $pluginManagerInstance->get_default_settings()['trigger-delay'])) {
$timestamp = strtotime($pluginManagerInstance->get_schedule($id)->created_at) + ($trigger_delay * 86400);
}
$wpdb->update($pluginManagerInstance->get_tablename('schedule_list'), [ 'timestamp' => $timestamp ], [ 'id' => $id ]);
header('Location: '. $pageUrl);
exit;
}
$results = $pluginManagerInstance->get_schedules($listPageIndex, $listPageTerm);
$resultsTotal = $listPageTerm ? $pluginManagerInstance->get_schedules()->total : $results->total;
?>
<div class="plugin-head"><?php echo __('Requests', 'customer-reviews-collector-for-woocommerce'); ?></div>
<div class="plugin-body table-list">
<?php if ($resultsTotal): ?>
<form class="row justify-content-end">
<input type="hidden" name="page" value="<?php echo esc_attr($_page); ?>" />
<input type="hidden" name="tab" value="<?php echo esc_attr($_tab); ?>" />
<input type="hidden" name="pi" value="1" />
<div class="col-3">
<input type="text" class="form-control" autofocus name="q" value="<?php echo esc_attr($listPageTerm); ?>" placeholder="<?php echo __('Search', 'customer-reviews-collector-for-woocommerce'); ?>" />
</div>
<div class="col-2 col-sm-auto">
<button class="btn btn-primary ti-btn-loading-on-click"><?php echo __('Search', 'customer-reviews-collector-for-woocommerce'); ?></button>
</div>
</form>
<?php endif; ?>
<?php if ($results->total): ?>
<div class="table-container">
<table class="table ti-table">
<thead>
<tr>
<th scope="col" style="width: 75px"><?php echo __('Status', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col"><?php echo __('Name', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 210px"><?php echo __('E-mail', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 115px"><?php echo __('Order id', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 165px"><?php echo __('Created date', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 165px"><?php echo __('Send date', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 100px"></th>
</tr>
</thead>
<tbody>
<?php foreach ($results->schedules as $schedule): ?>
<tr>
<td>
<?php if ($schedule->sent): ?>
<span class="dashicons dashicons-saved text-success" data-toggle="tooltip" title="<?php echo __('E-mail sent', 'customer-reviews-collector-for-woocommerce'); ?>"></span>
<?php elseif (!$schedule->timestamp || !$pluginManagerInstance->is_campaign_active()): ?>
<span class="dashicons dashicons-remove text-danger" data-toggle="tooltip" title="<?php echo __('Cancelled', 'customer-reviews-collector-for-woocommerce'); ?>"></span>
<?php elseif ($schedule->timestamp <= time()): ?>
<span class="ti-loading-icon text-info" data-toggle="tooltip" title="<?php echo __('Queued for sending', 'customer-reviews-collector-for-woocommerce'); ?>"></span>
<?php else: ?>
<span class="dashicons dashicons-clock text-warning" data-toggle="tooltip" title="<?php echo __('Timed sending', 'customer-reviews-collector-for-woocommerce'); ?>"></span>
<?php endif; ?>
</td>
<td><?php echo esc_html($schedule->name); ?></td>
<td><?php echo esc_html($schedule->email); ?></td>
<td>
<?php if (wc_get_order($schedule->order_id)): ?>
<a href="<?php echo esc_url('post.php?post='. $schedule->order_id .'&action=edit'); ?>" target="_blank"><?php echo esc_html($schedule->order_id); ?></a>
<?php else: ?>
<i><?php echo __('Not exists', 'customer-reviews-collector-for-woocommerce'); ?></i>
<?php endif; ?>
</td>
<td><?php echo date('Y-m-d H:i:s', strtotime($schedule->created_at)); ?></td>
<td><?php echo $schedule->timestamp ? date('Y-m-d H:i:s', $schedule->timestamp) : ''; ?></td>
<td class="text-center">
<?php if (!$schedule->sent): ?>
<?php if ($pluginManagerInstance->is_campaign_active()): ?>
<?php if (!$schedule->timestamp): ?>
<a href="<?php echo wp_nonce_url($pageUrl .'&send='. $schedule->id, 'ti-invite-send'); ?>" class="btn btn-success btn-sm ti-btn-loading-on-click" data-toggle="tooltip" title="<?php echo __('Send', 'customer-reviews-collector-for-woocommerce'); ?>">
<span class="dashicons dashicons-email"></span>
</a>
<?php else: ?>
<a href="<?php echo wp_nonce_url($pageUrl .'&stop='. $schedule->id, 'ti-invite-stop'); ?>" class="btn btn-warning btn-sm ti-btn-loading-on-click" data-toggle="tooltip" title="<?php echo __('Stop', 'customer-reviews-collector-for-woocommerce'); ?>">
<span class="dashicons dashicons-remove"></span>
</a>
<?php endif; ?>
<?php endif; ?>
<a href="<?php echo wp_nonce_url($pageUrl .'&cancel='. $schedule->id, 'ti-invite-cancel'); ?>" class="btn btn-danger btn-sm ti-btn-loading-on-click" data-toggle="tooltip" title="<?php echo __('Cancel', 'customer-reviews-collector-for-woocommerce'); ?>">
<span class="dashicons dashicons-trash"></span>
</a>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php if ($results->maxNumPages > 1): ?>
<?php include($pluginManagerInstance->get_plugin_dir() . 'include' . DIRECTORY_SEPARATOR . 'pagination.php'); ?>
<?php endif; ?>
<?php else: ?>
<div class="alert alert-warning">
<p><?php echo __('List is empty.', 'customer-reviews-collector-for-woocommerce'); ?></p>
</div>
<?php endif; ?>
</div>