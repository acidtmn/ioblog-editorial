<?php
/** Точки расширения бесплатной темы и сохранность содержимого при отключении Pro. */

/** Проверяет готовность расширения, а не наличие файлов или произвольного ключа в настройках. */
function ioblog_has_pro() {
	return (bool) apply_filters( 'ioblog_pro_active', false );
}

/** Сохраняет полезную ссылку старого Pro-блока даже при отключённом расширении. */
function ioblog_link_card_fallback( $content, $block ) {
	// Полный рендер Pro или сохранённая разметка уже содержат результат, повторно их не оборачиваем.
	if ( $content || ioblog_has_pro() ) {
		return $content;
	}
	$url = esc_url( $block['attrs']['url'] ?? '' );
	return $url ? '<p><a href="' . $url . '" rel="noopener">' . esc_html( wp_parse_url( $url, PHP_URL_HOST ) ?: $url ) . '</a></p>' : '';
}
add_filter( 'render_block_ioblog/link-card', 'ioblog_link_card_fallback', 10, 2 );
