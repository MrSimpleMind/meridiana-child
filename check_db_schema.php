<?php
require_once('../../../wp-load.php'); // Adjust path as needed

global $wpdb;
$table_name = $wpdb->prefix . 'document_views';

$sql = "DESCRIBE $table_name;";
$results = $wpdb->get_results($sql);

if ($results) {
    echo "Schema for table {$table_name}:\n";
    foreach ($results as $column) {
        echo "  " . $column->Field . " (" . $column->Type . ")\n";
    }
} else {
    echo "Table {$table_name} not found or no columns.\n";
    if ($wpdb->last_error) {
        echo "Database error: " . $wpdb->last_error . "\n";
    }
}
?>
