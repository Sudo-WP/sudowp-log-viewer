# Security Audit Summary - SudoWP Log Viewer

## Original Plugin

| Field | Value |
|-------|-------|
| Plugin | Log Viewer |
| Author | Markus Fischbacher |
| WordPress.org slug | `log-viewer` |
| Last upstream version | `14.05.04` (build `14.05.04-1559`) |
| WordPress.org status | Closed February 21, 2022 - Security Issue |
| CVE | None assigned |

## Vulnerabilities Addressed

### Stored XSS via Unescaped Log Output - Critical

**Description:** An attacker could inject JavaScript into any `.log` file within `wp-content/` (for example, via a crafted User-Agent header in an HTTP request). When an administrator opened the Log Viewer admin page or Debug Bar panel, the malicious payload executed in their browser session.

**Affected code:** `admin/views/files-view.php`, `views/debug-bar-panel.php`, `getCurrentFileContent()` in both `Files_View_Page` and `Log_Viewer_DebugBar_Panel`.

**Root cause:** `file_get_contents()` output was echoed directly into a `<textarea>` element with no output escaping. Log files routinely contain attacker-controlled strings such as User-Agent headers and request URIs.

**Fix applied:** All `getCurrentFileContent()` output is wrapped in `esc_html()` before rendering. All other dynamic output in both templates is escaped with the appropriate WordPress escaping function (`esc_html()`, `esc_attr()`, `esc_url()`).

**OWASP mapping:** A03:2021 Injection

### Path Traversal via Absolute Path Injection - Critical

**Description:** An authenticated administrator could read any file on the server that the web server process had permission to access, including `wp-config.php` and `/etc/passwd`.

**Affected code:** `getCurrentFile()` in `Files_View_Page` and `Log_Viewer_DebugBar_Panel`, `transformFilePath()` in `Log_Viewer_Admin`.

**Root cause:** The `$_REQUEST['file']` parameter was passed through `stripslashes()` and WordPress core's `validate_file()`, but `validate_file()` only blocks `..` sequences and Windows drive letters. When the allowlist parameter was empty (no log files present), any absolute path like `/etc/passwd` passed validation. `transformFilePath()` prepended `WP_CONTENT_DIR . '/'` and called `realpath()`, but on Linux `/var/www/html/wp-content//etc/passwd` resolves to `/etc/passwd`.

**Fix applied:** Replaced `validate_file()` validation with strict `in_array()` allowlist check against `getFiles()` results. Added `realpath()` boundary check in `transformFilePath()` to verify the resolved path stays within `WP_CONTENT_DIR`. Any file parameter not in the allowlist defaults to the first available log file.

**OWASP mapping:** A01:2021 Broken Access Control

### Debug Statement Left in Production - Critical

**Description:** A `var_dump()` / `die()` block in `view_page()` halted execution on every file action POST and leaked full server filesystem paths to the browser.

**Affected code:** `admin/includes/class-files-view-page.php`, `view_page()` method.

**Root cause:** Debug code was committed to production and never removed. An `if ( isset( $file ) )` check followed by `var_dump()` and `die()` fired after every dump/empty/break action because the `$file` variable was defined in the preceding nonce-verified block.

**Fix applied:** Removed the entire debug block.

**OWASP mapping:** A05:2021 Security Misconfiguration

### Missing Authorization on Debug Bar Panel - High

**Description:** The Debug Bar panel rendered log file contents without any capability check. Any user who could access the Debug Bar (which can include non-administrators depending on configuration) could view log file contents.

**Affected code:** `includes/class-dbpanel.php`, `render()` method.

**Root cause:** No `current_user_can()` check was present in the render path.

**Fix applied:** Added `if ( ! current_user_can( 'manage_options' ) ) { return; }` at the top of the `render()` method.

**OWASP mapping:** A01:2021 Broken Access Control

### `is_super_admin()` Used as Access Gate - Medium

**Description:** In single-site WordPress installations, `is_super_admin()` returns true for any user with the administrator role. It does not respect the `DISALLOW_FILE_EDIT` constant, allowing broader access than intended.

**Affected code:** `admin/class-log-viewer-admin.php`, `__construct()` and `get_instance()`.

**Root cause:** The original plugin used `is_super_admin()` for its access control checks rather than a standard capability check.

**Fix applied:** Replaced all `is_super_admin()` calls with `current_user_can( 'manage_options' )`.

**OWASP mapping:** A01:2021 Broken Access Control

### `setTimeout` String Eval Anti-Pattern - Medium

**Description:** The autorefresh feature passed a string argument to `setTimeout()`, which is functionally equivalent to `eval()`. This is hostile to Content Security Policy headers and is a deprecated JavaScript practice.

**Affected code:** `admin/views/files-view.php`.

**Root cause:** Legacy JavaScript pattern: `setTimeout( "window.location.replace(document.URL);", ... )`.

**Fix applied:** Replaced with `setTimeout( function() { window.location.replace( window.location.href ); }, ... )`.

**OWASP mapping:** A03:2021 Injection

### User Option Key Inconsistency - Medium

**Description:** The `_loadUserOptions()` and `updateUserOptions()` methods constructed the `wp_options` key using different string interpolation patterns. While both produced the same result at runtime, the inconsistency made the code fragile and harder to audit.

**Affected code:** `admin/includes/class-user-options.php`.

**Root cause:** `_loadUserOptions()` used `sprintf( "%s_log-viewer_settings", $user->ID )` while `updateUserOptions()` used `sprintf( "%s%s", $user->ID, self::KEYS_IDENTIFIER )`.

**Fix applied:** Introduced a single private static `_buildOptionKey()` method used by both functions.

## PHP Compatibility Changes

- Replaced `parent::Debug_Bar_Panel( 'Log Viewer' )` (PHP 4-style constructor call, fatal error in PHP 8.0+) with `parent::__construct( 'Log Viewer' )`.
- Added `declare(strict_types=1)` to all PHP files.
- Declared all class properties with explicit types to prevent PHP 8.2 dynamic property deprecation warnings.

## Additional Hardening

- All `$_REQUEST`, `$_POST`, and `$_GET` input is now sanitized with `sanitize_text_field()` and `wp_unslash()`.
- The menu capability was changed from `edit_plugins` to `manage_options` for consistency with the access control model.
- The empty `uninstall.php` now properly cleans up all `*_log-viewer_settings` records from `wp_options` on plugin deletion.
- Replaced `@VERSION@` and `@VERSION_SHORT@` build token placeholders (never substituted by the upstream build system) with the actual version string `1.1.0`.

## Limitations and Caveats

- The upstream plugin has been abandoned since 2014. There is no vendor support and no expectation of upstream patches.
- Log file scanning is limited to `*.log` files in the top level of `WP_CONTENT_DIR`. Subdirectory scanning is not supported.
- The Debug Bar integration requires the separate Debug Bar plugin to be installed and active.
- User settings are stored in `wp_options` rather than user meta. On multisite installations, settings are stored per-site rather than per-network.
