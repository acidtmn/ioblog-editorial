<?php
/**
 * Однократный перенос пользовательских настроек при активации темы.
 *
 * @package IoblogEditorial
 */

/**
 * Переносит только данные владельца сайта, не копируя настройки и код старой темы.
 *
 * @return void
 */
function ioblog_migrate_existing_theme_settings() {
	$legacy = get_option( 'theme_mods_blog-child', array() );
	if ( ! is_array( $legacy ) ) {
		$legacy = array();
	}

	$mapping = array(
		'footer_text'             => 'ioblog_footer_text',
		'footer_copyright'        => 'ioblog_footer_copyright',
		'home_hero_subheading'    => 'ioblog_hero_description',
		'header_search_heading'   => 'ioblog_search_heading',
		'color_scheme'            => 'ioblog_color_scheme',
	);

	foreach ( $mapping as $legacy_key => $new_key ) {
		if ( isset( $legacy[ $legacy_key ] ) && '' !== $legacy[ $legacy_key ] && ! get_theme_mod( $new_key, '' ) ) {
			set_theme_mod( $new_key, $legacy[ $legacy_key ] );
		}
	}

	$locations = isset( $legacy['nav_menu_locations'] ) && is_array( $legacy['nav_menu_locations'] ) ? $legacy['nav_menu_locations'] : array();
	set_theme_mod(
		'nav_menu_locations',
		array(
			'primary' => isset( $locations['mobile'] ) ? (int) $locations['mobile'] : ( isset( $locations['primary'] ) ? (int) $locations['primary'] : 0 ),
			'footer'  => isset( $locations['footer'] ) ? (int) $locations['footer'] : 0,
		)
	);

	set_theme_mod( 'ioblog_migration_complete', 1 );
}
add_action( 'after_switch_theme', 'ioblog_migrate_existing_theme_settings' );

/**
 * Однократно переносит полезные данные удаляемых плагинов в единый option темы.
 *
 * @return void
 */
function ioblog_migrate_plugin_features() {
	if ( ! ioblog_has_pro() || (int) get_option( 'ioblog_feature_migration_version', 0 ) >= 1 ) {
		return;
	}

	global $wpdb;
	$settings = ioblog_get_settings();

	$table = $wpdb->prefix . 'prli_links';
	if ( '' === $settings['redirects'] && $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) ) {
		$links = $wpdb->get_results( "SELECT slug, url, redirect_type FROM {$table} ORDER BY id ASC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Имя таблицы собрано из доверенного prefix.
		$lines = array();
		foreach ( $links as $link ) {
			$lines[] = sanitize_title( $link->slug ) . '|' . esc_url_raw( $link->url ) . '|' . (int) $link->redirect_type;
		}
		$settings['redirects'] = implode( "\n", $lines );
	}

	$telegram = get_option( 'tgiv_instantview_render', array() );
	if ( is_array( $telegram ) && ! empty( $telegram['tgiv_channel_name'] ) ) {
		$channel = ltrim( sanitize_text_field( $telegram['tgiv_channel_name'] ), '@' );
		if ( preg_match( '/[a-z_]/i', $channel ) ) {
			$settings['telegram_channel'] = $channel;
		}
	}

	if ( false === get_option( 'ioblog_settings', false ) ) {
		add_option( 'ioblog_settings', $settings, '', false );
	} else {
		update_option( 'ioblog_settings', $settings, false );
	}

	update_option( 'ioblog_feature_migration_version', 1, false );
}
add_action( 'init', 'ioblog_migrate_plugin_features', 1 );
