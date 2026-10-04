<?php
/** Настройки уведомлений и документов; юридические тексты остаются обычными страницами Gutenberg. */
final class Ioblog_Legal_Settings {
	public static function defaults() { return array( 'notice_enabled' => 1, 'require_consent' => 1, 'privacy_page' => 0, 'consent_page' => 0, 'disclosure_page' => 0, 'revision' => '1' ); }
	public static function get() {
		$settings = (array) get_option( 'ioblog_settings', array() );
		try { return self::validate( $settings['legal_config'] ?? array() ); }
		catch ( InvalidArgumentException $error ) { return self::defaults(); }
	}
	public static function validate( $input ) {
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), array_keys( self::defaults() ) ) ) { throw new InvalidArgumentException( 'legal' ); }
		$output = self::defaults();
		foreach ( $input as $key => $value ) {
			// Версия — непрозрачная безопасная метка; смена версии повторно спрашивает выбор cookies.
			if ( 'revision' === $key ) {
				if ( ! is_string( $value ) || ! preg_match( '/^[a-zA-Z0-9_.-]{1,32}$/D', $value ) ) { throw new InvalidArgumentException( $key ); }
				$output[ $key ] = $value;
			} else {
				$limit = str_ends_with( $key, '_page' ) ? PHP_INT_MAX : 1;
				if ( ! is_scalar( $value ) || ! is_numeric( $value ) || (float) $value !== (float) (int) $value || (int) $value < 0 || (int) $value > $limit ) { throw new InvalidArgumentException( $key ); }
				$output[ $key ] = (int) $value;
			}
		}
		$pages = array_filter( array( $output['privacy_page'], $output['consent_page'], $output['disclosure_page'] ) );
		if ( count( array_unique( $pages ) ) !== count( $pages ) ) { throw new InvalidArgumentException( 'separate-documents' ); }
		return $output;
	}
	public static function page( $kind ) {
		$config = self::get(); $id = $config[ $kind . '_page' ] ?? 0;
		if ( 'privacy' === $kind && ! $id ) { $id = get_option( 'wp_page_for_privacy_policy', 0 ); }
		$page = $id ? get_post( $id ) : null;
		return $page && 'page' === $page->post_type && 'publish' === $page->post_status && ! $page->post_password ? $page : null;
	}
	public static function version() {
		$parts = array( self::get()['revision'] );
		foreach ( array( 'privacy', 'consent', 'disclosure' ) as $kind ) {
			$page = self::page( $kind ); $parts[] = $page ? hash( 'sha256', $page->ID . '|' . $page->post_content . '|' . $page->post_title ) : '';
		}
		return hash( 'sha256', implode( '|', $parts ) );
	}
	public static function save( $input ) {
		$config = self::validate( $input ); $settings = (array) get_option( 'ioblog_settings', array() ); $settings['legal_config'] = $config;
		$priority = has_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings' );
		remove_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings' );
		try { update_option( 'ioblog_settings', $settings ); }
		finally { if ( false !== $priority ) { add_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings', $priority ); } }
		if ( ( get_option( 'ioblog_settings' )['legal_config'] ?? null ) !== $config ) { throw new RuntimeException( 'save' ); }
	}
}
