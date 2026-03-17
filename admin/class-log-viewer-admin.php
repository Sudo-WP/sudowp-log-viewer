<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Log_Viewer_Admin
 *
 * Main class for admin functionality.
 */
class Log_Viewer_Admin {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.2.0';

	/**
	 * Plugin version short.
	 *
	 * @var string
	 */
	const VERSION_SHORT = '1.2.0';

	/**
	 * Unique identifier / text domain.
	 *
	 * @var string
	 */
	protected string $plugin_slug = 'sudowp-log-viewer';

	/**
	 * Singleton instance.
	 *
	 * @var Log_Viewer_Admin|null
	 */
	protected static ?Log_Viewer_Admin $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @var string|null
	 */
	protected ?string $plugin_screen_hook_suffix = null;

	/**
	 * @var Files_View_Page|null
	 */
	private ?Files_View_Page $_files_view_page = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
	}

	/**
	 * Return a singleton instance or false if unauthorized.
	 *
	 * @return false|Log_Viewer_Admin
	 */
	public static function get_instance() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Enqueue admin stylesheet on the Log Viewer page only.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue_admin_styles( string $hook_suffix ): void {
		if ( null === $this->_files_view_page ) {
			return;
		}

		if ( $hook_suffix !== $this->_files_view_page->_hook_name ) {
			return;
		}

		wp_enqueue_style(
			'sudowp-log-viewer-admin',
			plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/admin.css',
			array(),
			self::VERSION
		);
	}

	/**
	 * Wrapper for getting Files View Page.
	 *
	 * @return Files_View_Page
	 */
	public function get_Files_View_Page(): Files_View_Page {
		if ( null === $this->_files_view_page ) {
			require_once __DIR__ . '/includes/class-user-options.php';
			require_once __DIR__ . '/includes/class-files-view-page.php';
			$this->_files_view_page = new Files_View_Page( realpath( __DIR__ . DIRECTORY_SEPARATOR . 'views' ) );
		}

		return $this->_files_view_page;
	}

	/**
	 * Register the administration menu.
	 */
	public function add_plugin_admin_menu(): void {
		$this->get_Files_View_Page();
	}

	/**
	 * Returns an array of log filenames relative to WP_CONTENT_DIR.
	 *
	 * @return array<string>
	 */
	public static function getFiles(): array {
		$content_dir = realpath( WP_CONTENT_DIR );
		if ( false === $content_dir ) {
			return array();
		}

		$path    = $content_dir . DIRECTORY_SEPARATOR . '*.log';
		$replace = $content_dir . DIRECTORY_SEPARATOR;
		$files   = array();

		foreach ( array_reverse( glob( $path ) ) as $file ) {
			$files[] = str_replace( $replace, '', $file );
		}

		return $files;
	}

	/**
	 * Resolves a relative filename to its real path within WP_CONTENT_DIR.
	 *
	 * Returns false if the resolved path escapes the content directory boundary.
	 *
	 * @param string $file Filename relative to WP_CONTENT_DIR.
	 * @return string|false
	 */
	public static function transformFilePath( string $file ) {
		$boundary = realpath( WP_CONTENT_DIR );
		if ( false === $boundary ) {
			return false;
		}

		$resolved = realpath( $boundary . DIRECTORY_SEPARATOR . $file );
		if ( false === $resolved || strpos( $resolved, $boundary . DIRECTORY_SEPARATOR ) !== 0 ) {
			return false;
		}

		return $resolved;
	}
}
