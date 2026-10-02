<?php
/** Публичная конфигурация библиотеки; личные закладки никогда не попадают в HTML-кеш. */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', static function () {
	if ( ! ioblog_get_setting( 'library_enabled' ) ) { return; }
	wp_enqueue_style( 'ioblog-library', get_theme_file_uri( 'assets/css/library.css' ), array( 'ioblog-responsive' ), ioblog_asset_version( 'assets/css/library.css' ) );
	wp_enqueue_script( 'ioblog-library-store', get_theme_file_uri( 'assets/js/library-store.js' ), array(), ioblog_asset_version( 'assets/js/library-store.js' ), true );
	wp_enqueue_script( 'ioblog-library', get_theme_file_uri( 'assets/js/library.js' ), array( 'ioblog-library-store' ), ioblog_asset_version( 'assets/js/library.js' ), true );
	wp_enqueue_script( 'ioblog-library-backup', get_theme_file_uri( 'assets/js/library-backup.js' ), array( 'ioblog-library-store' ), ioblog_asset_version( 'assets/js/library-backup.js' ), true );
	wp_enqueue_script( 'ioblog-library-transfer', get_theme_file_uri( 'assets/js/library-transfer.js' ), array( 'ioblog-library', 'ioblog-library-backup' ), ioblog_asset_version( 'assets/js/library-transfer.js' ), true );
	$post = get_queried_object();
	$current = is_singular( 'post' ) && $post instanceof WP_Post && 'publish' === $post->post_status && ! $post->post_password
		? array( 'id' => $post->ID, 'title' => wp_strip_all_tags( get_the_title( $post ) ), 'url' => get_permalink( $post ) ) : null;
	wp_localize_script( 'ioblog-library', 'ioblogLibrary', array( 'current' => $current, 'labels' => array(
		'save' => __( 'Save for later', 'ioblog-editorial' ), 'saved' => __( 'Saved for later', 'ioblog-editorial' ),
		'remove' => __( 'Remove from library', 'ioblog-editorial' ), 'error' => __( 'Browser storage is unavailable. Allow site storage to use your library.', 'ioblog-editorial' ),
		'limit' => __( 'Your library holds up to 100 articles. Remove an article before adding another.', 'ioblog-editorial' ),
		// translators: %d is the integer reading percentage, replaced by the browser script.
		'progress' => __( 'Read: %d%', 'ioblog-editorial' ), 'resume' => __( 'Continue reading', 'ioblog-editorial' ),
		'empty' => __( 'Save an article to find it here. Your library stays in this browser.', 'ioblog-editorial' ),
		'emptyHistory' => __( 'Start reading an article to see it here.', 'ioblog-editorial' ),
		'noMatches' => __( 'No matching articles. Try another title or reading status.', 'ioblog-editorial' ),
		// translators: %d is the number of matching articles, replaced by the browser script.
		'results' => __( 'Articles found: %d', 'ioblog-editorial' ),
		'invalidBackup' => __( 'Invalid backup. Choose an IO Blog library JSON file no larger than 256 KB.', 'ioblog-editorial' ),
		'backupSite' => __( 'This backup belongs to a different site address. Import it on the original site.', 'ioblog-editorial' ),
		'imported' => __( 'Library restored. Existing bookmarks have been kept.', 'ioblog-editorial' ),
		'exported' => __( 'Library file prepared. Keep it safe: it contains your reading history.', 'ioblog-editorial' ),
	) ) );
	if ( $current ) { wp_enqueue_script( 'ioblog-reading-progress', get_theme_file_uri( 'assets/js/reading-progress.js' ), array( 'ioblog-library' ), ioblog_asset_version( 'assets/js/reading-progress.js' ), true ); }
} );

add_action( 'wp_footer', static function () {
	if ( ioblog_get_setting( 'library_enabled' ) ) { get_template_part( 'template-parts/library' ); }
} );
