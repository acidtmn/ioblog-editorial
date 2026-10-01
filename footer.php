<?php
/**
 * Подвал самостоятельной темы.
 *
 * @package IoblogEditorial
 */
?>
</main>
<footer class="io-footer">
	<div class="io-container io-footer__grid">
		<div class="io-footer__about">
			<a class="io-brand io-brand--footer" href="<?php echo esc_url( home_url( '/' ) ); ?>"><span class="io-brand__mark">IO</span><strong>BLOG</strong></a>
			<p><?php echo esc_html( get_theme_mod( 'ioblog_footer_text', __( 'An independent blog about technology, computers, software, and practical solutions without the noise.', 'ioblog-editorial' ) ) ); ?></p>
		</div>
		<nav class="io-footer__nav" aria-label="<?php esc_attr_e( 'Footer menu', 'ioblog-editorial' ); ?>">
			<h2 class="io-footer__title"><?php esc_html_e( 'Information', 'ioblog-editorial' ); ?></h2>
			<?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'io-footer__menu', 'fallback_cb' => false, 'depth' => 2 ) ); ?>
		</nav>
		<div class="io-footer__social">
			<?php ioblog_render_social_links(); ?>
		</div>
	</div>
	<div class="io-container io-footer__bottom">
		<p><?php echo wp_kses_post( get_theme_mod( 'ioblog_footer_copyright', __( '© 2026 IO Blog. All rights reserved.', 'ioblog-editorial' ) ) ); ?></p>
		<p><?php esc_html_e( 'Made with care for readers', 'ioblog-editorial' ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
