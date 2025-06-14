<?php
require_once '/Volumes/Macintosh HD/Users/Copia Disco L PC/orma-store/wordpress/wp-load.php';
global $wpdb;
$tables = $wpdb->get_results("SHOW TABLES");
foreach ($tables as $table) {
    echo $table->{'Tables_in_' . $wpdb->dbname} . "\n";
}
?>
