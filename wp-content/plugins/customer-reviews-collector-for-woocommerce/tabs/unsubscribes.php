<?php
defined('ABSPATH') or die('No script kiddies please!');
$listPageIndex = isset($_REQUEST['pi']) ? (int)$_REQUEST['pi'] : 1;
$listPageTerm = isset($_REQUEST['q']) ? sanitize_text_field($_REQUEST['q']) : "";
$pageUrl = "?page=$_page&tab=$_tab&pi=$listPageIndex&q=$listPageTerm";
if (isset($_GET['cancel'])) {
check_admin_referer('ti-unsubscribe-cancel');
$id = (int)$_GET['cancel'];
$wpdb->delete($pluginManagerInstance->get_tablename('unsubscribes'), [ 'id' => $id ]);
header('Location: '. $pageUrl);
exit;
}
$results = $pluginManagerInstance->get_unsubscribes($listPageIndex, $listPageTerm);
$resultsTotal = $listPageTerm ? $pluginManagerInstance->get_unsubscribes()->total : $results->total;
?>
<div class="plugin-head"><?php echo __('Unsubscribes', 'customer-reviews-collector-for-woocommerce'); ?></div>
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
<th scope="col"><?php echo __('E-mail', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 200px"><?php echo __('Created date', 'customer-reviews-collector-for-woocommerce'); ?></th>
<th scope="col" style="width: 100px" class="text-center"><?php echo __('Cancel', 'customer-reviews-collector-for-woocommerce'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($results->unsubscribes as $unsubscribe): ?>
<tr>
<td><?php echo esc_html($unsubscribe->email); ?></td>
<td><?php echo date('Y-m-d H:i:s', strtotime($unsubscribe->created_at)); ?></td>
<td class="text-center">
<a href="<?php echo wp_nonce_url($pageUrl .'&cancel='. $unsubscribe->id, 'ti-unsubscribe-cancel'); ?>" class="btn btn-danger btn-sm ti-btn-loading-on-click">
<span class="dashicons dashicons-trash"></span>
</a>
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