<?php
/**
 * Консервативная поддержка SVG для администраторов.
 *
 * @package IoblogEditorial
 */

/**
 * Разрешает SVG только доверенным администраторам и только при включённой настройке.
 *
 * @param array $mimes Допустимые MIME-типы.
 *
 * @return array
 */
function ioblog_allow_svg_mime( $mimes ) {
	if ( current_user_can( 'manage_options' ) && ioblog_get_setting( 'svg_uploads' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'ioblog_allow_svg_mime' );

/**
 * Отклоняет активный контент, внешние ресурсы и некорректный XML до загрузки.
 *
 * @param array $file Данные временного файла WordPress.
 *
 * @return array
 */
function ioblog_validate_svg_upload( $file ) {
	if ( 'svg' !== strtolower( pathinfo( $file['name'] ?? '', PATHINFO_EXTENSION ) ) ) {
		return $file;
	}

	if ( ! current_user_can( 'manage_options' ) || ! ioblog_get_setting( 'svg_uploads' ) ) {
		$file['error'] = __( 'SVG uploads are disabled or unavailable for your role.', 'ioblog-editorial' );
		return $file;
	}

	if ( empty( $file['tmp_name'] ) || (int) filesize( $file['tmp_name'] ) > MB_IN_BYTES ) {
		$file['error'] = __( 'The SVG must be a valid file no larger than 1 MB.', 'ioblog-editorial' );
		return $file;
	}

	$svg = file_get_contents( $file['tmp_name'] );
	if ( false === $svg || preg_match( '/<!DOCTYPE|<!ENTITY|<\s*(script|iframe|object|embed|foreignObject|audio|video)\b|\son[a-z]+\s*=|javascript\s*:|data\s*:\s*text\/html|(?:href|src)\s*=\s*["\']\s*https?:/iu', $svg ) ) {
		$file['error'] = __( 'The SVG contains prohibited active or external content.', 'ioblog-editorial' );
		return $file;
	}

	$previous = libxml_use_internal_errors( true );
	$document = new DOMDocument();
	$loaded   = $document->loadXML( $svg, LIBXML_NONET | LIBXML_NOBLANKS );
	libxml_clear_errors();
	libxml_use_internal_errors( $previous );

	if ( ! $loaded || 'svg' !== strtolower( $document->documentElement->localName ?? '' ) ) {
		$file['error'] = __( 'The file is not a valid SVG.', 'ioblog-editorial' );
	}

	return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'ioblog_validate_svg_upload' );

/**
 * Помогает WordPress распознать уже проверенный SVG после MIME-проверки.
 *
 * @param array  $data     Результат проверки.
 * @param string $file     Путь к файлу.
 * @param string $filename Исходное имя.
 * @param array  $mimes    Разрешённые MIME-типы.
 *
 * @return array
 */
function ioblog_confirm_svg_filetype( $data, $file, $filename, $mimes ) {
	unset( $file, $mimes );
	if ( current_user_can( 'manage_options' ) && ioblog_get_setting( 'svg_uploads' ) && 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		$data['ext']  = 'svg';
		$data['type'] = 'image/svg+xml';
	}

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'ioblog_confirm_svg_filetype', 10, 4 );
