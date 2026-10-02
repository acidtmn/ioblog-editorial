<?php
/** Подключение модуля Free, WordPress-хуков и локализованных ресурсов интерфейса. */
namespace IOBlog\Claps;

foreach ( array( 'schema', 'repository', 'rate-limiter', 'identity', 'access', 'service', 'controller', 'clearfy' ) as $module ) {
	require_once __DIR__ . '/' . $module . '.php';
}
$controller = new Controller( new Service( new Repository() ) );
add_action( 'init', array( Schema::class, 'install' ), 5 );
add_action( 'rest_api_init', array( $controller, 'register' ) );
add_filter( 'rest_post_dispatch', array( Controller::class, 'no_cache' ), 10, 3 );
add_action( 'deleted_post', array( Repository::class, 'delete_post' ) );

add_action( 'wp_enqueue_scripts', static function () {
	$post = get_queried_object();
	if ( ! is_singular( 'post' ) || ! ioblog_get_setting( 'claps_enabled' ) || ! $post instanceof \WP_Post || 'publish' !== $post->post_status || $post->post_password ) { return; }
	wp_enqueue_style( 'ioblog-claps', get_theme_file_uri( 'assets/css/claps.css' ), array( 'ioblog-single' ), ioblog_asset_version( 'assets/css/claps.css' ) );
	wp_enqueue_script( 'ioblog-claps', get_theme_file_uri( 'assets/js/claps.js' ), array(), ioblog_asset_version( 'assets/js/claps.js' ), true );
	// Переводы берутся из PHP-каталога: отдельная библиотека и сетевой файл языка не нужны.
	wp_localize_script( 'ioblog-claps', 'ioblogClaps', array(
		'endpoint' => rest_url( 'ioblog/v1/claps/' . $post->ID ),
		'session' => rest_url( 'ioblog/v1/claps/' . $post->ID . '/session' ),
		'labels' => array(
			'clap' => __( 'Clap for this article', 'ioblog-editorial' ),
			'limit' => __( 'All 10 claps given. Thank you!', 'ioblog-editorial' ),
			'thanks' => __( 'Thank you for supporting the author!', 'ioblog-editorial' ),
			'undone' => __( 'Your claps have been removed.', 'ioblog-editorial' ),
			'error' => __( 'Could not save your claps. Please try again.', 'ioblog-editorial' ),
			'loading' => __( 'Loading claps…', 'ioblog-editorial' ),
			'saving' => __( 'Saving your claps…', 'ioblog-editorial' ),
			// translators: %1$d: claps from this browser; %2$d: maximum allowed claps.
			'mine' => __( 'Yours: %1$d / %2$d', 'ioblog-editorial' ),
		),
	) );
} );
