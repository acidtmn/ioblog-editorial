<?php
/**
 * Формирование содержания по H2/H3 внутри Gutenberg-контента.
 *
 * @package IoblogEditorial
 */

/**
 * Добавляет стабильные id к заголовкам и возвращает содержание вместе с HTML.
 *
 * @param string $content Уже обработанный WordPress-контент.
 *
 * @return array{content:string,items:array<int,array{level:int,id:string,title:string}>}
 */
function ioblog_prepare_table_of_contents( $content ) {
	$items           = array();
	$used            = array();
	$excluded_blocks = array();

	// Рекламная разметка может содержать визуальные заголовки, но не является частью структуры статьи.
	$content_without_ads = preg_replace_callback(
		'/<aside\b(?=[^>]*\bclass=["\'][^"\']*\bio-ad-slot\b[^"\']*["\'])[^>]*>.*?<\/aside>/isu',
		function ( $matches ) use ( &$excluded_blocks ) {
			$placeholder                       = '<!--ioblog-toc-excluded-' . count( $excluded_blocks ) . '-->';
			$excluded_blocks[ $placeholder ] = $matches[0];
			return $placeholder;
		},
		$content
	);
	if ( null !== $content_without_ads ) {
		$content = $content_without_ads;
	}

	$content = preg_replace_callback(
		'/<(h[23])([^>]*)>(.*?)<\/\1>/isu',
		function ( $matches ) use ( &$items, &$used ) {
			$title = trim( wp_strip_all_tags( $matches[3] ) );
			if ( '' === $title ) {
				return $matches[0];
			}

			$id = '';
			if ( preg_match( '/\sid=["\']([^"\']+)["\']/iu', $matches[2], $id_match ) ) {
				$id = sanitize_title( $id_match[1] );
			}

			if ( '' === $id ) {
				$id = sanitize_title( $title );
			}
			if ( '' === $id ) {
				$id = 'section-' . ( count( $items ) + 1 );
			}

			$base    = $id;
			$suffix  = 2;
			while ( isset( $used[ $id ] ) ) {
				$id = $base . '-' . $suffix;
				++$suffix;
			}
			$used[ $id ] = true;

			$attributes = preg_replace( '/\s+id=["\'][^"\']+["\']/iu', '', $matches[2] );
			$items[]    = array( 'level' => (int) substr( strtolower( $matches[1] ), 1 ), 'id' => $id, 'title' => $title );

			return sprintf( '<%1$s%2$s id="%3$s">%4$s</%1$s>', strtolower( $matches[1] ), $attributes, esc_attr( $id ), $matches[3] );
		},
		$content
	);

	if ( $excluded_blocks ) {
		$content = strtr( $content, $excluded_blocks );
	}

	return array( 'content' => $content, 'items' => $items );
}

/**
 * Выводит единый список содержания в desktop- или mobile-контейнере.
 *
 * @param array  $items Список заголовков.
 * @param string $class Дополнительный класс.
 *
 * @return void
 */
function ioblog_render_toc( $items, $class = '' ) {
	if ( empty( $items ) ) {
		return;
	}
	?>
	<nav class="io-toc <?php echo esc_attr( $class ); ?>" aria-label="<?php esc_attr_e( 'Table of contents', 'ioblog-editorial' ); ?>">
		<h2 class="io-toc__title"><?php esc_html_e( 'In this article', 'ioblog-editorial' ); ?></h2>
		<ol class="io-toc__list">
			<?php foreach ( $items as $item ) { ?>
				<li class="io-toc__item io-toc__item--h<?php echo esc_attr( $item['level'] ); ?>"><a href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></li>
			<?php } ?>
		</ol>
	</nav>
	<?php
}
