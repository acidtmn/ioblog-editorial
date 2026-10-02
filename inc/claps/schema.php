<?php
/** Схема хранения реакций: смена темы не удаляет накопленные данные. */
namespace IOBlog\Claps;

final class Schema {
	public static function install() {
		if ( '1' === get_option( 'ioblog_claps_schema' ) ) { return; }
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$collate = $wpdb->get_charset_collate();
		// Составной ключ не позволяет параллельным запросам создать две реакции одного браузера.
		dbDelta( "CREATE TABLE {$wpdb->prefix}ioblog_claps (
			post_id bigint(20) unsigned NOT NULL,
			actor char(64) NOT NULL,
			claps tinyint(3) unsigned NOT NULL DEFAULT 0,
			PRIMARY KEY  (post_id,actor)
		) $collate;" );
		// Временные ограничения живут отдельно и не превращаются в постоянную историю IP-адресов.
		dbDelta( "CREATE TABLE {$wpdb->prefix}ioblog_clap_limits (
			bucket char(64) NOT NULL,
			hits int(10) unsigned NOT NULL DEFAULT 0,
			expires bigint(20) unsigned NOT NULL,
			PRIMARY KEY  (bucket),
			KEY expires (expires)
		) $collate;" );
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'ioblog_claps' ) ) ) && $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . 'ioblog_clap_limits' ) ) ) ) {
			update_option( 'ioblog_claps_schema', '1', false );
		}
	}
}
