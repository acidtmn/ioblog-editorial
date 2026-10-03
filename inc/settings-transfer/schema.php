<?php
/** Контракт переносимой конфигурации: только данные, без произвольных options WordPress. */
final class Ioblog_Settings_Transfer_Schema {
	const FORMAT = 'ioblog-theme-settings';
	const LIMIT = 1048576;

	public static function groups() {
		return array(
			'general' => __( 'Interface and language', 'ioblog-editorial' ),
			'homepage' => __( 'Homepage', 'ioblog-editorial' ),
			'reading' => __( 'Reading and typography', 'ioblog-editorial' ),
			'comments' => __( 'Comments and CAPTCHA', 'ioblog-editorial' ),
			'ads' => __( 'Advertising slots', 'ioblog-editorial' ),
			'integrations' => __( 'Integrations', 'ioblog-editorial' ),
			'appearance' => __( 'Appearance', 'ioblog-editorial' ),
		);
	}

	public static function group( $key, $section = 'settings' ) {
		if ( 'settings' !== $section ) { return 'appearance'; }
		if ( 'design_config' === $key ) { return 'appearance'; }
		if ( 'community_config' === $key ) { return 'integrations'; }
		if ( str_starts_with( $key, 'ad_' ) ) { return 'ads'; }
		if ( preg_match( '/^(captcha_|comments_|comment_image_|svg_uploads)/', $key ) ) { return 'comments'; }
		if ( str_starts_with( $key, 'home_' ) ) { return 'homepage'; }
		if ( preg_match( '/^(reading_|typography_|article_layout|claps_|library_)/', $key ) ) { return 'reading'; }
		if ( preg_match( '/^(social_|telegram_|head_code|footer_code|redirects|interests_)/', $key ) ) { return 'integrations'; }
		return 'general';
	}

	/** Ограничение глубины и объёма не даёт чужому JSON превратить проверку в расход памяти. */
	public static function value( $value, $depth = 0 ) {
		if ( $depth > 8 ) { throw new InvalidArgumentException( 'depth' ); }
		if ( is_array( $value ) ) {
			if ( count( $value ) > 512 ) { throw new InvalidArgumentException( 'size' ); }
			foreach ( $value as $key => $item ) {
				if ( ! is_int( $key ) && ! preg_match( '/^[a-zA-Z0-9_.-]{1,100}$/D', $key ) ) { throw new InvalidArgumentException( 'key' ); }
				self::value( $item, $depth + 1 );
			}
			return;
		}
		if ( is_string( $value ) && strlen( $value ) <= 524288 && ! str_contains( $value, "\0" ) ) { return; }
		if ( is_int( $value ) || is_bool( $value ) || null === $value || ( is_float( $value ) && is_finite( $value ) ) ) { return; }
		throw new InvalidArgumentException( 'value' );
	}

