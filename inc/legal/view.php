<?php
/** Кеш-безопасное представление: сторонний код остаётся инертным до явного выбора браузера. */
function ioblog_consent_allowed( $category ) {
	if ( ! Ioblog_Legal_Settings::get()['notice_enabled'] ) { return true; }
	$raw = $_COOKIE['io_privacy_choice'] ?? '';
	if ( ! is_string( $raw ) || strlen( $raw ) > 1024 ) { return false; }
	$choice = json_decode( wp_unslash( $raw ), true );
	// Cookie содержит только предпочтение, не право доступа: авторизация и CSRF проверяются независимо.
	return is_array( $choice ) && is_string( $choice['version'] ?? null ) && hash_equals( Ioblog_Legal_Settings::version(), $choice['version'] ) && is_numeric( $choice['at'] ?? null ) && $choice['at'] <= time() * 1000 && $choice['at'] > ( time() - 180 * DAY_IN_SECONDS ) * 1000 && true === ( $choice[ $category ] ?? false );
}
function ioblog_consent_wrap( $html, $category = 'analytics' ) {
	if ( ! Ioblog_Legal_Settings::get()['notice_enabled'] || '' === $html ) { return $html; }
	if ( ! in_array( $category, array( 'analytics', 'marketing' ), true ) ) { return ''; }
	// Base64 исключает закрывающий </template> внутри доверенного кода: он не может выйти из инертного контейнера.
	return '<template data-io-consent-content="' . esc_attr( $category ) . '" data-io-consent-html="' . esc_attr( base64_encode( $html ) ) . '"></template>';
}
add_action( 'wp_enqueue_scripts', static function () {
	if ( ! Ioblog_Legal_Settings::get()['notice_enabled'] ) { return; }
	wp_enqueue_style( 'ioblog-consent', get_theme_file_uri( 'assets/css/consent.css' ), array(), ioblog_asset_version( 'assets/css/consent.css' ) );
	wp_enqueue_script( 'ioblog-consent', get_theme_file_uri( 'assets/js/consent.js' ), array(), ioblog_asset_version( 'assets/js/consent.js' ), true );
	wp_localize_script( 'ioblog-consent', 'IOBlogConsentConfig', array( 'version' => Ioblog_Legal_Settings::version() ) );
} );
add_action( 'wp_footer', static function () {
	if ( Ioblog_Legal_Settings::get()['notice_enabled'] ) { require get_theme_file_path( 'template-parts/consent-notice.php' ); }
}, 5 );
