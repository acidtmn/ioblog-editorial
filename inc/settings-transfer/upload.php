<?php
/** Проверка загруженного файла без сохранения копии с ключами в публичной медиатеке. */
final class Ioblog_Settings_Transfer_Upload {
	public static function read( $file ) {
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== ( $file['error'] ?? null ) || ! is_string( $file['tmp_name'] ?? null ) || ! is_uploaded_file( $file['tmp_name'] ) || ! is_string( $file['name'] ?? null ) || 'json' !== strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) ) { throw new InvalidArgumentException( 'upload' ); }
		$size = filesize( $file['tmp_name'] );
		if ( false === $size || $size < 2 || $size > Ioblog_Settings_Transfer_Schema::LIMIT ) { throw new InvalidArgumentException( 'size' ); }
		$contents = file_get_contents( $file['tmp_name'], false, null, 0, Ioblog_Settings_Transfer_Schema::LIMIT + 1 );
		if ( false === $contents || strlen( $contents ) !== $size ) { throw new RuntimeException( 'read' ); }
		return $contents;
	}
}
