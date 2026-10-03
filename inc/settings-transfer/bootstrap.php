<?php
/** Сборка модулей переноса. На публичных страницах обработчики и ресурсы не подключаются. */
foreach ( array( 'schema', 'references', 'repository', 'presentation', 'service', 'upload', 'controller', 'admin' ) as $module ) {
	require_once __DIR__ . '/' . $module . '.php';
}
add_action( 'admin_menu', 'ioblog_register_transfer_page', 20 );
add_action( 'admin_enqueue_scripts', 'ioblog_enqueue_transfer_assets' );
add_action( 'admin_post_ioblog_transfer_export', array( 'Ioblog_Settings_Transfer_Controller', 'export' ) );
add_action( 'wp_ajax_ioblog_transfer_preview', array( 'Ioblog_Settings_Transfer_Controller', 'preview' ) );
add_action( 'wp_ajax_ioblog_transfer_confirm', array( 'Ioblog_Settings_Transfer_Controller', 'confirm' ) );
