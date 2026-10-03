<?php
/** Экспорт, предварительный просмотр и одноразовое подтверждение переноса настроек. */
final class Ioblog_Settings_Transfer_Service {
	public static function export() {
		$data = Ioblog_Settings_Transfer_Repository::snapshot();
		$data['created_at'] = gmdate( 'c' );
		$data['references'] = Ioblog_Settings_Transfer_References::collect( $data );
		$json = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION );
		if ( ! is_string( $json ) || strlen( $json ) > Ioblog_Settings_Transfer_Schema::LIMIT ) { throw new RuntimeException( 'size' ); }
		return $json;
	}

	private static function rows( $data ) {
		$current = Ioblog_Settings_Transfer_Repository::snapshot();
		$rows = array();
		foreach ( array( 'settings', 'mods', 'identity' ) as $section ) {
			foreach ( $data[ $section ] ?? array() as $key => $value ) {
				if ( ( $current[ $section ][ $key ] ?? null ) !== $value ) {
					$rows[] = array( 'group' => Ioblog_Settings_Transfer_Schema::group( $key, $section ), 'key' => $section . '.' . $key, 'label' => Ioblog_Settings_Transfer_Presentation::label( $key, $section ), 'before' => Ioblog_Settings_Transfer_Presentation::summary( $key, $current[ $section ][ $key ] ?? '' ), 'after' => Ioblog_Settings_Transfer_Presentation::summary( $key, $value ) );
				}
			}
		}
		foreach ( array( 'custom_css', 'site_icon' ) as $key ) {
			if ( isset( $data[ $key ] ) && $data[ $key ] !== $current[ $key ] ) {
				$rows[] = array( 'group' => 'appearance', 'key' => $key, 'label' => Ioblog_Settings_Transfer_Presentation::label( $key, 'mods' ), 'before' => Ioblog_Settings_Transfer_Presentation::summary( $key, $current[ $key ] ), 'after' => Ioblog_Settings_Transfer_Presentation::summary( $key, $data[ $key ] ) );
			}
		}
		return $rows;
	}

	private static function requires_code_permission( $data, $groups ) {
		foreach ( $data['settings'] as $key => $value ) {
			if ( in_array( Ioblog_Settings_Transfer_Schema::group( $key ), $groups, true ) && is_string( $value ) && ( str_contains( $value, '<' ) || ( str_ends_with( $key, '_code' ) && ! empty( $value ) ) ) ) { return true; }
		}
		if ( in_array( 'appearance', $groups, true ) ) {
			foreach ( $data['mods'] as $value ) {
				if ( is_string( $value ) && str_contains( $value, '<' ) ) { return true; }
			}
		}
		return false;
	}

	public static function preview( $json ) {
		$data = Ioblog_Settings_Transfer_Schema::decode( $json );
		$resolved = Ioblog_Settings_Transfer_References::resolve( $data );
		$rows = self::rows( $resolved['data'] );
		// После сопоставления храним уже местные идентификаторы и их местные подписи.
		$resolved['data']['site_url'] = home_url( '/' );
		$resolved['data']['references'] = Ioblog_Settings_Transfer_References::collect( $resolved['data'] );
		$token = bin2hex( random_bytes( 24 ) );
		// Одна копия на пользователя, только в серверном transient. Ключи не возвращаются в AJAX-ответе.
		$stored = array( 'token' => $token, 'user' => get_current_user_id(), 'fingerprint' => Ioblog_Settings_Transfer_Repository::fingerprint(), 'data' => $resolved['data'], 'expires' => time() + 600 );
		if ( ! set_transient( 'ioblog_transfer_' . get_current_user_id(), $stored, 600 ) ) { throw new RuntimeException( 'preview' ); }
		return array( 'token' => $token, 'rows' => $rows, 'skipped' => $resolved['skipped'], 'source' => esc_url_raw( $data['site_url'] ), 'code_permission' => current_user_can( 'unfiltered_html' ), 'pro_active' => ioblog_has_pro() );
	}

	public static function confirm( $token, $groups ) {
		if ( ! is_string( $token ) || ! preg_match( '/^[a-f0-9]{48}$/D', $token ) || ! is_array( $groups ) || ! $groups || count( $groups ) > 7 || count( array_filter( $groups, 'is_string' ) ) !== count( $groups ) || array_diff( $groups, array_keys( Ioblog_Settings_Transfer_Schema::groups() ) ) ) { throw new InvalidArgumentException( 'confirmation' ); }
		$key = 'ioblog_transfer_' . get_current_user_id();
		$stored = get_transient( $key );
		if ( ! is_array( $stored ) || $stored['user'] !== get_current_user_id() || $stored['expires'] < time() || ! hash_equals( $stored['token'], $token ) ) { throw new RuntimeException( 'expired' ); }
		if ( self::requires_code_permission( $stored['data'], $groups ) && ! current_user_can( 'unfiltered_html' ) ) { throw new RuntimeException( 'permission' ); }
		// Блокировка защищает от двух одновременных подтверждений одного предварительного просмотра.
		if ( ! add_option( 'ioblog_transfer_lock', time(), '', false ) ) {
			if ( (int) get_option( 'ioblog_transfer_lock' ) < time() - 120 ) { delete_option( 'ioblog_transfer_lock' ); }
			throw new RuntimeException( 'busy' );
		}
		try {
			if ( ! hash_equals( $stored['fingerprint'], Ioblog_Settings_Transfer_Repository::fingerprint() ) ) { throw new RuntimeException( 'stale' ); }
			// Снова проверяем объекты: статью могли удалить после предварительного просмотра.
			$resolved = Ioblog_Settings_Transfer_References::resolve( $stored['data'] );
			if ( $resolved['data'] !== $stored['data'] ) { throw new RuntimeException( 'stale' ); }
			$rows = array_filter( self::rows( $stored['data'] ), static function ( $row ) use ( $groups ) { return in_array( $row['group'], $groups, true ); } );
			delete_transient( $key );
			if ( $rows ) { Ioblog_Settings_Transfer_Repository::apply( $stored['data'], $groups ); }
			return array( 'count' => count( $rows ) );
		} finally { delete_option( 'ioblog_transfer_lock' ); }
	}

	public static function error_message( $error ) {
		$messages = array(
			'stale' => __( 'Settings changed after preview. Select the file again.', 'ioblog-editorial' ),
			'expired' => __( 'Preview expired. Select the file again.', 'ioblog-editorial' ),
			'permission' => __( 'Importing advertising or analytics code requires unfiltered HTML permission.', 'ioblog-editorial' ),
			'busy' => __( 'Another import is running. Please try again shortly.', 'ioblog-editorial' ),
			'write' => __( 'Settings could not be saved. Check the database and export the current settings before retrying.', 'ioblog-editorial' ),
			'css' => __( 'Custom CSS could not be saved. Check the database before retrying.', 'ioblog-editorial' ),
		);
		return $messages[ $error->getMessage() ] ?? __( 'Invalid backup. Use an IO Blog settings JSON file up to 1 MB. Check field types and install its WordPress language pack first.', 'ioblog-editorial' );
	}
}
