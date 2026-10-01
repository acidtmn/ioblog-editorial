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
		<a class="io-card__arrow" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: post title. */ __( 'Read: %s', 'ioblog-editorial' ), get_the_title( $post_id ) ) ); ?>">→</a>
	</div>
</article>
