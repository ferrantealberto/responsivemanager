<?php
require_once '/Volumes/Macintosh HD/Users/Copia Disco L PC/orma-store/wordpress/wp-load.php';
global $wpdb;
$table_name = $wpdb->prefix . 'rem_element_configs';
echo $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
?>
