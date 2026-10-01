<?php
/** Настраиваемая типографика и макет статьи. */

/** @return string[] */
function ioblog_typography_presets() {
	return array( 'editorial', 'compact', 'comfortable' );
}

/** @return string[] */
function ioblog_article_layouts() {
	return array( 'sidebar', 'focused', 'wide' );
}

/** Добавляет закрытые модификаторы, не позволяя настройкам создавать произвольные CSS-классы. */
function ioblog_typography_body_classes( $classes ) {
	$preset = sanitize_key( (string) ioblog_get_setting( 'typography_preset' ) );
	$layout = sanitize_key( (string) ioblog_get_setting( 'article_layout' ) );
	$classes[] = 'io-type-' . ( in_array( $preset, ioblog_typography_presets(), true ) ? $preset : 'editorial' );
	$classes[] = 'io-layout-' . ( in_array( $layout, ioblog_article_layouts(), true ) ? $layout : 'sidebar' );
	return $classes;
}
add_filter( 'body_class', 'ioblog_typography_body_classes' );

/** Выводит только числовые CSS-переменные после ограничения диапазона в sanitizer. */
function ioblog_enqueue_typography_variables() {
	// Inline CSS идёт после базовых переменных, иначе base.css возвращал ширину к 790 px.
	wp_add_inline_style( 'ioblog-reading', sprintf(
		':root{--io-reading-size:%dpx;--io-reading-leading:%.2f;--io-content:%dpx}',
		min( 22, max( 16, absint( ioblog_get_setting( 'reading_font_size' ) ) ) ),
		min( 2, max( 1.45, (float) ioblog_get_setting( 'reading_line_height' ) ) ),
		min( 920, max( 680, absint( ioblog_get_setting( 'reading_width' ) ) ) )
	) );
}
add_action( 'wp_enqueue_scripts', 'ioblog_enqueue_typography_variables', 20 );
