<?php
/** Страница переноса и её ресурсы, не меняющие поведение остальных экранов WordPress. */
function ioblog_register_transfer_page() {
	add_submenu_page( 'ioblog-settings', __( 'Settings transfer', 'ioblog-editorial' ), __( 'Export and import', 'ioblog-editorial' ), 'manage_options', 'ioblog-settings-transfer', 'ioblog_render_transfer_page' );
}

function ioblog_render_transfer_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	require get_theme_file_path( 'template-parts/admin/settings-transfer.php' );
}

function ioblog_enqueue_transfer_assets( $hook ) {
	if ( ! str_ends_with( $hook, '_page_ioblog-settings-transfer' ) ) { return; }
	wp_enqueue_style( 'ioblog-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), ioblog_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_style( 'ioblog-settings-transfer', get_theme_file_uri( 'assets/css/settings-transfer.css' ), array( 'ioblog-admin' ), ioblog_asset_version( 'assets/css/settings-transfer.css' ) );
	wp_enqueue_script( 'ioblog-settings-transfer', get_theme_file_uri( 'assets/js/settings-transfer.js' ), array(), ioblog_asset_version( 'assets/js/settings-transfer.js' ), true );
	wp_localize_script( 'ioblog-settings-transfer', 'ioblogSettingsTransfer', array(
		'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'ioblog_transfer' ),
		'groups' => Ioblog_Settings_Transfer_Schema::groups(),
		'loading' => __( 'Checking backup...', 'ioblog-editorial' ),
		'network' => __( 'The request failed. Check your connection and try again.', 'ioblog-editorial' ),
		'invalid' => __( 'Choose a JSON file up to 1 MB.', 'ioblog-editorial' ),
		/* translators: %d: number of changed settings. */
		'preview' => __( 'Changes found: %d. Choose sections and confirm within ten minutes.', 'ioblog-editorial' ),
		'skipped' => __( 'Objects not found locally; their current settings will be preserved:', 'ioblog-editorial' ),
		'none' => __( 'No changes in the selected sections.', 'ioblog-editorial' ),
		/* translators: %d: number of applied changes. */
		'success' => __( 'Settings restored. Changes applied: %d.', 'ioblog-editorial' ),
		'source' => __( 'Backup source:', 'ioblog-editorial' ),
		'pro' => __( 'Pro settings are preserved even without an active extension. A license is still required to enable Pro features.', 'ioblog-editorial' ),
	) );
}
