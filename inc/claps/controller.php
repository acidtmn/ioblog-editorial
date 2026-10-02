<?php
/** REST-вход: маршрутизация и делегирование, без SQL и правил подсчёта. */
namespace IOBlog\Claps;

final class Controller {
	private $service;
	public function __construct( Service $service ) { $this->service = $service; }

	public function register() {
		$base = array( 'callback' => array( $this, 'handle' ), 'permission_callback' => array( Access::class, 'check' ) );
		$number = array( 'type' => 'integer', 'minimum' => 0, 'maximum' => Service::LIMIT, 'required' => true );
		register_rest_route( 'ioblog/v1', '/claps/(?P<id>\d+)', array(
			array_merge( $base, array( 'methods' => 'GET' ) ),
			array_merge( $base, array( 'methods' => 'POST', 'args' => array( 'claps' => $number, 'expected' => $number ) ) ),
		) );
		register_rest_route( 'ioblog/v1', '/claps/(?P<id>\d+)/session', array_merge( $base, array( 'methods' => 'POST' ) ) );
	}

	public function handle( \WP_REST_Request $request ) {
		try {
			if ( 'GET' === $request->get_method() ) { return $this->service->read( (int) $request['id'] ); }
			if ( str_ends_with( $request->get_route(), '/session' ) ) { return $this->service->session( (int) $request['id'] ); }
			return $this->service->change( (int) $request['id'], (int) $request['claps'], (int) $request['expected'], (string) $request->get_header( 'x-ioblog-claps' ) );
		} catch ( \Throwable $error ) {
			// Ошибка хранилища не раскрывает SQL и не ломает чтение статьи.
			return new \WP_Error( 'claps_unavailable', __( 'Claps are temporarily unavailable. Please try again later.', 'ioblog-editorial' ), array( 'status' => 503 ) );
		}
	}

	public static function no_cache( $response, $server, $request ) {
		if ( str_starts_with( $request->get_route(), '/ioblog/v1/claps/' ) ) {
			$response->header( 'Cache-Control', 'private, no-store, max-age=0' );
			$response->header( 'Vary', 'Cookie, Origin' );
		}
		return $response;
	}
}
