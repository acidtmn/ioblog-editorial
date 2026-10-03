<?php
/** Проверенный локальный WOFF2: не подключает Google Fonts и не расширяет загрузки обычным пользователям. */
final class Ioblog_Design_Fonts {
	public static function upload( $file ) {
		if ( ! is_array( $file ) || ! empty( $file['error'] ) || ! is_uploaded_file( $file['tmp_name'] ?? '' ) || ( $file['size'] ?? 0 ) > 2 * MB_IN_BYTES || 'woff2' !== strtolower( pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) ) ) { throw new InvalidArgumentException( 'font' ); }
		$handle = fopen( $file['tmp_name'], 'rb' );
		$magic = $handle ? fread( $handle, 4 ) : ''; if ( $handle ) { fclose( $handle ); }
		if ( 'wOF2' !== $magic ) { throw new InvalidArgumentException( 'font' ); }
		require_once ABSPATH . 'wp-admin/includes/file.php';
		// WordPress сверяет MIME ещё и с общим списком. Разрешение живёт только
		// внутри этой проверенной административной операции, а не для всех загрузок сайта.
		$font_mime = static function ( $mimes ) { $mimes['woff2'] = 'font/woff2'; return $mimes; };
		add_filter( 'upload_mimes', $font_mime );
		try { $upload = wp_handle_upload( $file, array( 'test_form' => false, 'mimes' => array( 'woff2' => 'font/woff2' ) ) ); }
		finally { remove_filter( 'upload_mimes', $font_mime ); }
		if ( isset( $upload['error'] ) ) { throw new RuntimeException( 'upload' ); }
		$id = wp_insert_attachment( array( 'post_title' => sanitize_text_field( pathinfo( $file['name'], PATHINFO_FILENAME ) ), 'post_mime_type' => 'font/woff2', 'post_status' => 'inherit' ), $upload['file'] );
		if ( is_wp_error( $id ) || ! $id ) { wp_delete_file( $upload['file'] ); throw new RuntimeException( 'attachment' ); }
		return $id;
	}
}
add_action( 'admin_post_io_design_font', static function () {
	if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
	check_admin_referer( 'io_design_font' );
	try { Ioblog_Design_Fonts::upload( $_FILES['font'] ?? array() ); }
	catch ( Throwable $error ) { wp_die( esc_html__( 'Upload a valid WOFF2 font no larger than 2 MB.', 'ioblog-editorial' ), '', array( 'response' => 400 ) ); }
	wp_safe_redirect( admin_url( 'admin.php?page=ioblog-design' ) ); exit;
} );
