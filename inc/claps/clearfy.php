<?php
/** Точечное разрешение API хлопков через штатный список исключений Clearfy Pro. */
namespace IOBlog\Claps;

add_filter( 'clearfy_rest_api_white_list', static function ( $routes ) {
	// Clearfy трактует элементы как регулярные выражения: якоря не открывают чужие пространства API.
	if ( ioblog_get_setting( 'claps_enabled' ) ) {
		$routes[] = '^\/ioblog\/v1\/claps\/[0-9]+(?:\/session)?$';
	}
	return $routes;
} );
