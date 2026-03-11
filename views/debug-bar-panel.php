<?php
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">

	<?php if ( $showEditSection ) : ?>

	<div class="fileedit-sub">

		<div id="templateside">
			<?php
			printf(
				'%1$s <strong>%2$s</strong>',
				esc_html__( 'Showing' ),
				esc_html( str_replace( realpath( ABSPATH ), '', $realfile ) )
			);
			?>
			<a class="button-secondary" title="Open full view" href="<?php echo esc_url( Files_View_Page::getPageUrl() ); ?>">Open full view</a>
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

</div>
