<?php
/**
 * Нативная форма и лента комментариев самостоятельной темы.
 *
 * @package IoblogEditorial
 */

if ( post_password_required() ) {
	return;
}
?>
<section class="comments-area">
	<header class="io-comments__header"><h2><?php esc_html_e( 'Comments', 'ioblog-editorial' ); ?> <span><?php echo esc_html( get_comments_number() ); ?></span></h2></header>
	<?php if ( have_comments() ) { ?>
		<ol class="comment-list"><?php wp_list_comments( array( 'style' => 'ol', 'avatar_size' => 44, 'short_ping' => true, 'callback' => 'ioblog_comment_markup' ) ); ?></ol>
		<?php the_comments_pagination( array( 'prev_text' => '←', 'next_text' => '→' ) ); ?>
	<?php } ?>
	<?php
	comment_form(
		array(
			'title_reply'          => __( 'Join the discussion', 'ioblog-editorial' ),
			'label_submit'         => __( 'Submit', 'ioblog-editorial' ),
			'comment_field'        => ioblog_comment_field(),
			'comment_notes_before' => '<p class="io-comment-note">' . esc_html__( 'Keep it concise and relevant. Attach an image when it helps the discussion.', 'ioblog-editorial' ) . '</p>',
			'class_submit'         => 'io-comment-submit',
		)
	);
	?>
</section>
<div class="io-lightbox" hidden><button class="io-lightbox__backdrop" type="button" aria-label="<?php esc_attr_e( 'Close image', 'ioblog-editorial' ); ?>"></button><button class="io-lightbox__close" type="button" aria-label="<?php esc_attr_e( 'Close image', 'ioblog-editorial' ); ?>">×</button><img src="" alt="<?php esc_attr_e( 'Image attached to a comment', 'ioblog-editorial' ); ?>"></div>
