<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<?php if ( isset( $dumped ) ) :
		if ( $dumped ) : ?>
			<div id="message" class="updated">
				<p><?php esc_html_e( 'File dumped successfully.' ); ?></p>
			</div>
			<?php return;
		else : ?>
			<div id="message" class="error">
				<p><?php esc_html_e( 'Could not dump file.' ); ?></p>
			</div>
		<?php endif;
	endif; ?>

	<?php if ( isset( $handle ) ) :
		if ( ! $handle ) : ?>
			<div id="message" class="error">
				<p><?php esc_html_e( 'Could not update file.' ); ?></p>
			</div>
		<?php else : ?>
			<div id="message" class="updated">
				<p><?php esc_html_e( 'File updated successfully.' ); ?></p>
			</div>
		<?php endif;
	endif; ?>

	<?php if ( ! $files ) : ?>
		<div id="message" class="updated">
			<p><?php esc_html_e( 'No files found.' ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! $writeable && $showEditSection ) : ?>
		<div id="message" class="updated">
			<p>
			<?php
				printf(
					/* translators: %s: filename */
					esc_html__( 'You can not edit file [%s] ( not writeable ).' ),
					esc_html( $this->getCurrentFile() )
				);
			?>
			</p>
		</div>
	<?php endif; ?>

	<?php if ( $showEditSection ) : ?>

		<div class="fileedit-sub">

			<?php
			printf(
				'%1$s <strong>%2$s</strong>',
				esc_html__( 'Showing' ),
				esc_html( str_replace( realpath( ABSPATH ), '', $realfile ) )
			);
			?>

			<div class="tablenav top">

				<?php if ( $writeable ) : ?>

					<div class="alignleft">
						<form method="post" action="<?php echo esc_url( $this->getPageUrl() ); ?>">
							<?php wp_nonce_field( 'actions_nonce', 'actions_nonce' ); ?>
							<input type="hidden" value="<?php echo esc_attr( $this->getCurrentFile() ); ?>"
								name="<?php echo esc_attr( self::$_KEYS_FILEACTION_FILE ); ?>" />
							<input id="scrollto" type="hidden" value="0"
								name="<?php echo esc_attr( self::$_KEYS_FILEACTION_SCROLLTO ); ?>">
							<select name="<?php echo esc_attr( self::$_KEYS_FILEACTION_ACTION ); ?>">
								<option selected="selected" value="-1"><?php esc_html_e( 'File Actions' ); ?></option>
								<option value="<?php echo esc_attr( self::$_KEYS_FILEACTIONS_DUMP ); ?>"><?php esc_html_e( 'Dump' ); ?></option>
								<option value="<?php echo esc_attr( self::$_KEYS_FILEACTIONS_EMPTY ); ?>"><?php esc_html_e( 'Empty' ); ?></option>
								<option value="<?php echo esc_attr( self::$_KEYS_FILEACTIONS_BREAK ); ?>"><?php esc_html_e( 'Break' ); ?></option>
							</select>
							<?php submit_button( __( 'Do' ), 'button', self::$_KEYS_FILEACTION_SUBMIT, false ); ?>
						</form>
					</div>

				<?php endif; ?>

				<div class="alignright">
					<form method="post" action="<?php echo esc_url( $this->getPageUrl() ); ?>">
						<?php wp_nonce_field( 'viewoptions_nonce', 'viewoptions_nonce' ); ?>
						<input type="hidden" value="<?php echo esc_attr( $this->getCurrentFile() ); ?>" name="file2" />
						<input
							title="Autorefresh page every <?php echo absint( User_Options::getAutoRefreshIntervall() ); ?> seconds"
							type="checkbox" value="1" <?php checked( 1 === User_Options::getAutoRefresh() ); ?>
							id="<?php echo esc_attr( User_Options::KEYS_AUTOREFRESH ); ?>"
							name="<?php echo esc_attr( User_Options::KEYS_AUTOREFRESH ); ?>" />
						<label
							title="Autorefresh page every <?php echo absint( User_Options::getAutoRefreshIntervall() ); ?> seconds"
							for="<?php echo esc_attr( User_Options::KEYS_AUTOREFRESH ); ?>">Autorefresh</label>
						<select name="<?php echo esc_attr( User_Options::KEYS_LINEOUTPUTORDER ); ?>">
							<option <?php selected( User_Options::LINEOUTPUTORDER_FIFO === User_Options::getLineOutputOrder() ); ?>
								value="<?php echo esc_attr( (string) User_Options::LINEOUTPUTORDER_FIFO ); ?>">FIFO
							</option>
							<option <?php selected( User_Options::LINEOUTPUTORDER_FILO === User_Options::getLineOutputOrder() ); ?>
								value="<?php echo esc_attr( (string) User_Options::LINEOUTPUTORDER_FILO ); ?>">FILO
							</option>
						</select>
						<?php submit_button( __( 'Apply' ), 'button', self::$_KEYS_VIEWFIELDS_SUBMIT, false ); ?>
					</form>
				</div>

			</div>

			<div id="templateside">
				<h3>Log Files</h3>
				<ul>
					<?php foreach ( $files as $file ) :
						$is_current = ( $file === $this->getCurrentFile() );
					?>
						<li<?php echo $is_current ? ' class="highlight"' : ''; ?>>
							<a href="<?php echo esc_url( $this->getPageUrl() . '&file=' . rawurlencode( $file ) ); ?>">
								<?php echo esc_html( $file ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>

			<div id="template">
				<div>
					<?php if ( false === $realfile || ! is_file( $realfile ) ) : ?>
						<div id="message" class="error">
							<p><?php esc_html_e( 'Could not load file.' ); ?></p>
						</div>
					<?php else : ?>
						<textarea id="newcontent" name="newcontent" rows="25" cols="70"
								readonly="readonly"><?php echo esc_html( $this->getCurrentFileContent() ); ?></textarea>
					<?php endif; ?>
					<div>
						<h3><?php esc_html_e( 'Fileinfo' ); ?></h3>
						<dl>
							<dt><?php esc_html_e( 'Fullpath:' ); ?></dt>
							<dd><?php echo esc_html( $realfile ); ?></dd>
							<dt><?php esc_html_e( 'Last updated: ' ); ?></dt>
							<dd><?php
								if ( false !== $realfile && is_file( $realfile ) ) {
									echo esc_html( date_i18n(
										get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
										filemtime( $realfile )
									) );
								}
							?></dd>
						</dl>
					</div>
				</div>
			</div>

		</div>

	<?php endif; ?>

	<?php if ( 1 === User_Options::getAutoRefresh() ) : ?>
		<script type="text/javascript">
			setTimeout( function() { window.location.replace( window.location.href ); }, <?php echo absint( User_Options::getAutoRefreshIntervall() * 1000 ); ?> );
		</script>
	<?php endif; ?>

</div>
