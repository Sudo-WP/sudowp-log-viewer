=== SudoWP Log Viewer ===
Contributors: sudowp, wprepublic
Tags: debug, log, admin, development, security
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.0
Stable tag: 1.1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This is SudoWP Log Viewer, a community-maintained and security-hardened fork of the
abandoned "Log Viewer" plugin by Markus Fischbacher.

== Description ==

This plugin provides an easy way to view any *.log files directly in the WordPress admin panel. You can perform simple actions like emptying a file or deleting it.

To activate WordPress logging to file, set `define( 'WP_DEBUG_LOG', true );` in your wp-config.php file.

This fork was created after the original plugin was closed on WordPress.org on February 21, 2022
due to a security issue. No patch was released by the original author. SudoWP Log Viewer
addresses the known vulnerabilities and modernizes the codebase for PHP 8.0+ compatibility.

= Why this fork? =

The original Log Viewer plugin was closed by WordPress.org on February 21, 2022.
Our code audit identified stored XSS via unescaped log output, path traversal via
absolute path injection, and missing authorization checks in the Debug Bar panel.

= Security Patches in SudoWP Edition =

* Fixed stored XSS: all log file content is now escaped with esc_html()
* Fixed path traversal: file selection uses strict allowlist validation with realpath() boundary check
* Removed var_dump()/die() debug statement that broke all file actions and leaked server paths
* Added current_user_can('manage_options') check to Debug Bar panel
* Replaced is_super_admin() with current_user_can('manage_options') consistently
* Replaced setTimeout string eval with proper function callback
* Fixed user option key construction inconsistency
* Added uninstall.php cleanup for wp_options records
* PHP 8.0+ compatibility: fixed fatal error from PHP 4-style constructor call

= Features =

* View any .log file in wp-content/ from the WordPress admin Tools menu
* File actions: delete, empty, or append a break separator
* Autorefresh page every 15 seconds (toggle per-user)
* FIFO/FILO display order preference
* Optional Debug Bar panel integration

= Known Limitations =

* Log file scanning is limited to *.log files in the top level of wp-content/
* Subdirectories are not scanned
* Autorefresh interval is fixed at 15 seconds
* Debug Bar integration requires the Debug Bar plugin

== Installation ==

Important: Deactivate and delete the original Log Viewer plugin before
installing this fork. Both plugins cannot be active at the same time.

1. Upload the `sudowp-log-viewer` folder to `/wp-content/plugins/`.
2. Activate through the Plugins menu in WordPress.
3. Navigate to Tools > Log Viewer to view log files.

== Frequently Asked Questions ==

= Why can I not see the Tools > Log Viewer menu entry? =

You need the `manage_options` capability, which is granted to administrators by default.
On multisite installations, you must be a Super Admin.

= How do I enable debug.log? =

Add `define( 'WP_DEBUG_LOG', true );` in your wp-config.php file. This is not recommended on production environments.

= Can I view log files in subdirectories? =

Not currently. Only *.log files in the top level of wp-content/ are scanned. This is a known limitation.

== Changelog ==

= 1.1.1 =
* Security: Prepared statement in uninstall.php for safe database queries.
* Hardening: Validation on lineoutputorder parameter.
* Hardening: Absolute require paths to prevent directory traversal in includes.

= 1.1.0 =
* Security: Fixed stored XSS via unescaped log file content output
* Security: Fixed path traversal via absolute path injection in file parameter
* Security: Removed var_dump()/die() debug statement that broke file actions and leaked server paths
* Security: Added capability check to Debug Bar panel render path
* Security: Replaced is_super_admin() with current_user_can('manage_options')
* Hardening: Replaced setTimeout string eval with function callback
* Hardening: Fixed user option key construction inconsistency
* Hardening: Added proper uninstall.php cleanup for wp_options records
* Compatibility: Fixed PHP 8.0 fatal error from PHP 4-style parent constructor call
* Compatibility: Added declare(strict_types=1) to all PHP files
* Compatibility: Declared all class properties with explicit types

== Upgrade Notice ==

= 1.1.0 =
Security release. All users of the original Log Viewer plugin should
switch to this fork immediately.
