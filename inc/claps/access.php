<?php
/** Граница публичного API: видимость записи, настройка владельца и источник запроса. */
namespace IOBlog\Claps;

final class Access {
	public static function check( \WP_REST_Request $request, string $setting = 'claps_enabled' ) {
		if ( ! ioblog_get_setting( $setting ) ) {
			return new \WP_Error( 'claps_disabled', __( 'Claps are disabled.', 'ioblog-editorial' ), array( 'status' => 403 ) );
		}
		$post = get_post( (int) $request['id'] );
		// Парольные и неопубликованные статьи не должны раскрываться через отдельный публичный счётчик.
		if ( ! $post || 'post' !== $post->post_type || 'publish' !== $post->post_status || '' !== $post->post_password ) {
			return new \WP_Error( 'claps_post', __( 'This article is unavailable.', 'ioblog-editorial' ), array( 'status' => 404 ) );
		}
		$origin = $request->get_header( 'origin' );
		$site = self::origin( home_url() );
		if ( ( $origin && self::origin( $origin ) !== $site ) || in_array( $request->get_header( 'sec-fetch-site' ), array( 'cross-site', 'same-site' ), true ) ) {
			return new \WP_Error( 'claps_origin', __( 'Open the article on this site and try again.', 'ioblog-editorial' ), array( 'status' => 403 ) );
		}
		// POST без Origin принимается только с собственным Referer; сторонняя форма не создаёт сессию.
		if ( 'POST' === $request->get_method() && ! $origin && self::origin( (string) $request->get_header( 'referer' ) ) !== $site ) {
			return new \WP_Error( 'claps_origin', __( 'Open the article on this site and try again.', 'ioblog-editorial' ), array( 'status' => 403 ) );
		}
		return true;
	}

	private static function origin( string $url ): string {
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) ) { return ''; }
		$scheme = strtolower( $parts['scheme'] );
		$port = $parts['port'] ?? ( 'https' === $scheme ? 443 : 80 );
		return $scheme . '://' . strtolower( $parts['host'] ) . ':' . $port;
	}
}
