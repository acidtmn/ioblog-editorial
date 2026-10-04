<?php
/**
 * Универсальная карточка реальной WordPress-записи.
 *
 * @package IoblogEditorial
 */

$post_id = isset( $args['post_id'] ) ? (int) $args['post_id'] : get_the_ID();
$variant = isset( $args['variant'] ) ? sanitize_html_class( $args['variant'] ) : 'standard';
$excerpt = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_strip_all_tags( get_post_field( 'post_content', $post_id ) );
?>
<article <?php post_class( 'io-card io-card--' . $variant, $post_id ); ?>>
	<a class="io-card__media" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" tabindex="-1" aria-hidden="true">
		<?php ioblog_post_thumbnail( $post_id, 'ioblog-card', 'featured' === $variant ); ?>
		<?php ioblog_category_badge( $post_id ); ?>
	</a>
	<div class="io-card__body">
		<?php ioblog_post_meta( $post_id ); ?>
		<h3 class="io-card__title"><a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></a></h3>
		<?php if ( ! in_array( $variant, array( 'compact', 'sidebar' ), true ) ) { ?>
			<p class="io-card__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 'featured' === $variant ? 24 : 18, '…' ) ); ?></p>
		<?php } ?>
		<div class="io-card__footer">
		<?php if ( ioblog_get_setting( 'claps_enabled' ) && 'publish' === get_post_status( $post_id ) && ! get_post_field( 'post_password', $post_id ) ) { ?>
			<a class="io-card__claps" href="<?php echo esc_url( get_permalink( $post_id ) . '#io-claps' ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: total claps. */ __( 'Claps: %d', 'ioblog-editorial' ), \IOBlog\Claps\Repository::total( $post_id ) ) ); ?>"><svg viewBox="0 0 48 48" aria-hidden="true"><path d="m24 31-9-12a2 2 0 0 1 3-2l6 7-7-13a2 2 0 0 1 4-2l7 13-4-15a2 2 0 0 1 4-1l4 15 1-12a2 2 0 0 1 4 0l-1 17-2 8M13 39l-7-13a2 2 0 0 1 3-2l5 7-2-18a2 2 0 0 1 4 0l2 13 1-17a2 2 0 0 1 4 0l-1 17 4-14a2 2 0 0 1 4 1l-3 16 5-6a3 3 0 0 1 4 4l-8 13a9 9 0 0 1-15-1z"/></svg><?php echo esc_html( number_format_i18n( \IOBlog\Claps\Repository::total( $post_id ) ) ); ?></a>
		<?php } ?>
		<a class="io-card__arrow" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: post title. */ __( 'Read: %s', 'ioblog-editorial' ), get_the_title( $post_id ) ) ); ?>">→</a>
		</div>
	</div>
</article>
