<?php
/**
 * Подготовка данных для повторно используемых карточек публикаций.
 *
 * @package IoblogEditorial
 */

/**
 * Возвращает первую назначенную рубрику записи.
 *
 * @param int $post_id Идентификатор записи.
 *
 * @return WP_Term|null
 */
function ioblog_primary_category( $post_id ) {
	$categories = get_the_category( $post_id );
	return $categories ? $categories[0] : null;
}

/**
 * Рассчитывает ориентировочное время чтения без отдельного плагина.
 *
 * @param int $post_id Идентификатор записи.
 *
 * @return int
 */
function ioblog_reading_minutes( $post_id ) {
	$content = wp_strip_all_tags( strip_shortcodes( get_post_field( 'post_content', $post_id ) ) );
	$words   = preg_split( '/\s+/u', trim( $content ), -1, PREG_SPLIT_NO_EMPTY );
	return max( 1, (int) ceil( count( $words ) / 180 ) );
}

/**
 * Выводит краткие метаданные публикации.
 *
 * @param int  $post_id       Идентификатор записи.
 * @param bool $with_comments Нужно ли показать комментарии.
 *
 * @return void
 */
function ioblog_post_meta( $post_id, $with_comments = false ) {
	?>
	<div class="io-post-meta">
		<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_id ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y', $post_id ) ); ?></time>
		<span class="io-post-meta__dot" aria-hidden="true"></span>
		<span><?php echo esc_html( sprintf( /* translators: %d: estimated reading time in minutes. */ _n( '%d min read', '%d min read', ioblog_reading_minutes( $post_id ), 'ioblog-editorial' ), ioblog_reading_minutes( $post_id ) ) ); ?></span>
		<?php if ( $with_comments ) { ?>
			<span class="io-post-meta__dot" aria-hidden="true"></span>
			<span><?php echo esc_html( sprintf( /* translators: %d: number of comments. */ _n( '%d comment', '%d comments', get_comments_number( $post_id ), 'ioblog-editorial' ), get_comments_number( $post_id ) ) ); ?></span>
		<?php } ?>
	</div>
	<?php
}

/**
 * Выводит компактную рубричную метку.
 *
 * @param int $post_id Идентификатор записи.
 *
 * @return void
 */
function ioblog_category_badge( $post_id ) {
	$category = ioblog_primary_category( $post_id );
	if ( ! $category ) {
		return;
	}
	?>
	<a class="io-category-badge" href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
	<?php
}

/**
 * Выводит реальную миниатюру, CSS-обложку или нейтральную подложку.
 *
 * @param int    $post_id Идентификатор записи.
 * @param string $size    Размер WordPress.
 * @param bool   $eager   Нужно ли приоритетно загрузить изображение первого экрана.
 *
 * @return void
 */
function ioblog_post_thumbnail( $post_id, $size = 'ioblog-card', $eager = false ) {
	if ( has_post_thumbnail( $post_id ) ) {
		$attributes = array(
			'class'    => 'io-card__image',
			'loading'  => $eager ? 'eager' : 'lazy',
			'decoding' => 'async',
		);
		if ( $eager ) {
			$attributes['fetchpriority'] = 'high';
		}
		echo get_the_post_thumbnail( $post_id, $size, $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		return;
	}

	?>
	<div class="io-card__placeholder" aria-hidden="true"><span>IO</span><strong><?php echo esc_html( wp_trim_words( get_the_title( $post_id ), 6 ) ); ?></strong></div>
	<?php
}

/**
 * Делегирует карточку одному шаблону с предсказуемым вариантом компоновки.
 *
 * @param int    $post_id Идентификатор записи.
 * @param string $variant Вариант карточки.
 *
 * @return void
 */
function ioblog_render_card( $post_id, $variant = 'standard' ) {
	get_template_part( 'template-parts/card', null, array( 'post_id' => $post_id, 'variant' => $variant ) );
}

/**
 * Возвращает свежие реальные публикации, исключая текущую.
 *
 * @param int $exclude_id Исключаемая запись.
 * @param int $count      Количество записей.
 *
 * @return WP_Query
 */
function ioblog_recent_posts_query( $exclude_id = 0, $count = 5 ) {
	return new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $count,
			'post__not_in'        => array_filter( array( $exclude_id ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
}
