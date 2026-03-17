<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_file     = $this->getCurrentFile();
$current_file_rel = $current_file ? esc_html( str_replace( realpath( ABSPATH ), '', $realfile ) ) : '';
$file_mtime       = ( false !== $realfile && is_file( $realfile ) ) ? filemtime( $realfile ) : false;
$file_size        = ( false !== $realfile && is_file( $realfile ) ) ? filesize( $realfile ) : false;

function sudowp_lv_format_bytes( int $bytes ): string {
	if ( $bytes >= 1048576 ) {
		return round( $bytes / 1048576, 2 ) . ' MB';
	}
	if ( $bytes >= 1024 ) {
		return round( $bytes / 1024, 1 ) . ' KB';
	}
	return $bytes . ' B';
}
?>

<?php
// Notices
if ( isset( $dumped ) ) :
	if ( $dumped ) : ?>
		<div class="sudowp-lv-notice sudowp-lv-notice-success"><?php esc_html_e( 'File deleted successfully.' ); ?></div>
		<?php
		// Re-check $showEditSection after dump
		$showEditSection = ! empty( $files );
	else : ?>
		<div class="sudowp-lv-notice sudowp-lv-notice-error"><?php esc_html_e( 'Could not delete file.' ); ?></div>
	<?php endif;
endif;

if ( isset( $handle ) ) :
	if ( ! $handle ) : ?>
		<div class="sudowp-lv-notice sudowp-lv-notice-error"><?php esc_html_e( 'Could not update file.' ); ?></div>
	<?php else : ?>
		<div class="sudowp-lv-notice sudowp-lv-notice-success"><?php esc_html_e( 'File updated successfully.' ); ?></div>
	<?php endif;
endif;

if ( ! $writeable && $showEditSection ) : ?>
	<div class="sudowp-lv-notice sudowp-lv-notice-warning">
		<?php printf(
			/* translators: %s: filename */
			esc_html__( 'File [%s] is not writeable. File actions are disabled.' ),
			esc_html( $current_file )
		); ?>
	</div>
<?php endif; ?>

