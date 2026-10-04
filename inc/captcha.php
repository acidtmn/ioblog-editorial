<?php
/**
 * Выбираемая CAPTCHA для комментариев и публичных форм авторизации.
 *
 * @package IoblogEditorial
 */

/**
 * Возвращает внешний провайдер только при наличии обоих ключей.
 *
 * @return string
 */
function ioblog_active_captcha_provider() {
	$provider = (string) ioblog_get_setting( 'captcha_provider' );
	$site_key = (string) ioblog_get_setting( 'captcha_site_key' );
	$secret   = (string) ioblog_get_setting( 'captcha_secret_key' );

	return in_array( $provider, array( 'yandex', 'google' ), true ) && $site_key && $secret ? $provider : 'builtin';
}

/**
 * Регистрирует скрипт активного внешнего провайдера на текущем экране.
 *
 * @return void
 */
function ioblog_enqueue_captcha_provider_script() {
	$provider = ioblog_active_captcha_provider();
	if ( 'yandex' === $provider ) {
		wp_enqueue_script( 'ioblog-yandex-captcha', 'https://smartcaptcha.cloud.yandex.ru/captcha.js', array(), null, array( 'strategy' => 'defer', 'in_footer' => true ) );
	} elseif ( 'google' === $provider ) {
		$language = strtolower( substr( determine_locale(), 0, 2 ) );
		wp_enqueue_script( 'ioblog-google-captcha', add_query_arg( 'hl', $language, 'https://www.google.com/recaptcha/api.js' ), array(), null, array( 'strategy' => 'async', 'in_footer' => true ) );
	}
}

/**
 * Подключает CAPTCHA только на записях с доступной гостевой формой комментариев.
 *
 * @return void
 */
function ioblog_enqueue_comment_captcha() {
	if ( ! is_singular() || ! comments_open() || is_user_logged_in() ) {
		return;
	}

	ioblog_enqueue_captcha_provider_script();
}
add_action( 'wp_enqueue_scripts', 'ioblog_enqueue_comment_captcha' );

/**
 * Подключает CAPTCHA и локальные стили в штатные формы входа WordPress.
 *
 * @return void
 */
function ioblog_enqueue_login_captcha() {
	ioblog_enqueue_captcha_provider_script();
	wp_enqueue_style( 'ioblog-login-captcha', get_theme_file_uri( 'assets/css/login.css' ), array(), ioblog_asset_version( 'assets/css/login.css' ) );
}
add_action( 'login_enqueue_scripts', 'ioblog_enqueue_login_captcha' );

/**
 * Возвращает контейнер выбранного виджета или пояснение о встроенной защите.
 *
 * @return string
 */
function ioblog_captcha_field() {
	if ( is_user_logged_in() ) {
		return '';
	}

	$provider = ioblog_active_captcha_provider();
	$site_key = (string) ioblog_get_setting( 'captcha_site_key' );

	if ( 'yandex' === $provider ) {
		return '<div class="io-captcha smart-captcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
	}

	if ( 'google' === $provider ) {
		return '<div class="io-captcha g-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '"></div>';
	}

	return '<p class="io-captcha-note">' . esc_html__( 'This form is protected by the built-in IO Blog anti-spam.', 'ioblog-editorial' ) . '</p>';
}

/**
 * Выводит внешний виджет в формах входа, регистрации и восстановления пароля.
 *
 * @return void
 */
function ioblog_render_auth_captcha_field() {
	if ( 'builtin' === ioblog_active_captcha_provider() ) {
		return;
	}

	echo '<div class="io-login-captcha">' . ioblog_captcha_field() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Функция возвращает собственную безопасную разметку темы.
}
add_action( 'login_form', 'ioblog_render_auth_captcha_field' );
add_action( 'register_form', 'ioblog_render_auth_captcha_field' );
add_action( 'lostpassword_form', 'ioblog_render_auth_captcha_field' );

/**
 * Возвращает адрес пользователя без доверия к подменяемым proxy-заголовкам.
 *
 * @return string
 */
function ioblog_comment_remote_ip() {
	return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
}

/**
 * Проверяет присланный токен у активного внешнего сервиса.
 *
 * При сетевой ошибке сайт остаётся доступным: временная недоступность API
 * CAPTCHA не должна блокировать владельцу вход в административную панель.
 *
 * @return true|WP_Error
 */
