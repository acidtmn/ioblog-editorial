<?php
/** Чтение и восстановление конфигурации через API WordPress, без доступа к чужим options. */
final class Ioblog_Settings_Transfer_Repository {
	public static function snapshot() {
		return array(
			'format' => Ioblog_Settings_Transfer_Schema::FORMAT, 'version' => 1, 'theme' => 'ioblog-editorial',
			'theme_version' => wp_get_theme()->get( 'Version' ), 'site_url' => home_url( '/' ),
			'settings' => ioblog_get_settings(), 'mods' => get_theme_mods() ?: array(),
			'custom_css' => wp_get_custom_css(), 'site_icon' => (int) get_option( 'site_icon', 0 ),
			'identity' => array( 'blogname' => (string) get_option( 'blogname' ), 'blogdescription' => (string) get_option( 'blogdescription' ) ),
		);
	}

	public static function fingerprint() {
		return hash( 'sha256', wp_json_encode( self::snapshot() ) );
	}

	private static function write_option( $key, $value ) {
		$current = get_option( $key );
		// Числовые одиночные options ядро читает строками после загрузки из SQL.
		if ( $current === $value || ( is_scalar( $current ) && is_scalar( $value ) && (string) $current === (string) $value ) ) { return; }
		update_option( $key, $value );
		$current = get_option( $key );
		if ( $current !== $value && ! ( is_scalar( $current ) && is_scalar( $value ) && (string) $current === (string) $value ) ) { throw new RuntimeException( 'write' ); }
	}

	/** Полная форма и импорт имеют разный контракт: её sanitizer сбрасывает отсутствующие флажки. */
	public static function apply( $data, $groups ) {
		$settings_before = get_option( 'ioblog_settings', array() );
		$mods_key = 'theme_mods_' . get_stylesheet();
		$mods_before = get_option( $mods_key, array() );
		$language_before = get_option( 'WPLANG', '' );
		$locale_before = get_user_meta( get_current_user_id(), 'locale', true );
		$icon_before = get_option( 'site_icon', 0 );
		$css_before = wp_get_custom_css();
		$css_post_before = wp_get_custom_css_post();
		$identity_before = array( 'blogname' => get_option( 'blogname' ), 'blogdescription' => get_option( 'blogdescription' ) );
		$settings = is_array( $settings_before ) ? $settings_before : array();
		$mods = is_array( $mods_before ) ? $mods_before : array();
		foreach ( $data['settings'] as $key => $value ) {
			if ( in_array( Ioblog_Settings_Transfer_Schema::group( $key ), $groups, true ) ) { $settings[ $key ] = $value; }
		}
		// Если группа языка не выбрана, старая копия site_language не должна менять WPLANG и профиль.
		if ( ! in_array( 'general', $groups, true ) ) { $settings['site_language'] = $language_before ?: 'en_US'; }
		if ( in_array( 'appearance', $groups, true ) ) {
			foreach ( $data['mods'] as $key => $value ) {
				$mods[ $key ] = 'nav_menu_locations' === $key ? array_replace( $mods[ $key ] ?? array(), $value ) : $value;
			}
		}
		$priority = has_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings' );
		if ( false !== $priority ) { remove_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings', $priority ); }
		try {
			self::write_option( 'ioblog_settings', $settings );
			if ( in_array( 'appearance', $groups, true ) ) {
				self::write_option( $mods_key, $mods );
				if ( isset( $data['site_icon'] ) ) { self::write_option( 'site_icon', $data['site_icon'] ); }
				foreach ( $data['identity'] ?? array() as $key => $value ) { self::write_option( $key, $value ); }
				if ( $css_before !== $data['custom_css'] ) {
					$result = wp_update_custom_css_post( $data['custom_css'] );
					if ( is_wp_error( $result ) || wp_get_custom_css() !== $data['custom_css'] ) { throw new RuntimeException( 'css' ); }
				}
			}
		} catch ( Throwable $error ) {
			// При ошибке второй записи возвращаем первую, чтобы не оставлять обычный частичный импорт.
			update_option( 'ioblog_settings', $settings_before );
			update_option( $mods_key, $mods_before );
			update_option( 'WPLANG', $language_before );
			update_user_meta( get_current_user_id(), 'locale', $locale_before );
			update_option( 'site_icon', $icon_before );
			foreach ( $identity_before as $key => $value ) { update_option( $key, $value ); }
			if ( wp_get_custom_css() !== $css_before ) { wp_update_custom_css_post( $css_before ); }
			$css_post_after = wp_get_custom_css_post();
			if ( ! $css_post_before && $css_post_after ) { wp_delete_post( $css_post_after->ID, true ); }
			update_option( $mods_key, $mods_before );
			throw $error;
		} finally {
			if ( false !== $priority ) { add_filter( 'sanitize_option_ioblog_settings', 'ioblog_sanitize_settings', $priority ); }
		}
	}
}