	/** Проверяем известные поля строго, не обрезая молча рекламный HTML или секретные ключи. */
	public static function settings( $settings ) {
		self::value( $settings );
		if ( ! is_array( $settings ) || count( $settings ) > 256 ) { throw new InvalidArgumentException( 'settings' ); }
		$defaults = ioblog_settings_defaults();
		$pro_keys = array( 'ad_home_enabled', 'ad_home_code', 'ad_inline_enabled', 'ad_inline_code', 'ad_inline_paragraph', 'ad_sidebar_enabled', 'ad_sidebar_code', 'ad_after_enabled', 'ad_after_code', 'redirects', 'head_code', 'footer_code', 'telegram_channel', 'telegram_instant_view', 'social_covers_enabled', 'interests_enabled' );
		$allowed = array_merge( array_keys( $defaults ), array_keys( (array) get_option( 'ioblog_settings', array() ) ), $pro_keys, array( 'community_config', 'ad_rules' ) );
		foreach ( $settings as $key => $value ) {
			if ( ! is_string( $key ) || ! preg_match( '/^[a-z][a-z0-9_]{0,99}$/D', $key ) ) { throw new InvalidArgumentException( 'key' ); }
			if ( ! in_array( $key, $allowed, true ) ) { throw new InvalidArgumentException( 'unsupported' ); }
			if ( 'design_config' === $key ) { Ioblog_Design_Schema::validate( $value ); }
			if ( in_array( $key, array( 'community_config', 'ad_rules' ), true ) && ! is_array( $value ) ) { throw new InvalidArgumentException( $key ); }
			if ( 'community_config' === $key && class_exists( 'Ioblog_Community_Settings' ) ) { Ioblog_Community_Settings::validate( $value ); }
			if ( 'ad_rules' === $key && class_exists( 'Ioblog_Ad_Rules' ) ) { Ioblog_Ad_Rules::validate( $value ); }
			if ( in_array( $key, $pro_keys, true ) && ! str_ends_with( $key, '_enabled' ) && 'telegram_instant_view' !== $key && 'ad_inline_paragraph' !== $key && ! is_string( $value ) ) { throw new InvalidArgumentException( $key ); }
			if ( 'ad_inline_paragraph' === $key && ( ! is_int( $value ) || $value < 1 || $value > 20 ) ) { throw new InvalidArgumentException( $key ); }
			if ( isset( $defaults[ $key ] ) ) {
				$default = $defaults[ $key ];
				if ( ( is_string( $default ) && ! is_string( $value ) ) || ( is_int( $default ) && ! is_int( $value ) ) || ( is_float( $default ) && ! is_numeric( $value ) ) ) { throw new InvalidArgumentException( $key ); }
			}
			if ( preg_match( '/(_enabled|^home_show_|^comments_|^svg_uploads$|^telegram_instant_view$)/', $key ) && ! in_array( $value, array( 0, 1 ), true ) ) { throw new InvalidArgumentException( $key ); }
		}
		$ranges = array( 'search_min_chars' => array( 2, 6 ), 'search_limit' => array( 3, 20 ), 'home_posts_per_page' => array( 6, 24 ), 'reading_font_size' => array( 16, 22 ), 'reading_line_height' => array( 1.45, 2 ), 'reading_width' => array( 680, 920 ), 'comment_image_max_mb' => array( 1, 10 ) );
		foreach ( $ranges as $key => $range ) {
			if ( isset( $settings[ $key ] ) && ( ! is_int( $settings[ $key ] ) && ! is_float( $settings[ $key ] ) || $settings[ $key ] < $range[0] || $settings[ $key ] > $range[1] ) ) { throw new InvalidArgumentException( $key ); }
		}
		$enums = array( 'typography_preset' => ioblog_typography_presets(), 'article_layout' => ioblog_article_layouts(), 'captcha_provider' => array( 'builtin', 'yandex', 'google' ), 'site_language' => array_merge( array( 'en_US' ), get_available_languages() ) );
		foreach ( $enums as $key => $choices ) {
			if ( isset( $settings[ $key ] ) && ! in_array( $settings[ $key ], $choices, true ) ) { throw new InvalidArgumentException( $key ); }
		}
		foreach ( array( 'home_featured_post', 'home_about_page' ) as $key ) {
			if ( isset( $settings[ $key ] ) && ( ! is_int( $settings[ $key ] ) || $settings[ $key ] < 0 ) ) { throw new InvalidArgumentException( $key ); }
		}
		if ( isset( $settings['home_editorial_posts'] ) && ! preg_match( '/^(?:[1-9][0-9]*(?:,[1-9][0-9]*){0,4})?$/D', $settings['home_editorial_posts'] ) ) { throw new InvalidArgumentException( 'home_editorial_posts' ); }
		if ( isset( $settings['home_section_order'] ) ) {
			$order = '' === $settings['home_section_order'] ? array() : explode( ',', $settings['home_section_order'] );
			if ( array_diff( $order, array( 'categories', 'editorial', 'latest' ) ) || count( $order ) !== count( array_unique( $order ) ) ) { throw new InvalidArgumentException( 'home_section_order' ); }
		}
		foreach ( array( 'social_telegram_url', 'social_max_url', 'social_vk_url', 'social_ok_url' ) as $key ) {
			if ( ! empty( $settings[ $key ] ) && ( ! is_string( $settings[ $key ] ) || ! in_array( wp_parse_url( $settings[ $key ], PHP_URL_SCHEME ), array( 'http', 'https' ), true ) || ! wp_parse_url( $settings[ $key ], PHP_URL_HOST ) || esc_url_raw( $settings[ $key ] ) !== $settings[ $key ] ) ) { throw new InvalidArgumentException( $key ); }
		}
		if ( ! empty( $settings['redirects'] ) ) {
			foreach ( preg_split( '/\R/u', $settings['redirects'] ) as $line ) {
				$parts = explode( '|', $line );
				if ( 3 !== count( $parts ) || ! $parts[0] || sanitize_title( $parts[0] ) !== $parts[0] || ! in_array( $parts[2], array( '301', '302', '307', '308' ), true ) || ! in_array( wp_parse_url( $parts[1], PHP_URL_SCHEME ), array( 'http', 'https' ), true ) || ! wp_parse_url( $parts[1], PHP_URL_HOST ) || esc_url_raw( $parts[1], array( 'http', 'https' ) ) !== $parts[1] ) { throw new InvalidArgumentException( 'redirects' ); }
			}
		}
	}

