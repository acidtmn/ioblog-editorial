<?php
/**
 * Базовые возможности WordPress и области темы.
 *
 * @package IoblogEditorial
 */

/**
 * Регистрирует только стандартные возможности WordPress, необходимые шаблонам.
 *
 * @return void
 */
function ioblog_setup_theme() {
	load_theme_textdomain( 'ioblog-editorial', get_theme_file_path( 'languages' ) );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 56,
			'width'       => 220,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'ioblog-editorial' ),
			'mobile'  => __( 'Mobile menu', 'ioblog-editorial' ),
			'footer'  => __( 'Footer menu', 'ioblog-editorial' ),
		)
	);

	add_image_size( 'ioblog-card', 760, 430, true );
	add_image_size( 'ioblog-card-small', 480, 270, true );
	// Социальные сети ожидают широкую обложку; отдельный размер не меняет миниатюру самой статьи.
	add_image_size( 'ioblog-social', 1200, 630, true );
	add_editor_style( array( 'assets/css/base.css', 'assets/css/content.css', 'assets/css/reading.css' ) );
}
add_action( 'after_setup_theme', 'ioblog_setup_theme' );

/**
 * Регистрирует боковую область для штатных виджетов и рекламных блоков.
 *
 * @return void
 */
function ioblog_register_sidebars() {
	register_sidebar(
		array(
			'name'          => __( 'Article sidebar', 'ioblog-editorial' ),
			'id'            => 'article-sidebar',
			'description'   => __( 'Widgets displayed in the sticky sidebar of a single article.', 'ioblog-editorial' ),
			'before_widget' => '<section id="%1$s" class="io-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="io-widget__title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'ioblog_register_sidebars' );

/**
 * Сохраняет редакционную сетку главной предсказуемой на каждой странице пагинации.
 *
 * @param WP_Query $query Основной запрос WordPress.
 *
 * @return void
 */
function ioblog_configure_main_queries( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_home() ) {
		$query->set( 'posts_per_page', (int) ioblog_get_setting( 'home_posts_per_page' ) );
	}
}
add_action( 'pre_get_posts', 'ioblog_configure_main_queries' );
