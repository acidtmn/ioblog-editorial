<?php
/**
 * Конфигурация редакционной главной без привязки к конкретному сайту.
 *
 * @package IoblogEditorial
 */

if ( ! class_exists( 'Ioblog_Homepage_Layout' ) ) {
	/** Отвечает только за состав секций и распределение записей между ними. */
	final class Ioblog_Homepage_Layout {
		/** @return string[] */
		public static function section_order() {
			$allowed = array( 'categories', 'editorial', 'latest' );
			$saved   = array_filter( array_map( 'sanitize_key', explode( ',', (string) ioblog_get_setting( 'home_section_order' ) ) ) );
			$order   = array_values( array_intersect( $saved, $allowed ) );
			return array_values( array_unique( array_merge( $order, $allowed ) ) );
		}

		/** @return int[] */
		public static function post_ids( $posts ) {
			return array_values( array_filter( array_map( 'absint', wp_list_pluck( $posts, 'ID' ) ) ) );
		}

		/** @return int */
		public static function featured_id( $post_ids ) {
			$selected = absint( ioblog_get_setting( 'home_featured_post' ) );
			return $selected && 'post' === get_post_type( $selected ) && 'publish' === get_post_status( $selected ) ? $selected : ( $post_ids[0] ?? 0 );
		}

		/** @return int[] */
		public static function editorial_ids( $post_ids, $featured_id ) {
			$selected = array_filter( array_map( 'absint', explode( ',', (string) ioblog_get_setting( 'home_editorial_posts' ) ) ) );
			$selected = array_values( array_filter( $selected, static function ( $post_id ) use ( $featured_id ) {
				return $post_id !== $featured_id && 'post' === get_post_type( $post_id ) && 'publish' === get_post_status( $post_id );
			} ) );
			$fallback = array_values( array_diff( $post_ids, array_merge( array( $featured_id ), $selected ) ) );
			// Автоподбор оставляет хотя бы одну запись для свежей ленты на небольшом сайте.
			$fallback = array_slice( $fallback, 0, max( 0, count( $fallback ) - 1 ) );
			return array_slice( array_values( array_unique( array_merge( $selected, $fallback ) ) ), 0, 5 );
		}

		/** @return int[] */
		public static function latest_ids( $post_ids, $featured_id, $editorial_ids ) {
			return array_values( array_diff( $post_ids, array_merge( array( $featured_id ), $editorial_ids ) ) );
		}

		/** @return string */
		public static function about_url() {
			$page_id = absint( ioblog_get_setting( 'home_about_page' ) );
			return $page_id && 'page' === get_post_type( $page_id ) && 'publish' === get_post_status( $page_id ) ? get_permalink( $page_id ) : '';
		}
	}
}
