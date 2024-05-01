<?php
// Add custom Theme Functions here

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css', false, '1.3.0' );
});

add_action( 'init', function () {
	flatsome_register_follow_link('xiaohongshu', 'Xiaohongshu', array(
        'icon'     => '<i class="ico-xiaohongshu"></i>',
		'priority' => 50,
	));
});