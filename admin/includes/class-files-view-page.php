<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Files_View_Page
 *
 * Controller for the Files View admin page.
 */
class Files_View_Page {

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTION_SUBMIT = 'fileactions';

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTION_ACTION = 'action';

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTION_SCROLLTO = 'scrollto';

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTION_FILE = 'file';

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTIONS_DUMP = 'dump';

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTIONS_EMPTY = 'empty';

	/**
	 * @var string
	 */
	private static string $_KEYS_FILEACTIONS_BREAK = 'break';

	/**
	 * @var string
	 */
	private static string $_KEYS_VIEWFIELDS_SUBMIT = 'viewfields';

	/**
	 * @var string
	 */
	public static string $ACTIONS_VIEWOPTIONS_CHANGED = 'ViewOptions_Changed';

	/**
	 * @var string|false
	 */
	private $_currentFile = false;

	/**
	 * The hook name for this page.
	 *
	 * @var string|null
	 */
	public ?string $_hook_name = null;

	/**
	 * @var WP_Screen|null
	 */
	protected ?WP_Screen $_wpScreen = null;

	/**
	 * @var string
	 */
	private static string $_parent_slug = 'tools.php';

	/**
	 * @var string
	 */
	private string $_page_title = 'Files View';

	/**
	 * @var string
	 */
	private string $_menu_title = 'Log Viewer';

	/**
	 * @var string
	 */
	private string $_capability = 'manage_options';

	/**
	 * @var string
	 */
	private static string $_menu_slug = 'log_viewer_files_view';

	/**
	 * @var string
	 */
	private string $_view_file = 'files-view.php';

	/**
	 * @return WP_Screen
	 */
	public function getWPScreen(): WP_Screen {
		if ( ! $this->_wpScreen ) {
			$this->_wpScreen = WP_Screen::get( $this->_hook_name );
		}

		return $this->_wpScreen;
	}

	/**
	 * Returns the admin_url for this page.
	 *
	 * @return string
	 */
	public static function getPageUrl(): string {
		$url  = admin_url( self::$_parent_slug, 'admin' );
		$url .= '?page=' . self::$_menu_slug;

		return $url;
	}

	/**
	 * @param string $view_path View templates directory.
	 */
	public function __construct( string $view_path = '' ) {
		$this->_hook_name = add_submenu_page(
			self::$_parent_slug,
			$this->_page_title,
			$this->_menu_title,
			$this->_capability,
			self::$_menu_slug,
			array( $this, 'view_page' )
		);

		$this->_view_file = realpath( $view_path . DIRECTORY_SEPARATOR . $this->_view_file );

		add_action( self::$ACTIONS_VIEWOPTIONS_CHANGED, array( 'User_Options', 'updateUserOptions' ) );
	}

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
	 * Returns escaped content of the current file.
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

		if ( User_Options::LINEOUTPUTORDER_FILO === User_Options::getLineOutputOrder() ) {
			$lines = file( $path );
			$content = ( false !== $lines ) ? implode( '', array_reverse( $lines ) ) : '';
		} else {
			$content = file_get_contents( $path, false );
			if ( false === $content ) {
				$content = '';
			}
		}

