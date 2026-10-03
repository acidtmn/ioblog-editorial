<?php
/** Превью библиотеки: публичный endpoint возвращает только безопасную обложку опубликованных статей. */
// Clearfy может закрывать REST гостям. Исключаем только публичный маршрут
// обложек, не открывая остальные API WordPress или будущие маршруты темы.
add_filter( 'clearfy_rest_api_white_list', static function ( $routes ) {
	$routes[] = '^\/ioblog\/v1\/library\/covers$';
	return array_unique( $routes );
} );
add_action( 'rest_api_init', static function () {
	register_rest_route( 'ioblog/v1', '/library/covers', array( 'methods' => 'GET', 'permission_callback' => '__return_true', 'callback' => static function ( WP_REST_Request $request ) {
		$ids = $request->get_param( 'ids' );
		if ( ! is_string( $ids ) || ! preg_match( '/^[1-9][0-9]*(?:,[1-9][0-9]*){0,29}$/D', $ids ) ) { return new WP_Error( 'ids', 'Invalid IDs', array( 'status' => 400 ) ); }
		$output = array();
		foreach ( array_unique( array_map( 'absint', explode( ',', $ids ) ) ) as $id ) {
			$post = get_post( $id );
			if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status || $post->post_password ) { continue; }
			$css = ioblog_css_cover_html( $id );
			if ( $css ) {
				// Обложка строится шаблоном темы; whitelist исключает обработчики событий и внешний код фильтров.
				$output[ $id ] = array( 'html' => wp_kses( $css, array( 'div' => array( 'class' => true, 'role' => true, 'aria-label' => true, 'aria-hidden' => true ), 'span' => array( 'class' => true ), 'strong' => array( 'class' => true ), 'i' => array(), 'b' => array(), 'br' => array() ) ) );
			} else { $output[ $id ] = array( 'url' => get_the_post_thumbnail_url( $id, 'ioblog-card' ) ?: '', 'title' => wp_strip_all_tags( get_the_title( $id ) ) ); }
		}
		$response = new WP_REST_Response( $output ); $response->header( 'Cache-Control', 'public, max-age=300' ); return $response;
	} ) );
} );
function ioblog_enqueue_library_cover_assets() {
	wp_enqueue_style( 'ioblog-library-covers', get_theme_file_uri( 'assets/css/library-covers.css' ), array(), ioblog_asset_version( 'assets/css/library-covers.css' ) );
	wp_enqueue_script( 'ioblog-library-covers', get_theme_file_uri( 'assets/js/library-covers.js' ), array(), ioblog_asset_version( 'assets/js/library-covers.js' ), true );
	// Версия меняет URL после обновления: браузер не переиспользует старый постоянный редирект Clearfy.
	wp_localize_script( 'ioblog-library-covers', 'IOBlogLibraryCovers', array( 'endpoint' => add_query_arg( 'v', wp_get_theme()->get( 'Version' ), rest_url( 'ioblog/v1/library/covers' ) ) ) );
}
add_action( 'wp_enqueue_scripts', static function () {
	if ( ioblog_get_setting( 'library_enabled' ) ) { ioblog_enqueue_library_cover_assets(); }
}, 15 );
