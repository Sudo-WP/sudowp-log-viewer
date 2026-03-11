<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class User_Options
 *
 * Controls storage and access of per-user options.
 */
class User_Options {

	/**
	 * Unique string to identify option keys (appends to user ID).
	 */
	const KEYS_IDENTIFIER = '_log-viewer_settings';

	/**
	 * Line output order: First-In-First-Out.
	 */
	const LINEOUTPUTORDER_FIFO = 0;

	/**
	 * Line output order: First-In-Last-Out.
	 */
	const LINEOUTPUTORDER_FILO = 1;

	const KEYS_AUTOREFRESH         = 'autorefresh';
	const KEYS_AUTOREFRESHINTERVALL = 'arintervall';
	const KEYS_LINEOUTPUTORDER     = 'lineoutputorder';
	const KEYS_OPTIONSVERSION      = 'version';

	/**
	 * Cached options.
	 *
	 * @var array<string, mixed>|false
	 */
	private static $_options = false;

	/**
	 * Default option values.
	 *
	 * @var array<string, mixed>
	 */
	private static array $_defaultOptions = array(
		self::KEYS_AUTOREFRESH         => 1,
		self::KEYS_AUTOREFRESHINTERVALL => 15,
		self::KEYS_LINEOUTPUTORDER     => self::LINEOUTPUTORDER_FIFO,
		self::KEYS_OPTIONSVERSION      => '1.1.0',
	);

	/**
	 * Builds the wp_options key for the current user.
	 *
	 * @return string
	 */
	private static function _buildOptionKey(): string {
		$user = wp_get_current_user();
		return $user->ID . self::KEYS_IDENTIFIER;
	}

	/**
	 * @return int
	 */
	public static function getAutoRefresh(): int {
		if ( ! self::$_options ) {
			self::_loadUserOptions();
		}

		return (int) self::$_options[ self::KEYS_AUTOREFRESH ];
	}

	/**
	 * @param bool $enabled
	 */
	public static function setAutoRefresh( bool $enabled = true ): void {
		if ( ! self::$_options ) {
			self::_loadUserOptions();
		}

		self::$_options[ self::KEYS_AUTOREFRESH ] = $enabled ? 1 : 0;
	}

	/**
	 * @return int
	 */
	public static function getAutoRefreshIntervall(): int {
		if ( ! self::$_options ) {
			self::_loadUserOptions();
		}

		return (int) self::$_options[ self::KEYS_AUTOREFRESHINTERVALL ];
	}

	/**
	 * @return int
	 */
	public static function getLineOutputOrder(): int {
		if ( ! self::$_options ) {
			self::_loadUserOptions();
		}

		return (int) self::$_options[ self::KEYS_LINEOUTPUTORDER ];
	}

	/**
	 * Loads user options from the database.
	 */
	private static function _loadUserOptions(): void {
		if ( ! is_user_logged_in() ) {
			self::$_options = self::$_defaultOptions;
			return;
		}

		$key      = self::_buildOptionKey();
		$settings = get_option( $key, false );

		if ( false === $settings ) {
			add_option( $key, self::$_defaultOptions );
			$settings = self::$_defaultOptions;
		} elseif ( ! is_array( $settings ) || ! array_key_exists( self::KEYS_OPTIONSVERSION, $settings ) ) {
			update_option( $key, self::$_defaultOptions );
			$settings = self::$_defaultOptions;
		}

		self::$_options = $settings;
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function toArray(): array {
		if ( ! self::$_options ) {
			self::_loadUserOptions();
		}

		return self::$_options;
	}

	/**
	 * Saves options to the database.
	 *
	 * @param array<string, mixed> $newOptions
	 */
	public static function updateUserOptions( array $newOptions = array() ): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( empty( $newOptions ) ) {
			$newOptions = self::toArray();
		} else {
			$newOptions = wp_parse_args( $newOptions, self::toArray() );
		}

		$key        = self::_buildOptionKey();
		$oldOptions = get_option( $key, false );

		if ( $newOptions !== $oldOptions ) {
			update_option( $key, $newOptions );
			self::$_options = $newOptions;
		}
	}
}
