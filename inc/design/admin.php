<?php
/** Административный транспорт оформления: права, nonce, делегирование сервису. */
add_action( 'admin_menu', static function () {
	add_submenu_page( 'ioblog-settings', __( 'Design studio', 'ioblog-editorial' ), __( 'Design studio', 'ioblog-editorial' ), 'manage_options', 'ioblog-design', static function () { require get_theme_file_path( 'template-parts/admin/design.php' ); } );
}, 20 );
add_action( 'admin_enqueue_scripts', static function ( $hook ) {
	if ( 'io-blog_page_ioblog-design' !== $hook && ! str_ends_with( $hook, '_page_ioblog-design' ) ) { return; }
	wp_enqueue_style( 'ioblog-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), ioblog_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_style( 'ioblog-design-admin', get_theme_file_uri( 'assets/css/design-admin.css' ), array(), ioblog_asset_version( 'assets/css/design-admin.css' ) );
	wp_enqueue_script( 'ioblog-design-admin', get_theme_file_uri( 'assets/js/design-admin.js' ), array(), ioblog_asset_version( 'assets/js/design-admin.js' ), true );
	wp_localize_script( 'ioblog-design-admin', 'IOBlogDesign', array( 'endpoint' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'io_design' ), 'error' => __( 'Unable to save. Please retry.', 'ioblog-editorial' ), 'saved' => __( 'Settings saved.', 'ioblog-editorial' ) ) );
	wp_localize_script( 'ioblog-design-admin', 'IOBlogDesignPalettes', array(
		'colors' => Ioblog_Design_Presets::colors(),
		'labels' => Ioblog_Design_Presets::labels(),
		'custom' => __( 'Custom palette', 'ioblog-editorial' ),
		'contrast' => array(
			'light' => __( 'Light palette', 'ioblog-editorial' ), 'dark' => __( 'Dark palette', 'ioblog-editorial' ),
			'text' => __( 'Main text', 'ioblog-editorial' ), 'muted' => __( 'Secondary text', 'ioblog-editorial' ),
			'button' => __( 'Button text', 'ioblog-editorial' ), 'focus' => __( 'Keyboard focus', 'ioblog-editorial' ),
			'pass' => __( 'Meets AA', 'ioblog-editorial' ), 'fail' => __( 'Below AA', 'ioblog-editorial' ),
		),
	) );
} );
add_action( 'wp_ajax_io_design', static function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( array( 'message' => 'Forbidden' ), 403 ); }
	check_ajax_referer( 'io_design', 'nonce' );
	try {
		$config = Ioblog_Design_Schema::validate( wp_unslash( $_POST['config'] ?? array() ) );
		if ( 'publish' === ( $_POST['mode'] ?? '' ) ) { Ioblog_Design_Service::save( $config ); wp_send_json_success(); }
		$token = bin2hex( random_bytes( 20 ) );
		set_transient( 'io_design_' . get_current_user_id(), array( 'token' => $token, 'config' => $config ), 10 * MINUTE_IN_SECONDS );
		wp_send_json_success( array( 'url' => add_query_arg( 'io_design_preview', $token, home_url( '/' ) ) ) );
	} catch ( Throwable $error ) { wp_send_json_error( array( 'message' => __( 'Check the field values.', 'ioblog-editorial' ) ), 400 ); }
} );