	public static function decode( $json ) {
		if ( ! is_string( $json ) || strlen( $json ) > self::LIMIT ) { throw new InvalidArgumentException( 'size' ); }
		$data = json_decode( $json, true, 16, JSON_THROW_ON_ERROR );
		if ( ! is_array( $data ) || self::FORMAT !== ( $data['format'] ?? null ) || 1 !== ( $data['version'] ?? null ) || 'ioblog-editorial' !== ( $data['theme'] ?? null ) ) { throw new InvalidArgumentException( 'format' ); }
		if ( array_diff( array_keys( $data ), array( 'format', 'version', 'theme', 'theme_version', 'site_url', 'created_at', 'settings', 'mods', 'custom_css', 'site_icon', 'references', 'identity' ) ) ) { throw new InvalidArgumentException( 'fields' ); }
		self::settings( $data['settings'] ?? null );
		self::value( $data['mods'] ?? null );
		self::value( $data['references'] ?? null );
		if ( ! is_array( $data['mods'] ?? null ) || ! is_array( $data['references'] ?? null ) || ! is_string( $data['site_url'] ?? null ) || ! is_string( $data['custom_css'] ?? null ) || ! is_int( $data['site_icon'] ?? null ) || $data['site_icon'] < 0 ) { throw new InvalidArgumentException( 'fields' ); }
		if ( ! in_array( wp_parse_url( $data['site_url'], PHP_URL_SCHEME ), array( 'http', 'https' ), true ) || ! wp_parse_url( $data['site_url'], PHP_URL_HOST ) ) { throw new InvalidArgumentException( 'site' ); }
		self::value( $data['custom_css'] );
		if ( isset( $data['identity'] ) ) {
			if ( ! is_array( $data['identity'] ) || array_diff( array_keys( $data['identity'] ), array( 'blogname', 'blogdescription' ) ) ) { throw new InvalidArgumentException( 'identity' ); }
			foreach ( $data['identity'] as $value ) { if ( ! is_string( $value ) || strlen( $value ) > 4096 || str_contains( $value, "\0" ) ) { throw new InvalidArgumentException( 'identity' ); } }
		}
		if ( isset( $data['mods']['ioblog_color_scheme'] ) && ! in_array( $data['mods']['ioblog_color_scheme'], array( 'system', 'light', 'dark' ), true ) ) { throw new InvalidArgumentException( 'color' ); }
		foreach ( $data['mods'] as $key => $value ) {
			if ( ! is_string( $key ) || ! preg_match( '/^[a-z][a-z0-9_]{0,99}$/D', $key ) ) { throw new InvalidArgumentException( 'mod' ); }
			if ( 'nav_menu_locations' === $key ) {
				if ( ! is_array( $value ) ) { throw new InvalidArgumentException( 'menus' ); }
				foreach ( $value as $id ) { if ( ! is_int( $id ) || $id < 0 ) { throw new InvalidArgumentException( 'menu' ); } }
			} elseif ( in_array( $key, array( 'custom_logo', 'custom_css_post_id' ), true ) && ( ! is_int( $value ) || $value < ( 'custom_css_post_id' === $key ? -1 : 0 ) ) ) { throw new InvalidArgumentException( 'media' ); }
		}
		return $data;
	}
}
