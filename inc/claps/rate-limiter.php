<?php
/** Короткие ограничения запросов с атомарным счётчиком, работающие без Redis. */
namespace IOBlog\Claps;

final class Rate_Limiter {
	public static function allow( string $subject, int $limit, int $seconds ): bool {
		global $wpdb;
		$window = (int) floor( time() / $seconds );
		$bucket = hash_hmac( 'sha256', $subject . ':' . $window, wp_salt( 'nonce' ) );
		$expires = ( $window + 1 ) * $seconds;
		// Удаляем истёкшие записи небольшими порциями; очистка не зависит от посещаемости WP-Cron.
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ioblog_clap_limits WHERE expires < %d LIMIT 100", time() ) );
		$result = $wpdb->query( $wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}ioblog_clap_limits (bucket,hits,expires) VALUES (%s,1,%d)
			ON DUPLICATE KEY UPDATE hits=LEAST(hits+1,1000000)", $bucket, $expires
		) );
		if ( false === $result ) { return false; }
		$hits = $wpdb->get_var( $wpdb->prepare( "SELECT hits FROM {$wpdb->prefix}ioblog_clap_limits WHERE bucket=%s", $bucket ) );
		return null !== $hits && (int) $hits <= $limit;
	}
}
