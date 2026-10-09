<?php
/**
 * Подключение собственных ресурсов темы без внешних библиотек.
 *
 * @package IoblogEditorial
 */

/**
 * Возвращает версию локального файла для корректного сброса браузерного кеша.
 *
 * @param string $relative_path Путь относительно каталога темы.
 *
 * @return string
 */
function ioblog_asset_version( $relative_path ) {
	$path = get_theme_file_path( $relative_path );
	return file_exists( $path ) ? (string) filemtime( $path ) : (string) wp_get_theme()->get( 'Version' );
}

/**
 * Подключает модульные таблицы стилей и два небольших скрипта интерфейса.
 *
 * @return void
 */
function ioblog_enqueue_assets() {
	$styles = array(
		'ioblog-base'       => 'assets/css/base.css',
		'ioblog-css-covers' => 'assets/css/css-covers.css',
		'ioblog-components' => 'assets/css/components.css',
		'ioblog-home'       => 'assets/css/home.css',
		'ioblog-content'    => 'assets/css/content.css',
		'ioblog-reading'    => 'assets/css/reading.css',
		'ioblog-single'     => 'assets/css/single.css',
		'ioblog-archive'    => 'assets/css/archive.css',
		'ioblog-dark'       => 'assets/css/dark.css',
		'ioblog-responsive' => 'assets/css/responsive.css',
		'ioblog-navigation' => 'assets/css/navigation.css',
	);
	$dependency = array();

	foreach ( $styles as $handle => $path ) {
		wp_enqueue_style( $handle, get_theme_file_uri( $path ), $dependency, ioblog_asset_version( $path ) );
		$dependency = array( $handle );
	}

	wp_enqueue_script( 'ioblog-navigation', get_theme_file_uri( 'assets/js/navigation.js' ), array(), ioblog_asset_version( 'assets/js/navigation.js' ), true );
	wp_enqueue_script( 'ioblog-interface', get_theme_file_uri( 'assets/js/interface.js' ), array( 'wp-i18n' ), ioblog_asset_version( 'assets/js/interface.js' ), true );
	wp_set_script_translations( 'ioblog-interface', 'ioblog-editorial', get_theme_file_path( 'languages' ) );
}
add_action( 'wp_enqueue_scripts', 'ioblog_enqueue_assets' );

/**
 * Предзагружает основной локальный шрифт, чтобы заголовок не менял размер после первого кадра.
 *
 * @return void
 */
function ioblog_preload_primary_font() {
	printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/dm-sans-normal-cyrillic.woff2' ) ) );
}
add_action( 'wp_head', 'ioblog_preload_primary_font', 1 );
