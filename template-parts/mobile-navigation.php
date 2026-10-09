<?php
/** Мобильные действия сайта не зависят от меню рубрик в шапке компьютера. */
defined( 'ABSPATH' ) || exit;
?>
<nav id="io-mobile-navigation" class="io-mobile-nav" aria-label="<?php esc_attr_e( 'Mobile menu', 'ioblog-editorial' ); ?>" hidden>
	<ul class="io-mobile-nav__list">
		<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v11h-7v-7h-4v7H3z"/></svg><?php esc_html_e( 'Home', 'ioblog-editorial' ); ?></a></li>
		<?php do_action( 'ioblog_mobile_navigation' ); ?>
		<?php if ( ioblog_get_setting( 'library_enabled' ) ) { ?><li><button class="io-library-open" type="button" hidden aria-controls="io-library" aria-haspopup="dialog"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z"/></svg><?php esc_html_e( 'My library', 'ioblog-editorial' ); ?></button></li><?php } ?>
		<li><button class="io-search-open" type="button" aria-controls="io-search-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 4 4"/></svg><?php esc_html_e( 'Search', 'ioblog-editorial' ); ?></button></li>
	</ul>
	<?php if ( has_nav_menu( 'mobile' ) ) { wp_nav_menu( array( 'theme_location' => 'mobile', 'container' => false, 'menu_class' => 'io-mobile-nav__list io-mobile-nav__custom', 'fallback_cb' => false, 'depth' => 2 ) ); } ?>
</nav>
