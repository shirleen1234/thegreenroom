<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
die;
}
require_once plugin_dir_path( __FILE__ ) . 'trustindex-collector-plugin.class.php';
$trustindex_collector = new TrustindexCollectorPlugin(__FILE__, "4.5");
$trustindex_collector->uninstall();
?>