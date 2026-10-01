<?php
/**
 * Единый компонент внешних социальных ссылок IO Blog.
 *
 * @package IoblogEditorial
 */

/**
 * Возвращает заполненные социальные ссылки в стабильном порядке.
 *
 * @return array<string, array{label: string, url: string}>
 */
function ioblog_get_social_links() {
	$networks = array(
		'telegram' => array( 'label' => 'Telegram', 'setting' => 'social_telegram_url' ),
		'max'      => array( 'label' => 'MAX', 'setting' => 'social_max_url' ),
		'vk'       => array( 'label' => __( 'VK', 'ioblog-editorial' ), 'setting' => 'social_vk_url' ),
		'ok'       => array( 'label' => __( 'Odnoklassniki', 'ioblog-editorial' ), 'setting' => 'social_ok_url' ),
	);
	$links = array();

	foreach ( $networks as $network => $data ) {
		$url = esc_url( (string) ioblog_get_setting( $data['setting'] ) );
		if ( $url ) {
			$links[ $network ] = array( 'label' => $data['label'], 'url' => $url );
		}
	}

	return $links;
}

/**
 * Возвращает компактную монохромную SVG-иконку выбранной сети.
 *
 * @param string $network Системное имя социальной сети.
 *
 * @return string
 */
function ioblog_get_social_icon( $network ) {
	$icons = array(
		'telegram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.6 3.2 18.4 19c-.2 1.1-.9 1.4-1.8.9l-4.9-3.6-2.4 2.3c-.3.3-.5.5-1 .5l.4-5 9.1-8.2c.4-.4-.1-.6-.6-.2L6 12.8l-4.8-1.5c-1-.3-1-1 .2-1.5L20 2.6c.9-.3 1.8.2 1.6.6Z"/></svg>',
		'max'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6.2h4.2l4.8 6.1 4.8-6.1H21v11.6h-4v-5.6l-5 6.1-5-6.1v5.6H3V6.2Z"/></svg>',
		'vk'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.4 6.4h3.4c.3 0 .5.2.6.5.7 2.2 1.8 4.1 3.3 5.7.3.3.5.4.7.3.2-.1.3-.4.3-.8V7.5c0-.7-.2-1-.8-1.2h5.2c.7 0 .9.4.9 1.1v3.4c0 .4.2.6.4.7.3.1.5-.1.8-.4 1.2-1.4 2-2.8 2.6-4.4.1-.2.3-.4.6-.4h3.2c.5 0 .7.3.5.8-.7 1.8-1.9 3.5-3.4 5.1-.4.4-.4.7 0 1.1 1.5 1.4 2.8 2.9 3.7 4.6.3.5.1.8-.4.8h-3.6c-.4 0-.7-.2-.9-.5-.8-1.2-1.8-2.2-2.9-3.1-.3-.2-.5-.2-.6-.1-.2.1-.2.4-.2.7v2.2c0 .5-.3.8-.8.8h-1.6c-2.8 0-5.3-1.3-7.4-3.7-1.9-2.2-3.3-4.7-4-7.8-.1-.5.1-.8.4-.8Z" transform="translate(-2) scale(.93)"/></svg>',
		'ok'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.2a4.2 4.2 0 1 1 0 8.4 4.2 4.2 0 0 1 0-8.4Zm0 2.5a1.7 1.7 0 1 0 0 3.4 1.7 1.7 0 0 0 0-3.4Zm5.4 7.2c.7.8.6 1.7-.3 2.2-.9.6-2 .9-3 1.1l2.9 2.9c.6.6.6 1.5 0 2.1-.6.6-1.5.6-2.1 0L12 17.3l-2.9 2.9c-.6.6-1.5.6-2.1 0-.6-.6-.6-1.5 0-2.1l2.9-2.9c-1.1-.2-2.1-.5-3-1.1-.9-.5-1-1.4-.3-2.2.5-.6 1.2-.6 2-.2 2.2 1.2 4.6 1.2 6.8 0 .8-.4 1.5-.4 2 .2Z"/></svg>',
	);

	return $icons[ $network ] ?? '';
}

/**
 * Выводит доступный список социальных сетей для подвала или карточек сайта.
 *
 * @return void
 */
function ioblog_render_social_links() {
	$links = ioblog_get_social_links();
	if ( empty( $links ) ) {
		return;
	}

	foreach ( $links as $network => $link ) {
		$accessible_label = sprintf( /* translators: %s: social network name. */ __( 'IO Blog on %s', 'ioblog-editorial' ), $link['label'] );
		printf(
			'<a class="io-social-link io-social-link--%1$s" href="%2$s" rel="noopener noreferrer" aria-label="%3$s" title="%4$s">%5$s<span class="screen-reader-text">%3$s</span></a>',
			esc_attr( $network ),
			esc_url( $link['url'] ),
			esc_attr( $accessible_label ),
			esc_attr( $link['label'] ),
			ioblog_get_social_icon( $network ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG хранится в коде темы без пользовательского ввода.
		);
	}
}
