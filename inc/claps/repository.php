<?php
/** Атомарная запись личного счётчика и чтение агрегатов, без HTTP и представления. */
namespace IOBlog\Claps;

final class Repository {
	public function state( int $post_id, string $actor ): array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare(
			"SELECT COALESCE(SUM(claps),0) AS total, COALESCE(SUM(claps>0),0) AS readers,
			COALESCE(MAX(CASE WHEN actor=%s THEN claps ELSE 0 END),0) AS mine
			FROM {$wpdb->prefix}ioblog_claps WHERE post_id=%d", $actor, $post_id
		), ARRAY_A );
		if ( null === $row || $wpdb->last_error ) { throw new \RuntimeException( 'Claps storage unavailable' ); }
		return array_map( 'intval', $row );
	}

	public function change( int $post_id, string $actor, int $desired, int $expected ): bool {
		global $wpdb;
		$created = $wpdb->query( $wpdb->prepare(
			"INSERT IGNORE INTO {$wpdb->prefix}ioblog_claps (post_id,actor,claps) VALUES (%d,%s,0)", $post_id, $actor
		) );
		if ( false === $created ) { throw new \RuntimeException( 'Claps storage unavailable' ); }
		// Сравнение прежнего значения защищает от гонки вкладок; абсолютное значение делает повтор запроса безопасным.
		$changed = $wpdb->query( $wpdb->prepare(
			"UPDATE {$wpdb->prefix}ioblog_claps SET claps=%d WHERE post_id=%d AND actor=%s AND claps=%d",
			$desired, $post_id, $actor, $expected
		) );
		if ( false === $changed ) { throw new \RuntimeException( 'Claps storage unavailable' ); }
		return $changed > 0 || $this->state( $post_id, $actor )['mine'] === $desired;
	}

	public static function delete_post( int $post_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ioblog_claps', array( 'post_id' => $post_id ), array( '%d' ) );
	}
}
