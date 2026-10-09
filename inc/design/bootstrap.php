<?php
/** Собирает модуль оформления, не смешивая транспорт, контракт и представление. */
foreach ( array( 'presets', 'schema', 'labels', 'fonts', 'service', 'admin' ) as $module ) { require_once __DIR__ . '/' . $module . '.php'; }
add_filter( 'ioblog_settings_defaults', static function ( $settings ) { $settings['design_config'] = array(); return $settings; } );
add_filter( 'ioblog_sanitize_settings', static function ( $settings, $input, $current ) {
	// Штатная форма сохраняет ранее опубликованный дизайн без необходимости дублировать его поля.
	$settings['design_config'] = $current['design_config'] ?? array();
	return $settings;
}, 20, 3 );
add_action( 'wp_enqueue_scripts', static function () {
	wp_enqueue_style( 'ioblog-design', get_theme_file_uri( 'assets/css/reader-mode.css' ), array( 'ioblog-responsive' ), ioblog_asset_version( 'assets/css/reader-mode.css' ) );
	wp_add_inline_style( 'ioblog-design', Ioblog_Design_Service::css( Ioblog_Design_Service::effective() ) );
	if ( is_singular( 'post' ) ) { wp_enqueue_script( 'ioblog-reader-mode', get_theme_file_uri( 'assets/js/reader-mode.js' ), array(), ioblog_asset_version( 'assets/js/reader-mode.js' ), true ); }
}, 40 );
add_filter( 'block_editor_settings_all', static function ( $settings ) {
	// Тот же CSS приходит в iframe Gutenberg: редактор показывает семейства, цвета и ритм статьи.
	$css = Ioblog_Design_Service::css( Ioblog_Design_Service::current() );
	$settings['styles'][] = array( 'css' => $css . '.editor-styles-wrapper{font-family:var(--io-font-body);color:var(--io-text);background:var(--io-bg)}', 'isGlobalStyles' => true );
	return $settings;
} );
add_action( 'ioblog_article_actions', static function () {
	$config = Ioblog_Design_Service::effective();
	if ( $config['reader_mode'] ) { echo '<button type="button" class="io-action-button io-reader-toggle" aria-pressed="false" data-tooltip="' . esc_attr__( 'Reader mode', 'ioblog-editorial' ) . '" aria-label="' . esc_attr__( 'Reader mode', 'ioblog-editorial' ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6a3 3 0 0 1 3 3v14a4 4 0 0 0-4-2H4zM13 7a3 3 0 0 1 3-3h5v15h-4a4 4 0 0 0-4 2"/></svg></button>'; }
	if ( $config['print_button'] ) { echo '<button type="button" class="io-action-button io-print-article" data-tooltip="' . esc_attr__( 'Print article', 'ioblog-editorial' ) . '" aria-label="' . esc_attr__( 'Print article', 'ioblog-editorial' ) . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8V3h10v5M7 17H3V9h18v8h-4M7 14h10v7H7z"/></svg></button>'; }
} );