<div id="sudowp-lv-wrap">

	<?php /* ---- Header ---- */ ?>
	<div id="sudowp-lv-header">
		<span class="sudowp-lv-title">Log Viewer</span>
		<?php if ( $showEditSection && $current_file_rel ) : ?>
			<span class="sudowp-lv-file-path"><?php echo $current_file_rel; ?></span>
		<?php endif; ?>
		<div class="sudowp-lv-meta">
			<?php if ( false !== $file_mtime ) : ?>
				<span title="Last updated">
					<?php echo esc_html( date_i18n(
						get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
						$file_mtime
					) ); ?>
				</span>
			<?php endif; ?>
			<?php if ( false !== $file_size ) : ?>
				<span title="File size"><?php echo esc_html( sudowp_lv_format_bytes( $file_size ) ); ?></span>
			<?php endif; ?>
		</div>
	</div>

	<?php /* ---- Toolbar ---- */ ?>
	<div id="sudowp-lv-toolbar">

		<?php if ( $showEditSection && $writeable ) : ?>
		<form method="post" action="<?php echo esc_url( $this->getPageUrl() ); ?>" style="display:contents;">
			<?php wp_nonce_field( 'actions_nonce', 'actions_nonce' ); ?>
			<input type="hidden" value="<?php echo esc_attr( $current_file ); ?>"
				name="<?php echo esc_attr( self::$_KEYS_FILEACTION_FILE ); ?>" />
			<input id="scrollto" type="hidden" value="0"
				name="<?php echo esc_attr( self::$_KEYS_FILEACTION_SCROLLTO ); ?>">

			<div class="sudowp-lv-toolbar-group">
				<select name="<?php echo esc_attr( self::$_KEYS_FILEACTION_ACTION ); ?>" id="sudowp-lv-action-select">
					<option selected="selected" value="-1"><?php esc_html_e( 'File Actions' ); ?></option>
					<option value="<?php echo esc_attr( self::$_KEYS_FILEACTIONS_DUMP ); ?>"><?php esc_html_e( 'Delete file' ); ?></option>
					<option value="<?php echo esc_attr( self::$_KEYS_FILEACTIONS_EMPTY ); ?>"><?php esc_html_e( 'Clear contents' ); ?></option>
					<option value="<?php echo esc_attr( self::$_KEYS_FILEACTIONS_BREAK ); ?>"><?php esc_html_e( 'Add separator' ); ?></option>
				</select>
				<button type="submit" id="sudowp-lv-action-btn" name="<?php echo esc_attr( self::$_KEYS_FILEACTION_SUBMIT ); ?>"
					class="sudowp-lv-btn sudowp-lv-btn-danger" style="display:none;">
					Run
				</button>
			</div>
		</form>

		<div class="sudowp-lv-toolbar-divider"></div>
		<?php endif; ?>

		<?php if ( $showEditSection ) : ?>
		<form method="post" action="<?php echo esc_url( $this->getPageUrl() ); ?>" style="display:contents;">
			<?php wp_nonce_field( 'viewoptions_nonce', 'viewoptions_nonce' ); ?>
			<input type="hidden" value="<?php echo esc_attr( $current_file ); ?>" name="file2" />

			<div class="sudowp-lv-toolbar-group">
				<label class="sudowp-lv-autorefresh"
					title="Auto-refresh every <?php echo absint( User_Options::getAutoRefreshIntervall() ); ?> seconds">
					<input type="checkbox" value="1"
						<?php checked( 1 === User_Options::getAutoRefresh() ); ?>
						id="<?php echo esc_attr( User_Options::KEYS_AUTOREFRESH ); ?>"
						name="<?php echo esc_attr( User_Options::KEYS_AUTOREFRESH ); ?>" />
					Auto-refresh (<?php echo absint( User_Options::getAutoRefreshIntervall() ); ?>s)
				</label>
			</div>

			<div class="sudowp-lv-toolbar-divider"></div>

			<div class="sudowp-lv-toolbar-group">
				<span class="sudowp-lv-order-label">Order:</span>
				<select name="<?php echo esc_attr( User_Options::KEYS_LINEOUTPUTORDER ); ?>">
					<option <?php selected( User_Options::LINEOUTPUTORDER_FIFO === User_Options::getLineOutputOrder() ); ?>
						value="<?php echo esc_attr( (string) User_Options::LINEOUTPUTORDER_FIFO ); ?>">Oldest first (FIFO)
					</option>
					<option <?php selected( User_Options::LINEOUTPUTORDER_FILO === User_Options::getLineOutputOrder() ); ?>
						value="<?php echo esc_attr( (string) User_Options::LINEOUTPUTORDER_FILO ); ?>">Newest first (FILO)
					</option>
				</select>
				<button type="submit" name="<?php echo esc_attr( self::$_KEYS_VIEWFIELDS_SUBMIT ); ?>"
					class="sudowp-lv-btn sudowp-lv-btn-primary">
					Apply
				</button>
			</div>
		</form>
		<?php endif; ?>

	</div>

	<?php if ( ! $showEditSection ) : ?>

		<?php /* ---- No files state ---- */ ?>
		<div id="sudowp-lv-nofiles">
			<svg class="sudowp-lv-nofiles-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
				<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
			</svg>
			<p>No *.log files found in wp-content/</p>
			<p style="font-size:11px;color:#2d4455;">Enable WP_DEBUG_LOG in wp-config.php to generate debug.log</p>
		</div>

	<?php else : ?>

		<?php /* ---- Sidebar ---- */ ?>
		<div id="sudowp-lv-sidebar">
			<div id="sudowp-lv-sidebar-header">Log Files</div>
			<ul>
				<?php foreach ( $files as $file ) :
					$is_current = ( $file === $current_file );
				?>
					<li<?php echo $is_current ? ' class="sudowp-lv-active"' : ''; ?>>
						<a href="<?php echo esc_url( $this->getPageUrl() . '&file=' . rawurlencode( $file ) ); ?>">
							<?php echo esc_html( $file ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<?php /* ---- Content ---- */ ?>
		<div id="sudowp-lv-content">
			<?php if ( false === $realfile || ! is_file( $realfile ) ) : ?>
				<div id="sudowp-lv-empty">
					<span class="sudowp-lv-empty-icon">&#9888;</span>
					<span class="sudowp-lv-empty-text">Could not load file.</span>
				</div>
			<?php else : ?>
				<textarea id="newcontent" name="newcontent" readonly="readonly" spellcheck="false"><?php echo esc_html( $this->getCurrentFileContent() ); ?></textarea>
				<div id="sudowp-lv-fileinfo">
					<div class="sudowp-lv-fi-item">
						<span class="sudowp-lv-fi-label">Path</span>
						<span><?php echo esc_html( $realfile ); ?></span>
					</div>
					<?php if ( false !== $file_mtime ) : ?>
					<div class="sudowp-lv-fi-item">
						<span class="sudowp-lv-fi-label">Modified</span>
						<span><?php echo esc_html( date_i18n(
							get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
							$file_mtime
						) ); ?></span>
					</div>
					<?php endif; ?>
					<?php if ( false !== $file_size ) : ?>
					<div class="sudowp-lv-fi-item">
						<span class="sudowp-lv-fi-label">Size</span>
						<span><?php echo esc_html( sudowp_lv_format_bytes( $file_size ) ); ?></span>
					</div>
					<?php endif; ?>
					<div class="sudowp-lv-fi-item">
						<span class="sudowp-lv-fi-label">Access</span>
						<span><?php echo $writeable ? 'Read / Write' : 'Read only'; ?></span>
					</div>
				</div>
			<?php endif; ?>
		</div>

	<?php endif; ?>

</div>

<?php if ( 1 === User_Options::getAutoRefresh() ) : ?>
<script type="text/javascript">
	setTimeout( function() { window.location.replace( window.location.href ); }, <?php echo absint( User_Options::getAutoRefreshIntervall() * 1000 ); ?> );
</script>
<?php endif; ?>

<script type="text/javascript">
	(function() {
		var select = document.getElementById('sudowp-lv-action-select');
		var btn    = document.getElementById('sudowp-lv-action-btn');
		if ( ! select || ! btn ) return;
		select.addEventListener('change', function() {
			btn.style.display = (this.value !== '-1') ? 'inline-flex' : 'none';
		});
	})();
</script>
