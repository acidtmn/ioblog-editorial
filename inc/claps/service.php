<?php
/** Правила хлопков: максимум десять, повтор без удвоения, отмена и ограничение запросов. */
namespace IOBlog\Claps;

final class Service {
	public const LIMIT = 10;
	private $repository;
	public function __construct( Repository $repository ) { $this->repository = $repository; }

	public function read( int $post_id ): array {
		return array_merge( $this->repository->state( $post_id, Identity::actor() ), array(
			'limit' => self::LIMIT, 'token' => Identity::token( $post_id ),
		) );
	}

	public function session( int $post_id ) {
		if ( ! Identity::cookie() ) {
			// Только REMOTE_ADDR: поддельный X-Forwarded-For не обходит ограничение создания новых браузеров.
			if ( ! Rate_Limiter::allow( 'session:' . ( $_SERVER['REMOTE_ADDR'] ?? 'unknown' ), 30, HOUR_IN_SECONDS ) ) { return $this->limited(); }
			if ( ! Identity::issue() ) {
				return new \WP_Error( 'claps_cookie', __( 'Allow cookies for this site to save your claps.', 'ioblog-editorial' ), array( 'status' => 403 ) );
			}
		}
		return $this->read( $post_id );
	}

	public function change( int $post_id, int $desired, int $expected, string $token ) {
		if ( $desired < 0 || $desired > self::LIMIT || $expected < 0 || $expected > self::LIMIT ) {
			return new \WP_Error( 'claps_limit', __( 'You can give up to 10 claps per article.', 'ioblog-editorial' ), array( 'status' => 400 ) );
		}
		if ( ! Identity::verify( $post_id, $token ) ) {
			return new \WP_Error( 'claps_token', __( 'Refresh the article and allow cookies to continue.', 'ioblog-editorial' ), array( 'status' => 403 ) );
		}
		if ( ! Rate_Limiter::allow( 'write:' . Identity::actor(), 30, MINUTE_IN_SECONDS ) ) { return $this->limited(); }
		if ( ! $this->repository->change( $post_id, Identity::actor(), $desired, $expected ) ) {
			return new \WP_Error( 'claps_conflict', __( 'Your claps changed in another tab. The counter has been refreshed.', 'ioblog-editorial' ), array( 'status' => 409 ) );
		}
		return $this->read( $post_id );
	}

	private function limited(): \WP_Error {
		return new \WP_Error( 'claps_rate', __( 'Too many requests. Please wait and try again.', 'ioblog-editorial' ), array( 'status' => 429 ) );
	}
}
