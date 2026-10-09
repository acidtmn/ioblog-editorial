<?php
/**
 * Шапка самостоятельной темы.
 *
 * @package IoblogEditorial
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#00866f">
	<script>(()=>{let theme='<?php echo esc_js( get_theme_mod( 'ioblog_color_scheme', 'system' ) ); ?>';try{theme=localStorage.getItem('ioblog-theme')||theme;}catch(error){/* При запрете хранилища используем настройки сайта. */}document.documentElement.dataset.theme=['light','dark','system'].includes(theme)?theme:'system';})();</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="io-skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'ioblog-editorial' ); ?></a>
<header class="io-header<?php echo Ioblog_Design_Service::effective()['mobile_navigation'] ? ' io-header--mobile-navigation' : ''; ?>">
	<div class="io-container io-header__inner">
		<a class="io-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'IO Blog — homepage', 'ioblog-editorial' ); ?>">
			<span class="io-brand__mark">IO</span><strong>BLOG</strong>
			<span class="io-brand__tagline"><?php echo wp_kses( __( 'Technology<br>for everyday life', 'ioblog-editorial' ), array( 'br' => array() ) ); ?></span>
		</a>

		<nav class="io-navigation" aria-label="<?php esc_attr_e( 'Primary menu', 'ioblog-editorial' ); ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'io-navigation__list', 'fallback_cb' => false, 'depth' => 2 ) ); ?>
		</nav>

		<div class="io-header__actions">
			<?php do_action( 'ioblog_header_actions' ); ?>
			<?php if ( ioblog_get_setting( 'library_enabled' ) ) { ?><button class="io-icon-button io-library-open" type="button" hidden aria-label="<?php esc_attr_e( 'My library', 'ioblog-editorial' ); ?>" aria-controls="io-library" aria-haspopup="dialog"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z"/></svg><span class="io-library-count" hidden aria-hidden="true"></span></button><?php } ?>
			<button class="io-icon-button io-search-open" type="button" aria-label="<?php esc_attr_e( 'Open search', 'ioblog-editorial' ); ?>" aria-controls="io-search-modal"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4 4"></path></svg></button>
			<button class="io-icon-button io-theme-toggle" type="button" aria-label="<?php esc_attr_e( 'Toggle color scheme', 'ioblog-editorial' ); ?>"><svg class="io-theme-toggle__sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3.5"></circle><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"></path></svg><svg class="io-theme-toggle__moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15.2A8 8 0 0 1 8.8 4 8.2 8.2 0 1 0 20 15.2z"></path></svg></button>
			<button class="io-icon-button io-menu-toggle" type="button" aria-label="<?php esc_attr_e( 'Open menu', 'ioblog-editorial' ); ?>" data-open-label="<?php esc_attr_e( 'Open menu', 'ioblog-editorial' ); ?>" data-close-label="<?php esc_attr_e( 'Close menu', 'ioblog-editorial' ); ?>" aria-controls="io-mobile-navigation" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg></button>
		</div>
	</div>
	<?php get_template_part( 'template-parts/mobile-navigation' ); ?>
</header>

<?php get_template_part( 'template-parts/search-modal' ); ?>
<main id="main" class="io-site-main">
