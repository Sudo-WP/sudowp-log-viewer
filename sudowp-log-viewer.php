<?php
declare(strict_types=1);
/**
 * Plugin Name:       SudoWP Log Viewer
 * Plugin URI:        https://sudowp.com/blog/sudowp-log-viewer-security-patch/
 * Description:       A security-hardened fork of the abandoned Log Viewer plugin by Markus Fischbacher. Fixes unescaped log output, path traversal, and PHP 8.x compatibility issues.
 * Version:           1.2.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            SudoWP
 * Author URI:        https://sudowp.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sudowp-log-viewer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ENABLE_DEBUGBAR_INTEGRATION' ) ) {
	define( 'ENABLE_DEBUGBAR_INTEGRATION', true );
}

if ( defined( 'ENABLE_DEBUGBAR_INTEGRATION' ) && ENABLE_DEBUGBAR_INTEGRATION === true ) {
	require_once plugin_dir_path( __FILE__ ) . '/includes/class-debugbar-integration.php';
	add_filter( 'debug_bar_panels', array( 'Log_Viewer_DebugBar_Integration', 'integrate_debugbar' ) );
}

if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once plugin_dir_path( __FILE__ ) . '/admin/class-log-viewer-admin.php';
	add_action( 'plugins_loaded', array( 'Log_Viewer_Admin', 'get_instance' ) );

}