		return $content;
	}

	/**
	 * Deletes the given file.
	 *
	 * @param string $file Absolute file path.
	 * @return bool|int
	 */
	private function _dumpFile( string $file ) {
		if ( ! is_writable( $file ) ) {
			return -1;
		}

		$result = unlink( $file );
		if ( true === $result ) {
			$this->_currentFile = false;
		}

		return $result;
	}

	/**
	 * Truncates the given file to zero bytes.
	 *
	 * @param string $file Absolute file path.
	 * @return bool|int
	 */
	private function _emptyFile( string $file ) {
		if ( ! is_writable( $file ) ) {
			return -1;
		}

		$handle = fopen( $file, 'w' );
		if ( ! $handle ) {
			return -2;
		}

		return fclose( $handle );
	}

	/**
	 * Appends a break separator to the given file.
	 *
	 * @param string $file Absolute file path.
	 * @return bool|int
	 */
	private function _appendBreak( string $file ) {
		if ( ! is_writable( $file ) ) {
			return -1;
		}

		$handle = fopen( $file, 'a' );
		if ( ! $handle ) {
			return -2;
		}

		fwrite( $handle, '------------------------' );

		return fclose( $handle );
	}

	/**
	 * Handles file actions from POST submission.
	 *
	 * @param string $fileaction Action to handle.
	 * @param string $file       Relative filename.
	 * @return $this|bool|int
	 */
	private function _handle_fileaction( string $fileaction, string $file ) {
		$files = Log_Viewer_Admin::getFiles();
		if ( ! in_array( $file, $files, true ) ) {
			wp_die( 'Invalid file.', 403 );
		}

		$realfile = Log_Viewer_Admin::transformFilePath( $file );
		if ( false === $realfile ) {
			wp_die( 'Invalid file path.', 403 );
		}

		switch ( $fileaction ) {
			case self::$_KEYS_FILEACTIONS_DUMP:
				$this->_dumpFile( $realfile );
				unset(
					$_POST[ self::$_KEYS_FILEACTION_ACTION ],
					$_POST[ self::$_KEYS_FILEACTION_FILE ],
					$_POST[ self::$_KEYS_FILEACTION_SUBMIT ],
					$_POST[ self::$_KEYS_FILEACTION_SCROLLTO ],
					$_REQUEST['file']
				);
				$this->_currentFile = false;
				break;

			case self::$_KEYS_FILEACTIONS_EMPTY:
				return $this->_emptyFile( $realfile );

			case self::$_KEYS_FILEACTIONS_BREAK:
				return $this->_appendBreak( $realfile );

			default:
				break;
		}

		return $this;
	}

	/**
	 * Renders the page view with POST action handling.
	 */
	public function view_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized.', 403 );
		}

		if ( array_key_exists( self::$_KEYS_FILEACTION_SUBMIT, $_POST ) && check_admin_referer( 'actions_nonce', 'actions_nonce' ) ) {
			$file       = sanitize_text_field( wp_unslash( $_POST[ self::$_KEYS_FILEACTION_FILE ] ) );
			$fileaction = sanitize_text_field( wp_unslash( $_POST[ self::$_KEYS_FILEACTION_ACTION ] ) );

			$result = $this->_handle_fileaction( $fileaction, $file );

			unset( $file, $fileaction );
		}

		if ( array_key_exists( self::$_KEYS_VIEWFIELDS_SUBMIT, $_POST ) && check_admin_referer( 'viewoptions_nonce', 'viewoptions_nonce' ) ) {
			$viewoptions = array(
				User_Options::KEYS_AUTOREFRESH => array_key_exists( User_Options::KEYS_AUTOREFRESH, $_POST ) ? 1 : 0,
			);
			if ( array_key_exists( User_Options::KEYS_LINEOUTPUTORDER, $_POST ) ) {
				$val = (int) $_POST[ User_Options::KEYS_LINEOUTPUTORDER ];
				if ( in_array( $val, [ User_Options::LINEOUTPUTORDER_FIFO, User_Options::LINEOUTPUTORDER_FILO ], true ) ) {
					$viewoptions[ User_Options::KEYS_LINEOUTPUTORDER ] = $val;
				}
			}

			do_action( self::$ACTIONS_VIEWOPTIONS_CHANGED, $viewoptions );
		}

		$files           = Log_Viewer_Admin::getFiles();
		$showEditSection = true;

		if ( empty( $files ) ) {
			$showEditSection = false;
		}

		$currentFile = $this->getCurrentFile();
		$realfile    = ( false !== $currentFile ) ? Log_Viewer_Admin::transformFilePath( $currentFile ) : false;
		$writeable   = ( false !== $realfile ) ? is_writable( $realfile ) : false;

		include_once $this->_view_file;
	}
}
