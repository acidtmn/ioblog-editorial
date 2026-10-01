<?php
/**
 * Встроенная защита комментариев от прямых запросов и частых отправок.
 *
 * @package IoblogEditorial
 */

/**
 * Создаёт подписанные поля времени и ловушку, которых нет у прямых спам-запросов.
 *
 * @return string
 */
function ioblog_antispam_fields() {
	if ( is_user_logged_in() ) {
		return '';
	}

	$post_id   = get_the_ID();
	$started   = time();
	$signature = hash_hmac( 'sha256', $post_id . '|' . $started, wp_salt( 'nonce' ) );

	return '<div class="io-comment-trap" aria-hidden="true"><label>' . esc_html__( 'Leave this field empty', 'ioblog-editorial' ) . '<input type="text" name="ioblog_website_confirm" value="" tabindex="-1" autocomplete="off"></label></div>' .
		'<input type="hidden" name="ioblog_comment_started" value="' . esc_attr( $started ) . '">' .
		'<input type="hidden" name="ioblog_comment_signature" value="' . esc_attr( $signature ) . '">';
}

/**
 * Проверяет подпись формы, honeypot, длительность заполнения и лимит IP.
 *
 * @param array $comment_data Данные будущего комментария.
 *
 * @return array
 */
function ioblog_validate_comment_request( $comment_data ) {
	if ( is_user_logged_in() || in_array( $comment_data['comment_type'] ?? '', array( 'pingback', 'trackback' ), true ) ) {
		return $comment_data;
	}

	if ( ! empty( $_POST['ioblog_website_confirm'] ) ) {
		wp_die( esc_html__( 'The comment was rejected by the anti-spam system.', 'ioblog-editorial' ), esc_html__( 'Spam protection', 'ioblog-editorial' ), array( 'response' => 403 ) );
	}

	$post_id   = absint( $comment_data['comment_post_ID'] ?? 0 );
	$started   = absint( $_POST['ioblog_comment_started'] ?? 0 );
	$signature = sanitize_text_field( wp_unslash( $_POST['ioblog_comment_signature'] ?? '' ) );
	$expected  = hash_hmac( 'sha256', $post_id . '|' . $started, wp_salt( 'nonce' ) );
	$elapsed   = time() - $started;

	if ( ! $started || ! hash_equals( $expected, $signature ) || $elapsed < 1 || $elapsed > 2 * DAY_IN_SECONDS ) {
		wp_die( esc_html__( 'The form has expired. Refresh the page and submit your comment again.', 'ioblog-editorial' ), esc_html__( 'Spam protection', 'ioblog-editorial' ), array( 'response' => 403, 'back_link' => true ) );
	}

	$rate_key = 'ioblog_comment_rate_' . md5( ioblog_comment_remote_ip() );
	if ( (int) get_transient( $rate_key ) >= 5 ) {
		wp_die( esc_html__( 'Too many comments were submitted in a short time. Try again in 10 minutes.', 'ioblog-editorial' ), esc_html__( 'Spam protection', 'ioblog-editorial' ), array( 'response' => 429, 'back_link' => true ) );
	}

	return $comment_data;
}
add_filter( 'preprocess_comment', 'ioblog_validate_comment_request', 3 );

/**
 * Учитывает успешно созданный комментарий в ограничителе частоты.
 *
 * @return void
 */
function ioblog_increment_comment_rate() {
	if ( is_user_logged_in() ) {
		return;
	}

	$rate_key = 'ioblog_comment_rate_' . md5( ioblog_comment_remote_ip() );
	set_transient( $rate_key, (int) get_transient( $rate_key ) + 1, 10 * MINUTE_IN_SECONDS );
}
add_action( 'comment_post', 'ioblog_increment_comment_rate', 1 );

/**
 * Отправляет комментарии с большим числом ссылок на ручную модерацию.
 *
 * @param int|string $approved Текущее решение WordPress.
 * @param array      $comment_data Данные комментария.
 *
 * @return int|string
 */
function ioblog_moderate_link_heavy_comments( $approved, $comment_data ) {
	if ( substr_count( strtolower( $comment_data['comment_content'] ?? '' ), 'http' ) > 2 ) {
		return 0;
	}

	return $approved;
}
add_filter( 'pre_comment_approved', 'ioblog_moderate_link_heavy_comments', 10, 2 );