function ioblog_validate_captcha_response() {
	$provider = ioblog_active_captcha_provider();
	if ( 'builtin' === $provider ) {
		return true;
	}

	$secret = (string) ioblog_get_setting( 'captcha_secret_key' );
	$ip     = ioblog_comment_remote_ip();
	if ( 'yandex' === $provider ) {
		$token    = sanitize_text_field( wp_unslash( $_POST['smart-token'] ?? '' ) );
		$endpoint = 'https://smartcaptcha.cloud.yandex.ru/validate';
		$body     = array( 'secret' => $secret, 'token' => $token, 'ip' => $ip );
	} else {
		$token    = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ?? '' ) );
		$endpoint = 'https://www.google.com/recaptcha/api/siteverify';
		$body     = array( 'secret' => $secret, 'response' => $token, 'remoteip' => $ip );
	}

	if ( '' === $token ) {
		return new WP_Error( 'ioblog_captcha_required', __( 'Confirm that you are not a robot.', 'ioblog-editorial' ) );
	}

	$response = wp_remote_post( $endpoint, array( 'timeout' => 4, 'body' => $body ) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return true;
	}

	$result = json_decode( wp_remote_retrieve_body( $response ), true );
	$passed = 'yandex' === $provider ? isset( $result['status'] ) && 'ok' === $result['status'] : ! empty( $result['success'] );
	$host   = 'yandex' === $provider ? ( $result['host'] ?? '' ) : ( $result['hostname'] ?? '' );
	$site   = wp_parse_url( home_url( '/' ) );
	$domain = ( $site['host'] ?? '' ) . ( 'yandex' === $provider && ! empty( $site['port'] ) ? ':' . $site['port'] : '' );

	if ( ! is_string( $host ) || ( $passed && $host && $domain && strtolower( $host ) !== strtolower( $domain ) ) ) {
		$passed = false;
	}

	return $passed ? true : new WP_Error( 'ioblog_captcha_failed', __( 'CAPTCHA verification failed. Refresh the page and try again.', 'ioblog-editorial' ) );
}

/**
 * Блокирует гостевой комментарий, если внешний провайдер отклонил токен.
 *
 * @param array $comment_data Данные будущего комментария.
 *
 * @return array
 */
function ioblog_verify_comment_captcha( $comment_data ) {
	if ( is_user_logged_in() || in_array( $comment_data['comment_type'] ?? '', array( 'pingback', 'trackback' ), true ) ) {
		return $comment_data;
	}

	$result = ioblog_validate_captcha_response();
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ), esc_html__( 'CAPTCHA verification', 'ioblog-editorial' ), array( 'response' => 403, 'back_link' => true ) );
	}

	return $comment_data;
}
add_filter( 'preprocess_comment', 'ioblog_verify_comment_captcha', 4 );

/**
 * Добавляет ошибку CAPTCHA к стандартной проверке логина и пароля.
 *
 * @param null|WP_User|WP_Error $user     Текущий результат аутентификации.
 * @param string                $username Введённое имя пользователя.
 * @param string                $password Введённый пароль.
 *
 * @return null|WP_User|WP_Error
 */
function ioblog_verify_login_captcha( $user, $username, $password ) {
	if ( 'POST' !== strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['log'], $_POST['pwd'] ) ) {
		return $user;
	}

	$result = ioblog_validate_captcha_response();
	return is_wp_error( $result ) ? $result : $user;
}
add_filter( 'authenticate', 'ioblog_verify_login_captcha', 30, 3 );

/**
 * Добавляет результат CAPTCHA к штатным ошибкам регистрации.
 *
 * @param WP_Error $errors Ошибки регистрации.
 *
 * @return WP_Error
 */
function ioblog_verify_registration_captcha( $errors ) {
	$result = ioblog_validate_captcha_response();
	if ( is_wp_error( $result ) ) {
		$errors->add( $result->get_error_code(), $result->get_error_message() );
	}

	return $errors;
}
add_filter( 'registration_errors', 'ioblog_verify_registration_captcha', 10, 1 );

/**
 * Добавляет результат CAPTCHA к ошибкам запроса восстановления пароля.
 *
 * @param WP_Error $errors Ошибки формы восстановления.
 *
 * @return void
 */
function ioblog_verify_lostpassword_captcha( $errors ) {
	$result = ioblog_validate_captcha_response();
	if ( is_wp_error( $result ) ) {
		$errors->add( $result->get_error_code(), $result->get_error_message() );
	}
}
add_action( 'lostpassword_post', 'ioblog_verify_lostpassword_captcha', 10, 1 );
