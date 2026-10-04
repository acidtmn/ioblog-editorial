<?php
/** Административный вход: только права/nonce и делегирование контракту настроек. */
add_action( 'admin_menu', static function () {
	add_submenu_page( 'ioblog-settings', __( 'Privacy and consent', 'ioblog-editorial' ), __( 'Privacy and consent', 'ioblog-editorial' ), 'manage_options', 'ioblog-privacy', static function () { require get_theme_file_path( 'template-parts/legal-admin.php' ); } );
} );
add_action( 'admin_post_io_legal_settings', static function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
	check_admin_referer( 'io_legal_settings' );
	try { Ioblog_Legal_Settings::save( wp_unslash( $_POST['legal'] ?? array() ) ); }
	catch ( Throwable $error ) { wp_die( esc_html__( 'Check the field values.', 'ioblog-editorial' ), '', array( 'response' => 400 ) ); }
	wp_safe_redirect( admin_url( 'admin.php?page=ioblog-privacy&saved=1' ) ); exit;
} );
add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	if ( str_ends_with( $hook, '_page_ioblog-privacy' ) ) { wp_enqueue_style( 'ioblog-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), ioblog_asset_version( 'assets/css/admin.css' ) ); }
} );
