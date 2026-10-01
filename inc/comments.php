<?php
/**
 * Нативные комментарии IO Blog с безопасным изображением и emoji-панелью.
 *
 * @package IoblogEditorial
 */

/**
 * Проверяет изображение до сохранения комментария, чтобы не создавать запись с опасным файлом.
 *
 * @param array $comment_data Данные будущего комментария.
 *
 * @return array
 */
function ioblog_validate_comment_image( $comment_data ) {
	if ( empty( $_FILES['ioblog_comment_image']['name'] ) ) {
		return $comment_data;
	}
	if ( ! ioblog_get_setting( 'comments_images' ) ) {
		wp_die( esc_html__( 'Image attachments are disabled in the site settings.', 'ioblog-editorial' ), esc_html__( 'Upload disabled', 'ioblog-editorial' ), array( 'response' => 400, 'back_link' => true ) );
	}

	if ( empty( $_POST['ioblog_comment_media_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ioblog_comment_media_nonce'] ) ), 'ioblog_comment_media' ) ) {
		wp_die( esc_html__( 'The image upload could not be verified. Go back and try again.', 'ioblog-editorial' ), esc_html__( 'Upload error', 'ioblog-editorial' ), array( 'response' => 403, 'back_link' => true ) );
	}

	$file     = $_FILES['ioblog_comment_image'];
	$limit_mb = (int) ioblog_get_setting( 'comment_image_max_mb' );
	if ( (int) $file['size'] > $limit_mb * MB_IN_BYTES ) {
		wp_die( esc_html( sprintf( /* translators: %d: maximum image size in megabytes. */ __( 'The image must be smaller than %d MB.', 'ioblog-editorial' ), $limit_mb ) ), esc_html__( 'File is too large', 'ioblog-editorial' ), array( 'response' => 400, 'back_link' => true ) );
	}

	$checked = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'jpg|jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif' ) );
	if ( empty( $checked['type'] ) ) {
		wp_die( esc_html__( 'Only JPG, PNG, WebP, and GIF files are allowed.', 'ioblog-editorial' ), esc_html__( 'Unsupported file', 'ioblog-editorial' ), array( 'response' => 400, 'back_link' => true ) );
	}

	return $comment_data;
}
add_filter( 'preprocess_comment', 'ioblog_validate_comment_image' );

/**
 * Сохраняет проверенный файл в медиатеке и связывает его с комментарием через meta.
 *
 * @param int $comment_id Идентификатор созданного комментария.
 *
 * @return void
 */
function ioblog_save_comment_image( $comment_id ) {
	if ( ! ioblog_get_setting( 'comments_images' ) || empty( $_FILES['ioblog_comment_image']['name'] ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$comment       = get_comment( $comment_id );
	$attachment_id = media_handle_upload( 'ioblog_comment_image', $comment ? (int) $comment->comment_post_ID : 0 );
	if ( ! is_wp_error( $attachment_id ) ) {
		add_comment_meta( $comment_id, '_ioblog_comment_image_id', (int) $attachment_id, true );
	}
}
add_action( 'comment_post', 'ioblog_save_comment_image' );

/**
 * Выводит комментарий в компактной плоской ленте без карточки внутри карточки.
 *
 * @param WP_Comment $comment Комментарий.
 * @param array      $args    Настройки wp_list_comments.
 * @param int        $depth   Уровень вложенности.
 *
 * @return void
 */
function ioblog_comment_markup( $comment, $args, $depth ) {
	$attachment_id = (int) get_comment_meta( $comment->comment_ID, '_ioblog_comment_image_id', true );
	?>
	<li <?php comment_class( 'io-comment' ); ?> id="comment-<?php comment_ID(); ?>">
		<article class="io-comment__body">
			<header class="io-comment__header">
				<?php echo get_avatar( $comment, 42 ); ?>
				<div><strong><?php comment_author(); ?></strong><time datetime="<?php comment_time( DATE_W3C ); ?>"><?php echo esc_html( get_comment_date( 'j F Y' ) ); ?></time></div>
			</header>
			<div class="io-comment__content"><?php comment_text(); ?></div>
			<?php if ( $attachment_id ) { ?>
				<button class="io-comment-image" type="button" data-full="<?php echo esc_url( wp_get_attachment_image_url( $attachment_id, 'full' ) ); ?>" aria-label="<?php esc_attr_e( 'Open comment image', 'ioblog-editorial' ); ?>"><?php echo wp_get_attachment_image( $attachment_id, 'medium_large', false, array( 'loading' => 'lazy' ) ); ?></button>
			<?php } ?>
			<div class="io-comment__reply"><?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'], 'reply_text' => __( 'Reply', 'ioblog-editorial' ) ) ) ); ?></div>
		</article>
	<?php
}

/**
 * Возвращает поле сообщения с toolbar, превью и собственным nonce загрузки.
 *
 * @return string
 */
function ioblog_comment_field() {
	$emojis = array( '🙂', '👍', '❤️', '🔥', '👏', '🤔', '😂', '🚀', '💡', '✅' );
	$images_enabled = (bool) ioblog_get_setting( 'comments_images' );
	$emojis_enabled = (bool) ioblog_get_setting( 'comments_emojis' );
	$limit_mb       = (int) ioblog_get_setting( 'comment_image_max_mb' );
	ob_start();
	?>
	<div class="io-comment-editor">
		<label class="screen-reader-text" for="comment"><?php esc_html_e( 'Comment', 'ioblog-editorial' ); ?></label>
		<textarea id="comment" name="comment" required placeholder="<?php esc_attr_e( 'Write a comment…', 'ioblog-editorial' ); ?>"></textarea>
		<?php if ( $images_enabled || $emojis_enabled ) { ?>
			<div class="io-comment-editor__toolbar">
				<?php if ( $images_enabled ) { ?><label class="io-comment-tool" title="<?php esc_attr_e( 'Add image', 'ioblog-editorial' ); ?>"><input type="file" name="ioblog_comment_image" accept="image/jpeg,image/png,image/webp,image/gif"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="10" r="2"></circle><path d="m5 17 4-4 3 3 2-2 5 3"></path></svg><span class="screen-reader-text"><?php esc_html_e( 'Add image', 'ioblog-editorial' ); ?></span></label><?php } ?>
				<?php if ( $emojis_enabled ) { ?><button class="io-comment-tool io-emoji-toggle" type="button" aria-expanded="false" title="<?php esc_attr_e( 'Add emoji', 'ioblog-editorial' ); ?>">☺</button><?php } ?>
				<?php if ( $images_enabled ) { ?><span class="io-comment-file"><?php echo esc_html( sprintf( /* translators: %d: maximum image size in megabytes. */ __( 'JPG, PNG, WebP, or GIF up to %d MB', 'ioblog-editorial' ), $limit_mb ) ); ?></span><?php } ?>
			</div>
		<?php } ?>
		<div class="io-comment-preview" hidden></div>
		<?php if ( $emojis_enabled ) { ?><div class="io-emoji-panel" hidden><?php foreach ( $emojis as $emoji ) { ?><button type="button" data-emoji="<?php echo esc_attr( $emoji ); ?>"><?php echo esc_html( $emoji ); ?></button><?php } ?></div><?php } ?>
		<?php if ( $images_enabled ) { wp_nonce_field( 'ioblog_comment_media', 'ioblog_comment_media_nonce' ); } ?>
		<?php echo ioblog_antispam_fields(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Поля подписаны сервером. ?>
		<?php echo ioblog_captcha_field(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Разметка формируется модулем CAPTCHA. ?>
	</div>
	<?php
	return ob_get_clean();
}
