<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log_Viewer_DebugBar_Integration {

	/**
	 * Integrates with the Debug Bar plugin as a panel.
	 *
	 * @param array $panels
	 * @return array
	 */
	public static function integrate_debugbar( array $panels ): array {
		require_once plugin_dir_path( __DIR__ ) . '/admin/class-log-viewer-admin.php';
		require_once plugin_dir_path( __DIR__ ) . '/includes/class-dbpanel.php';

		$myPanel  = new Log_Viewer_DebugBar_Panel( plugin_dir_path( __DIR__ ) . '/views' );
		$panels[] = $myPanel;

		return $panels;
	}
}
