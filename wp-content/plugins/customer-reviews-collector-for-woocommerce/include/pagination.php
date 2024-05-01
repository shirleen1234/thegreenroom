<?php
$paginationLinks = [
[
'text' => __('Previous', 'customer-reviews-collector-for-woocommerce'),
'page' => $listPageIndex - 1,
'disabled' => $listPageIndex <= 1
]
];
if ($results->maxNumPages < 9) {
for ($i = 1; $i <= $results->maxNumPages; $i++) {
$paginationLinks []= [
'page' => $i,
'active' => $listPageIndex === $i
];
}
}
else if ($results->maxNumPages > 7) {
if ($listPageIndex <= 4) {
for ($i = 1; $i < 6; $i++) {
$paginationLinks []= [
'page' => $i,
'active' => $listPageIndex === $i,
];
}
$paginationLinks []= [ 'text' => '...', 'disabled' => true ];
$paginationLinks []= [ 'page' => $results->maxNumPages ];
}
else if ($results->maxNumPages - 3 > $listPageIndex && $listPageIndex > 2) {
$paginationLinks []= [ 'page' => 1 ];
$paginationLinks []= [ 'text' => '...', 'disabled' => true ];
for ($i = $listPageIndex - 1; $i <= $listPageIndex + 1; $i++) {
$paginationLinks []= [
'page' => $i,
'active' => $listPageIndex === $i,
];
}
$paginationLinks []= [ 'text' => '...', 'disabled' => true ];
$paginationLinks []= [ 'page' => $results->maxNumPages ];
}
else {
$paginationLinks []= [ 'page' => 1 ];
$paginationLinks []= [ 'text' => '...', 'disabled' => true ];
for ($i = $results->maxNumPages - 4; $i <= $results->maxNumPages; $i++) {
$paginationLinks []= [
'page' => $i,
'active' => $listPageIndex === $i,
];
}
}
}
$paginationLinks []= [
'text' => __('Next', 'customer-reviews-collector-for-woocommerce'),
'page' => $listPageIndex + 1,
'disabled' => $listPageIndex >= $results->maxNumPages
];
?>
<nav>
<ul class="pagination justify-content-end">
<?php foreach ($paginationLinks as $link): ?>
<li class="page-item<?php if (isset($link['disabled']) && $link['disabled']): ?> disabled<?php endif; ?><?php if (isset($link['active']) && $link['active']): ?> active<?php endif; ?>">
<?php if (isset($link['disabled']) && $link['disabled']): ?>
<a class="page-link " href="#" tabindex="-1" aria-disabled="true">
<?php else: ?>
<a class="page-link " href="<?php echo esc_url(preg_replace('/&pi=\d+/', '&pi='. $link['page'], $pageUrl)); ?>">
<?php endif; ?>
<?php echo esc_html(isset($link['text']) ? $link['text'] : $link['page']); ?>
</a>
</li>
<?php endforeach; ?>
</ul>
</nav>