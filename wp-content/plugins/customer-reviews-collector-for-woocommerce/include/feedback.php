<!DOCTYPE html>
<html lang="">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.trustindex.io/assets/css/company-profile.css">
<link rel="stylesheet" href="<?php echo $trustindex_collector->get_plugin_file_url('assets/css/admin.css'); ?>">
<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
</head>
<body class="page write-review-page narrow-page">
<div class="wrapper">
<header class="ti-header">
<div class="container">
<div class="row align-items-center">
<div class="col-auto brand">
<a href="https://www.trustindex.io/" target="_blank">
<img class="footer-logo" src="https://cdn.trustindex.io/assets/platform/Trustindex/logo.svg" alt="Trustindex">
</a>
</div>
<div class="col-auto ml-auto header-text">
<a href="https://www.trustindex.io/" target="_blank" class="btn btn-primary">For Businesses</a>
</div>
</div>
</div>
</header>
<section class="company-header simple-profile-header">
<div class="container">
<div class="company-profile">
<div class="row profile align-items-center">
<div class="col">
<h1 class="card-title"><?php echo get_bloginfo('name'); ?></h1>
<h2 class="subtitle">
<a target="_blank" href="<?php echo get_site_url(); ?>"><?php echo preg_replace('/(https?:\/\/)?(www\.)?/', '', get_site_url()); ?></a>
</h2>
</div>
</div>
</div>
</div>
</section>
<div class="container">
<div class="row">
<main class="col">
<?php if (!$schedule['feedback']): ?>
<form action="" method="post" id="write-review">
<div class="card write-a-review">
<div class="card-body">
<h3 class="title"><?php echo __('Rate your experience', 'customer-reviews-collector-for-woocommerce'); ?></h3>
<div class="ti-quick-rating rating-<?php echo $rating; ?>">
<?php for ($i = 1; $i <= 5; $i++): ?>
<div class="star-check">
<div class="star"></div>
</div>
<?php endfor; ?>
</div>
</div>
</div>
<div class="card write-a-review active">
<div class="card-body">
<h3 class="title"><?php echo __('Send a message directly to our customer service team', 'customer-reviews-collector-for-woocommerce'); ?></h3>
<div class="form-group mb-4">
<textarea id="support-text" class="form-control write-a-review-text" rows="5" placeholder="<?php echo __('We are sorry that you had a bad experience. You can use this form to contact our customer service and give us an opportunity to resolve any problem or complaint you have before leaving a review.', 'customer-reviews-collector-for-woocommerce'); ?>"></textarea>
</div>
<h3 class="title"><?php echo __('Name', 'customer-reviews-collector-for-woocommerce'); ?></h3>
<div class="form-group mb-4">
<input type="text" class="form-control" value="<?php echo $schedule['name']; ?>" disabled />
</div>
<h3 class="title"><?php echo __('E-mail', 'customer-reviews-collector-for-woocommerce'); ?></h3>
<div class="form-group mb-4">
<input type="text" class="form-control" value="<?php echo $schedule['email']; ?>" disabled />
</div>
<div class="text-left mt-4">
<?php if ($isTest): ?>
<button class="btn btn-primary btn-lg btn-disabled" disabled style="pointer-events: none"><?php echo __('Send', 'customer-reviews-collector-for-woocommerce'); ?></button>
<?php else: ?>
<button class="btn btn-primary btn-submit btn-lg"><?php echo __('Send', 'customer-reviews-collector-for-woocommerce'); ?></button>
<?php endif; ?>
</div>
</div>
</div>
<div class="card write-a-review transparent mt-4">
<div class="card-body">
<?php if ($isTest): ?>
<a href="#" class="btn btn-outline-primary btn-sm disabled"><?php echo __('I prefer to write a negative review', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php else: ?>
<a href="<?php echo $reviewLink; ?>" class="btn btn-outline-primary btn-sm"><?php echo __('I prefer to write a negative review', 'customer-reviews-collector-for-woocommerce'); ?></a>
<?php endif; ?>
</div>
</div>
</form>
<?php endif; ?>
<div id="notification-success" class="card notification success"<?php if (!$schedule['feedback']): ?>style="display: none"<?php endif; ?>>
<div class="card-body"><?php echo __('Thank you for your feedback!', 'customer-reviews-collector-for-woocommerce'); ?></div>
</div>
</main>
</div>
</div>
</div>
<script type="text/javascript">
$(document).on('click', '.btn-submit', function(event) {
event.preventDefault();
let btn = $(this);
btn.blur();
let textarea = $('#support-text');
let text = textarea.val().trim();
textarea.removeClass('is-invalid');
if (text === "") {
return textarea.addClass('is-invalid').focus();
}
btn.addClass('btn-loading-animation');
$.post('', { text: text }, function() {
$('#write-review').hide();
$('#notification-success').fadeIn();
});
});
</script>
</body>
</html>