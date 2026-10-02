<?php
/**
 * Серверная часть живого поиска от трёх символов.
 *
 * @package IoblogEditorial
 */

/**
 * Приводит запись к безопасному JSON-контракту поисковой карточки.
 *
 * @param int $post_id Идентификатор записи.
 *
 * @return array<string, string|int>
 */
function ioblog_search_item( $post_id ) {
	$category = ioblog_primary_category( $post_id );
	$image    = get_the_post_thumbnail_url( $post_id, 'ioblog-card-small' );

	return array(
		'id'        => $post_id,
		'title'     => get_the_title( $post_id ),
		'url'       => get_permalink( $post_id ),
		'category'  => $category ? $category->name : '',
		'date'      => get_the_date( 'd.m.Y', $post_id ),
		'thumbnail' => $image ? $image : '',
	);
}

/**
 * Выполняет ограниченный публичный запрос только по опубликованным статьям.
 *
 * @param string $search_term Поисковая строка.
 *
 * @return array<int, array<string, string|int>>
 */
function ioblog_live_search_results( $search_term ) {
	$length = function_exists( 'mb_strlen' ) ? mb_strlen( $search_term ) : strlen( $search_term );
	$minimum = (int) ioblog_get_setting( 'search_min_chars' );
	if ( $length < $minimum ) {
		return array();
	}

	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			's'                   => $search_term,
			'posts_per_page'      => (int) ioblog_get_setting( 'search_limit' ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	$items = array_map( 'ioblog_search_item', wp_list_pluck( $query->posts, 'ID' ) );
	wp_reset_postdata();
	do_action( 'ioblog_search_completed', $search_term, $items );
	return $items;
}

/**
 * Отдаёт результаты через стандартный admin-ajax, не зависящий от rewrite-правил сервера.
 *
 * @return void
 */
function ioblog_ajax_search() {
	$search_term = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
	wp_send_json( array( 'items' => ioblog_live_search_results( trim( $search_term ) ) ) );
}
add_action( 'wp_ajax_ioblog_live_search', 'ioblog_ajax_search' );
add_action( 'wp_ajax_nopriv_ioblog_live_search', 'ioblog_ajax_search' );

/**
 * Подключает клиент поиска и передаёт только публичный endpoint и подписи.
 *
 * @return void
 */
function ioblog_enqueue_search() {
	wp_enqueue_script( 'ioblog-search', get_theme_file_uri( 'assets/js/search.js' ), array( 'wp-i18n' ), ioblog_asset_version( 'assets/js/search.js' ), true );
	wp_localize_script(
		'ioblog-search',
		'ioblogSearch',
		array(
			'endpoint' => admin_url( 'admin-ajax.php' ),
			'minChars' => (int) ioblog_get_setting( 'search_min_chars' ),
			'labels'   => array(
				'empty'   => sprintf( /* translators: %d: minimum number of search characters. */ __( 'Enter at least %d characters', 'ioblog-editorial' ), (int) ioblog_get_setting( 'search_min_chars' ) ),
				'loading' => __( 'Searching articles…', 'ioblog-editorial' ),
				'none'    => __( 'Nothing found. Try another search.', 'ioblog-editorial' ),
				'error'   => __( 'Search is temporarily unavailable. Please try again.', 'ioblog-editorial' ),
				/* translators: %d: number of matching articles. */
				'count'   => __( '%d matching articles', 'ioblog-editorial' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'ioblog_enqueue_search' );
