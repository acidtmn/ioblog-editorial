<?php
/** HTTP-входы переноса: права, CSRF, делегирование сервису и ответ. */
final class Ioblog_Settings_Transfer_Controller {
	public static function export() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'You are not allowed to manage these settings.', 'ioblog-editorial' ), '', array( 'response' => 403 ) ); }
		check_admin_referer( 'ioblog_transfer_export' );
		try { $json = Ioblog_Settings_Transfer_Service::export(); }
		catch ( Throwable $error ) { wp_die( esc_html( Ioblog_Settings_Transfer_Service::error_message( $error ) ) ); }
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'X-Content-Type-Options: nosniff' );
		header( 'Content-Disposition: attachment; filename="io-blog-settings-' . gmdate( 'Y-m-d-His' ) . '.json"' );
		// Здесь JSON намеренно содержит исходный HTML рекламных блоков, а не HTML страницы админки.
		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	public static function preview() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => __( 'You are not allowed to manage these settings.', 'ioblog-editorial' ) ), 403 ); }
		check_ajax_referer( 'ioblog_transfer', 'nonce' );
		try { $result = Ioblog_Settings_Transfer_Service::preview( Ioblog_Settings_Transfer_Upload::read( $_FILES['backup'] ?? null ) ); }
		catch ( Throwable $error ) { wp_send_json_error( array( 'message' => Ioblog_Settings_Transfer_Service::error_message( $error ) ), 400 ); }
		wp_send_json_success( $result );
	}

	public static function confirm() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => __( 'You are not allowed to manage these settings.', 'ioblog-editorial' ) ), 403 ); }
		check_ajax_referer( 'ioblog_transfer', 'nonce' );
		try { $result = Ioblog_Settings_Transfer_Service::confirm( wp_unslash( $_POST['token'] ?? '' ), wp_unslash( $_POST['groups'] ?? array() ) ); }
		catch ( Throwable $error ) { wp_send_json_error( array( 'message' => Ioblog_Settings_Transfer_Service::error_message( $error ) ), 400 ); }
		wp_send_json_success( $result );
	}
}
