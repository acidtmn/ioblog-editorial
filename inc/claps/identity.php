<?php
/** Анонимная идентичность браузера и короткий токен действия, без отпечатков устройства. */
namespace IOBlog\Claps;

final class Identity {
	public static function cookie_name(): string {
		return 'ioblog_claps_' . substr( hash( 'sha256', home_url() ), 0, 12 );
	}

	public static function cookie(): string {
		$value = $_COOKIE[ self::cookie_name() ] ?? '';
		if ( ! is_string( $value ) || ! preg_match( '/^([a-f0-9]{32})\.([a-f0-9]{64})$/D', $value, $parts ) ) { return ''; }
		return hash_equals( self::signature( $parts[1] ), $parts[2] ) ? $value : '';
	}

	public static function issue(): bool {
		if ( self::cookie() ) { return true; }
		$id = bin2hex( random_bytes( 16 ) );
		$value = $id . '.' . self::signature( $id );
		// Cookie выдаётся только при нажатии; приватный ответ никогда не попадает в кеш статьи.
		$sent = setcookie( self::cookie_name(), $value, array(
			'expires' => time() + YEAR_IN_SECONDS, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax',
		) );
		if ( $sent ) { $_COOKIE[ self::cookie_name() ] = $value; }
		return $sent;
	}

	public static function actor(): string {
		$cookie = self::cookie();
		return $cookie ? hash_hmac( 'sha256', $cookie, wp_salt( 'auth' ) ) : '';
	}

	public static function token( int $post_id ): string {
		if ( ! self::cookie() ) { return ''; }
		$expires = time() + 2 * HOUR_IN_SECONDS;
		return $expires . '.' . self::signature( self::cookie() . ':' . $post_id . ':' . $expires );
	}

	public static function verify( int $post_id, string $token ): bool {
		if ( ! self::cookie() || ! preg_match( '/^(\d{10})\.([a-f0-9]{64})$/D', $token, $parts ) ) { return false; }
		// Токен привязан к статье и подписанному браузеру, а не к общей гостевой сессии WordPress.
		if ( (int) $parts[1] < time() || (int) $parts[1] > time() + 2 * HOUR_IN_SECONDS ) { return false; }
		return hash_equals( self::signature( self::cookie() . ':' . $post_id . ':' . $parts[1] ), $parts[2] );
	}

	private static function signature( string $value ): string {
		return hash_hmac( 'sha256', home_url() . ':claps:' . $value, wp_salt( 'nonce' ) );
	}
}
