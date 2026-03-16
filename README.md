![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-blue)
![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green)
![WordPress 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-blue)
![Status: Security Patched](https://img.shields.io/badge/Status-Security%20Patched-orange)

# SudoWP Log Viewer

> **Security Notice:** The original [Log Viewer](https://wordpress.org/plugins/log-viewer/) plugin by Markus Fischbacher was closed on WordPress.org on February 21, 2022 due to a security issue. No patch was released by the original author. This fork addresses the identified vulnerabilities and modernizes the codebase for PHP 8.0+ compatibility.

SudoWP Log Viewer is a security-hardened fork of the abandoned Log Viewer WordPress plugin. It provides an easy way to view `*.log` files directly in the WordPress admin panel.

## Features

- View any `.log` file in `wp-content/` from the WordPress admin Tools menu
- File actions: delete, empty, or append a break separator to log files
- Autorefresh: automatically reload the page every 15 seconds (configurable per-user)
- FIFO/FILO display order preference
- Optional Debug Bar panel integration

## Security Patches in This Fork

- **Stored XSS fix:** All log file content output is now escaped with `esc_html()` to prevent JavaScript injection via crafted log entries
- **Path traversal fix:** File selection is validated against a strict allowlist of discovered `.log` files, with `realpath()` boundary enforcement
- **Debug statement removal:** Removed `var_dump()`/`die()` block that broke all file actions and leaked server paths
- **Authorization hardening:** Added `current_user_can( 'manage_options' )` check to the Debug Bar panel render path
- **Access control fix:** Replaced `is_super_admin()` with `current_user_can( 'manage_options' )` for consistent capability-based access control
- **JavaScript hardening:** Replaced `setTimeout` string eval with a proper function callback
- **PHP 8.0+ compatibility:** Fixed fatal error caused by PHP 4-style parent constructor call in the Debug Bar panel class
- **Clean uninstall:** Plugin now removes all user settings from `wp_options` on deletion

For the full audit report, see [SECURITY_AUDIT_SUMMARY.md](SECURITY_AUDIT_SUMMARY.md).

## Installation

**Important:** Deactivate and delete the original Log Viewer plugin before installing this fork. Both plugins cannot be active at the same time.

1. Upload the `sudowp-log-viewer` folder to `/wp-content/plugins/`.
2. Activate through the Plugins menu in WordPress.
3. Navigate to **Tools > Log Viewer** to view log files.

To enable WordPress debug logging, add the following to your `wp-config.php`:

```php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
```

## Requirements

- WordPress 6.0 or higher
- PHP 8.0 or higher

## Known Limitations

- Log file scanning is limited to `*.log` files in the top level of `wp-content/`. Subdirectories are not scanned.
- The Debug Bar integration requires the [Debug Bar](https://wordpress.org/plugins/debug-bar/) plugin.
- Autorefresh interval is fixed at 15 seconds.

## Changelog

### 1.1.1

- Bug fix: Prevented fatal `TypeError` on sites with no log files present in `wp-content/`. `view_page()` and Debug Bar `render()` now guard against `false` return from `getCurrentFile()` before calling `transformFilePath()`.

### 1.1.0

- Security: Fixed stored XSS via unescaped log file content output
- Security: Fixed path traversal via absolute path injection in file parameter
- Security: Removed `var_dump()`/`die()` debug statement that broke file actions and leaked server paths
- Security: Added capability check to Debug Bar panel render path
- Security: Replaced `is_super_admin()` with `current_user_can( 'manage_options' )`
- Hardening: Replaced `setTimeout` string eval with function callback
- Hardening: Fixed user option key construction inconsistency
- Hardening: Added proper `uninstall.php` cleanup for `wp_options` records
- Compatibility: Fixed PHP 8.0 fatal error from PHP 4-style parent constructor call
- Compatibility: Added `declare(strict_types=1)` to all PHP files
- Compatibility: Declared all class properties with explicit types

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

## Reporting Security Issues

See [SECURITY.md](SECURITY.md) for our security policy and reporting instructions.
