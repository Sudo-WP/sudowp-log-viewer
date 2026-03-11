<?php
declare(strict_types=1);

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$results = $wpdb->get_results(
	"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '%\_log-viewer\_settings'",
	ARRAY_A
);

if ( is_array( $results ) ) {
	foreach ( $results as $row ) {
		delete_option( $row['option_name'] );
	}
}
