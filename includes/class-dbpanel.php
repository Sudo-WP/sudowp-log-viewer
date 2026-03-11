<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Log_Viewer_DebugBar_Panel extends Debug_Bar_Panel {

	/**
	 * @var string
	 */
	private string $_view_file = 'debug-bar-panel.php';

	/**
	 * @var string|false
	 */
	private $_currentFile = false;

	/**
	 * Returns current/active filename validated against the allowlist.
	 *
	 * @return string|false
	 */
	public function getCurrentFile() {
		if ( false === $this->_currentFile ) {
			$files = Log_Viewer_Admin::getFiles();
			if ( empty( $files ) ) {
				return false;
			}

			if ( isset( $_REQUEST['file'] ) ) {
				$requested = sanitize_text_field( wp_unslash( $_REQUEST['file'] ) );
				if ( in_array( $requested, $files, true ) ) {
					$file = $requested;
				} else {
					$file = $files[0];
				}
			} else {
				$file = $files[0];
			}

			$this->_currentFile = $file;
		}

		return $this->_currentFile;
	}

	/**
	 * Returns content of the current file.
	 *
	 * @return string
	 */
	public function getCurrentFileContent(): string {
		if ( ! $this->getCurrentFile() ) {
			return '';
		}

		$path = Log_Viewer_Admin::transformFilePath( $this->getCurrentFile() );
		if ( false === $path ) {
			return '';
		}

		$content = file_get_contents( $path, false );

		return ( false !== $content ) ? $content : '';
	}

	/**
	 * @param string $view_path
	 */
	public function __construct( string $view_path = '' ) {
		parent::__construct( 'Log Viewer' );

		$this->_view_file = realpath( $view_path . DIRECTORY_SEPARATOR . $this->_view_file );
	}

	public function init(): bool {
		return true;
	}

	public function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		require_once plugin_dir_path( __DIR__ ) . '/admin/includes/class-files-view-page.php';

		$files           = Log_Viewer_Admin::getFiles();
		$showEditSection = true;

		if ( empty( $files ) ) {
			$showEditSection = false;
		}

		$realfile  = Log_Viewer_Admin::transformFilePath( $this->getCurrentFile() );
		$writeable = ( false !== $realfile ) ? is_writable( $realfile ) : false;

		include $this->_view_file;
	}
}
