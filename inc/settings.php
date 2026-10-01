<?php
/**
 * Единый контракт настроек самостоятельной темы.
 *
 * @package IoblogEditorial
 */

/**
 * Возвращает безопасные значения по умолчанию для всех модулей темы.
 *
 * @return array<string, mixed>
 */
function ioblog_settings_defaults() {
	return apply_filters( 'ioblog_settings_defaults', array(
		'site_language'             => get_option( 'WPLANG', '' ) ?: 'en_US',
		'search_min_chars'          => 3,
		'search_limit'              => 8,
		'home_show_hero'            => 1,
		'home_show_categories'      => 1,
		'home_show_editorial'       => 1,
		'home_section_order'        => 'categories,editorial,latest',
		'home_posts_per_page'       => 12,
		'home_featured_post'        => 0,
		'home_editorial_posts'      => '',
		'home_about_page'           => 0,
		'typography_preset'         => 'editorial',
		'article_layout'            => 'sidebar',
		'reading_font_size'         => 18,
		'reading_line_height'       => 1.72,
		'reading_width'             => 790,
		'comments_images'           => 1,
		'comments_emojis'           => 1,
		'comment_image_max_mb'      => 5,
		'captcha_provider'          => 'builtin',
		'captcha_site_key'          => '',
		'captcha_secret_key'        => '',
		'social_telegram_url'       => '',
		'social_max_url'            => '',
		'social_vk_url'             => '',
		'social_ok_url'             => '',
		'svg_uploads'               => 1,
	) );
}

/**
 * Возвращает полный набор настроек с добавленными значениями по умолчанию.
 *
 * @return array<string, mixed>
 */
function ioblog_get_settings() {
	$settings = get_option( 'ioblog_settings', array() );
	$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), ioblog_settings_defaults() );
	// Язык может измениться в штатной админке WordPress; сохранённая копия темы не должна его откатывать.
	$settings['site_language'] = get_option( 'WPLANG', '' ) ?: 'en_US';
	return $settings;
}

/**
 * Возвращает одно значение, не раскрывая устройство хранилища другим модулям.
 *
 * @param string $key Ключ параметра.
 *
 * @return mixed
 */
function ioblog_get_setting( $key ) {
	$settings = ioblog_get_settings();
	return array_key_exists( $key, $settings ) ? $settings[ $key ] : null;
}

/**
 * Валидирует настройки бесплатной темы и передаёт расширениям их поля.
 *
 * @param array $input Значения формы.
 *
 * @return array<string, mixed>
 */
function ioblog_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$current  = ioblog_get_settings();
	// Сохранение Free-полей не удаляет данные отключённого Pro. Из запроса берутся только известные ключи.
	$settings = $current;

	$settings['site_language']        = ioblog_validate_site_language( $input['site_language'] ?? $current['site_language'] );
	$settings['search_min_chars']     = min( 6, max( 2, absint( $input['search_min_chars'] ?? 3 ) ) );
	$settings['search_limit']         = min( 20, max( 3, absint( $input['search_limit'] ?? 8 ) ) );
	$settings['home_posts_per_page']  = min( 24, max( 6, absint( $input['home_posts_per_page'] ?? 12 ) ) );
	$settings['home_featured_post']   = absint( $input['home_featured_post'] ?? 0 );
	$settings['home_about_page']      = absint( $input['home_about_page'] ?? 0 );
	$settings['reading_font_size']    = min( 22, max( 16, absint( $input['reading_font_size'] ?? 18 ) ) );
	$settings['reading_line_height']  = min( 2, max( 1.45, (float) ( $input['reading_line_height'] ?? 1.72 ) ) );
	$settings['reading_width']        = min( 920, max( 680, absint( $input['reading_width'] ?? 790 ) ) );
	$settings['comment_image_max_mb'] = min( 10, max( 1, absint( $input['comment_image_max_mb'] ?? 5 ) ) );

	foreach ( array( 'home_show_hero', 'home_show_categories', 'home_show_editorial', 'comments_images', 'comments_emojis', 'svg_uploads' ) as $checkbox ) {
		$settings[ $checkbox ] = empty( $input[ $checkbox ] ) ? 0 : 1;
	}

	$provider                    = sanitize_key( $input['captcha_provider'] ?? 'builtin' );
	$settings['captcha_provider'] = in_array( $provider, array( 'builtin', 'yandex', 'google' ), true ) ? $provider : 'builtin';
	$settings['captcha_site_key'] = sanitize_text_field( $input['captcha_site_key'] ?? $current['captcha_site_key'] );
	$secret                       = sanitize_text_field( $input['captcha_secret_key'] ?? '' );
	$settings['captcha_secret_key'] = $secret ? $secret : $current['captcha_secret_key'];

	$order = array_filter( array_map( 'sanitize_key', explode( ',', (string) ( $input['home_section_order'] ?? '' ) ) ) );
	$settings['home_section_order'] = implode( ',', array_values( array_intersect( $order, array( 'categories', 'editorial', 'latest' ) ) ) );
	// Множественный выбор приходит массивом; строковый формат остаётся контрактом хранилища.
	$editorial_input = $input['home_editorial_posts'] ?? array();
	$editorial_ids = is_array( $editorial_input ) ? $editorial_input : preg_split( '/[\s,]+/', (string) $editorial_input );
	$editorial = array_slice( array_values( array_unique( array_filter( array_map( 'absint', $editorial_ids ) ) ) ), 0, 5 );
	$settings['home_editorial_posts'] = implode( ',', $editorial );
	$preset = sanitize_key( $input['typography_preset'] ?? 'editorial' );
	$settings['typography_preset'] = in_array( $preset, ioblog_typography_presets(), true ) ? $preset : 'editorial';
	$layout = sanitize_key( $input['article_layout'] ?? 'sidebar' );
	$settings['article_layout'] = in_array( $layout, ioblog_article_layouts(), true ) ? $layout : 'sidebar';

	foreach ( array( 'social_telegram_url', 'social_max_url', 'social_vk_url', 'social_ok_url' ) as $social_url ) {
		$settings[ $social_url ] = esc_url_raw( $input[ $social_url ] ?? '' );
	}

	return apply_filters( 'ioblog_sanitize_settings', $settings, $input, $current );
}
