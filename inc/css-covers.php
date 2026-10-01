<?php
/** CSS-обложки в штатных местах миниатюр WordPress, без фиктивных вложений. */

/**
 * Возвращает закрытый список визуальных вариантов, доступных редакции.
 *
 * @return string[]
 */
function ioblog_css_cover_variants() {
	return Ioblog_Css_Cover_Generator::variants();
}

/**
 * Проверяет сохранённый вариант до его использования в HTML-классе.
 *
 * @param int $post_id Идентификатор записи.
 *
 * @return string
 */
function ioblog_css_cover_variant( $post_id ) {
	return Ioblog_Css_Cover_Generator::resolve_variant( $post_id );
}

/**
 * Собирает короткие редакционные подписи для универсальной CSS-обложки.
 *
 * @param int    $post_id Идентификатор записи.
 * @param string $variant Проверенный визуальный вариант.
 *
 * @return array<string, string>
 */
function ioblog_css_cover_data( $post_id, $variant ) {
	return Ioblog_Css_Cover_Generator::build_data( $post_id, $variant );
}

/**
 * Рендерит одну обложку и для карточки, и для шапки статьи.
 *
 * @param int $post_id Идентификатор записи.
 *
 * @return string
 */
function ioblog_css_cover_html( $post_id ) {
	$variant = ioblog_css_cover_variant( $post_id );
	if ( ! $variant ) {
		return '';
	}

	// Историческая обложка Win+V остаётся неизменной, остальные используют общий шаблон.
	ob_start();
	if ( 'mint-keycaps' === $variant ) {
		get_template_part( 'template-parts/covers/mint-keycaps' );
	} else {
		get_template_part( 'template-parts/covers/editorial', null, ioblog_css_cover_data( $post_id, $variant ) );
	}
	return ob_get_clean();
}

function ioblog_css_cover_has_thumbnail( $has_thumbnail, $post ) {
	// В редакторе оставляем штатное состояние пустой миниатюры, чтобы пользователь мог загрузить настоящее изображение.
	if ( $has_thumbnail || is_admin() ) {
		return $has_thumbnail;
	}

	$post = get_post( $post );
	return $post instanceof WP_Post && '' !== ioblog_css_cover_variant( $post->ID );
}
add_filter( 'has_post_thumbnail', 'ioblog_css_cover_has_thumbnail', 10, 2 );

function ioblog_css_cover_thumbnail( $html, $post_id ) {
	// Для выбранных редакцией записей CSS заменяет тяжёлую картинку только на фронтенде.
	$cover = ioblog_css_cover_html( $post_id );
	return $cover ? $cover : $html;
}
add_filter( 'post_thumbnail_html', 'ioblog_css_cover_thumbnail', 10, 2 );

function ioblog_css_cover_post_class( $classes, $class, $post_id ) {
	$post_id = $post_id instanceof WP_Post ? $post_id->ID : $post_id;
	if ( ioblog_css_cover_variant( $post_id ) ) {
		$classes[] = 'ioblog-has-css-cover';
	}
	return $classes;
}
add_filter( 'post_class', 'ioblog_css_cover_post_class', 10, 3 );
