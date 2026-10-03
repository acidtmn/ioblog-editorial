<?php
/** Сервис оформления: хранение, черновики и безопасная генерация CSS. */
final class Ioblog_Design_Service {
	public static function current() {
		$raw = ioblog_get_setting( 'design_config' );
		try { return Ioblog_Design_Schema::validate( is_array( $raw ) ? $raw : array() ); }
		catch ( InvalidArgumentException $error ) { return Ioblog_Design_Schema::defaults(); }
	}
	public static function save( $input ) {
		$config = Ioblog_Design_Schema::validate( $input );
		$settings = (array) get_option( 'ioblog_settings', array() );
		$settings['design_config'] = $config;
		// Эта отдельная форма не отправляет остальные переключатели: полный sanitizer сбросил бы их.
		$registered = has_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings' );
		remove_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings' );
		try { update_option( 'ioblog_settings', $settings ); }
		finally { if ( false !== $registered ) { add_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings', $registered ); } }
		if ( ( get_option( 'ioblog_settings' )['design_config'] ?? null ) !== $config ) { throw new RuntimeException( 'save' ); }
	}
	public static function effective() {
		$config = self::current();
		// Черновик существует только десять минут и доступен тому администратору, который его создал.
		$token = isset( $_GET['io_design_preview'] ) && is_string( $_GET['io_design_preview'] ) ? sanitize_key( wp_unslash( $_GET['io_design_preview'] ) ) : '';
		if ( current_user_can( 'manage_options' ) && preg_match( '/^[a-f0-9]{40}$/D', $token ) ) {
			$draft = get_transient( 'io_design_' . get_current_user_id() );
			if ( is_array( $draft ) && hash_equals( $draft['token'], $token ) ) { $config = $draft['config']; nocache_headers(); }
		}
		return apply_filters( 'ioblog_design_effective', $config );
	}
	public static function css( $config ) {
		$config = Ioblog_Design_Schema::validate( $config );
		$css = '';
		foreach ( array( 'light', 'dark' ) as $scheme ) {
			$vars = '';
			foreach ( $config as $key => $value ) {
				if ( str_starts_with( $key, $scheme . '_' ) ) { $vars .= '--io-' . str_replace( '_', '-', substr( $key, strlen( $scheme ) + 1 ) ) . ':' . $value . ';'; }
			}
			$selector = 'light' === $scheme ? ':root' : ':root[data-theme="dark"]';
			$css .= $selector . '{' . $vars . '}';
			if ( 'dark' === $scheme ) { $css .= '@media(prefers-color-scheme:dark){:root[data-theme="system"]{' . $vars . '}}'; }
		}
		$fonts = array( 'dm' => '"DM Sans",sans-serif', 'serif' => 'Georgia,serif', 'sans' => 'Verdana,sans-serif', 'mono' => '"Courier New",monospace', 'custom' => '"DM Sans",sans-serif' );
		if ( $config['font_attachment'] && 'font/woff2' === get_post_mime_type( $config['font_attachment'] ) && in_array( 'custom', array_values( $config ), true ) ) {
			$url = esc_url_raw( wp_get_attachment_url( $config['font_attachment'] ) );
			$css .= '@font-face{font-family:"IO Blog Custom";font-display:swap;src:url("' . str_replace( array( '"', '\\' ), '', $url ) . '") format("woff2")}';
			$fonts['custom'] = '"IO Blog Custom","DM Sans",sans-serif';
		}
		$vars = '--io-container:' . $config['container_width'] . 'px;--io-radius:' . $config['radius'] . 'px;--io-sidebar-width:' . $config['sidebar_width'] . 'px;--io-design-gap:' . $config['section_gap'] . 'px;';
		foreach ( array( 'body', 'heading', 'menu', 'button', 'meta', 'code' ) as $role ) { $vars .= '--io-font-' . $role . ':' . $fonts[ $config[ 'font_' . $role ] ] . ';'; }
		$css .= ':root{' . $vars . '}body{font-family:var(--io-font-body);font-size:' . $config['body_size'] . 'px;letter-spacing:' . $config['letter_spacing'] . 'px}';
		$css .= 'h1,h2,h3,h4,h5,h6{font-family:var(--io-font-heading);font-weight:' . $config['heading_weight'] . '}';
		$css .= '.io-article-header h1{font-size:clamp(32px,' . ( 4.6 * $config['heading_scale'] / 100 ) . 'vw,' . ( $config['size_h1'] * $config['heading_scale'] / 100 ) . 'px)}';
		foreach ( array( 'h2', 'h3', 'h4', 'h5', 'h6' ) as $level ) { $css .= '.entry-content ' . $level . '{font-size:' . $config[ 'size_' . $level ] . 'px}'; }
		foreach ( array( 'menu' => '.io-navigation,.io-mobile-nav', 'button' => 'button,.io-button', 'meta' => '.io-post-meta', 'body' => 'body' ) as $role => $selector ) { $css .= $selector . '{font-weight:' . $config[ 'weight_' . $role ] . '}'; }
		$css .= '.io-navigation,.io-mobile-nav{font-family:var(--io-font-menu)}button,.io-button{font-family:var(--io-font-button)}.io-post-meta{font-family:var(--io-font-meta)}code,pre{font-family:var(--io-font-code)}.io-layout-sidebar .io-article-layout{grid-template-columns:minmax(0,var(--io-content)) var(--io-sidebar-width)}';
		$css .= '.io-button:not(.io-button--secondary){color:var(--io-button-text)}:focus-visible{outline:3px solid var(--io-focus);outline-offset:3px}.entry-content>p{margin-block:' . $config['paragraph_gap'] . 'px}';
		$css .= ':root[data-theme] .io-article-header__lead,:root[data-theme] .entry-content>p:first-child{color:var(--io-muted)}.io-post-grid{gap:' . $config['card_gap'] . 'px}.io-footer__grid{grid-template-columns:repeat(' . $config['footer_columns'] . ',minmax(0,1fr))}';
		$ratio = array( 'wide' => '16/9', 'classic' => '4/3', 'square' => '1/1' )[ $config['card_ratio'] ];
		$css .= '.io-card__media{aspect-ratio:' . $ratio . '}.io-card__media img{height:100%;object-fit:cover}.io-home-section{padding-block:var(--io-design-gap)}';
		if ( 'none' === $config['shadow'] ) { $css .= ':root{--io-shadow:none;--io-shadow-sm:none}'; }
		if ( 'strong' === $config['shadow'] ) { $css .= ':root{--io-shadow:0 20px 60px #101a2d26;--io-shadow-sm:0 10px 30px #101a2d20}'; }
		$regions = array( 'header_search' => '.io-search-open', 'header_theme_switch' => '.io-theme-toggle', 'header_tagline' => '.io-brand__tagline', 'article_cover' => '.io-article-cover', 'article_author' => '.io-author-card', 'article_navigation' => '.io-post-navigation', 'article_related' => '.io-related', 'article_toc' => '.io-toc,.io-mobile-toc', 'sidebar_recent' => '.io-widget--popular', 'card_excerpt' => '.io-card__excerpt', 'card_meta' => '.io-card .io-post-meta', 'footer_social' => '.io-footer__social' );
		foreach ( $regions as $key => $selector ) { if ( ! $config[ $key ] ) { $css .= $selector . '{display:none!important}'; } }
		if ( ! $config['header_sticky'] ) { $css .= '.io-header{position:relative;top:auto}'; }
		// Переключатель убирает обе версии меню, сохраняя назначение меню и рубрики под hero.
		if ( ! $config['header_navigation'] ) { $css .= '.io-navigation,.io-mobile-nav,.io-menu-toggle{display:none!important}.io-header__actions{margin-left:auto}'; }
		$css .= '@media(max-width:900px){.io-footer__grid{grid-template-columns:1fr}.entry-content{font-size:' . $config['mobile_reading_size'] . 'px}}';
		return $css;
	}
}
