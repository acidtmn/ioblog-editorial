<?php
/** Сопоставление объектов WordPress: ID одного сайта не является идентификатором другого. */
final class Ioblog_Settings_Transfer_References {
	public static function collect( $snapshot ) {
		$ids = array( $snapshot['settings']['home_featured_post'] ?? 0, $snapshot['settings']['home_about_page'] ?? 0, $snapshot['mods']['custom_logo'] ?? 0, $snapshot['site_icon'] ?? 0 );
		$ids = array_merge( $ids, explode( ',', $snapshot['settings']['home_editorial_posts'] ?? '' ) );
		$result = array( 'posts' => array(), 'menus' => array() );
		foreach ( array_unique( array_map( 'absint', $ids ) ) as $id ) {
			$post = $id ? get_post( $id ) : null;
			if ( $post ) { $result['posts'][ $id ] = array( 'slug' => $post->post_name, 'type' => $post->post_type ); }
		}
		foreach ( $snapshot['mods']['nav_menu_locations'] ?? array() as $id ) {
			$menu = $id ? get_term( $id, 'nav_menu' ) : null;
			if ( $menu && ! is_wp_error( $menu ) ) { $result['menus'][ $id ] = $menu->slug; }
		}
		return $result;
	}

	private static function post( $id, $type, $data, $same_site ) {
		if ( 0 === $id ) { return 0; }
		$identity = $data['references']['posts'][ $id ] ?? null;
		if ( ! is_array( $identity ) || ! is_string( $identity['slug'] ?? null ) || $type !== ( $identity['type'] ?? null ) ) { return null; }
		$post = $same_site ? get_post( $id ) : get_page_by_path( $identity['slug'], OBJECT, $type );
		// Проверяем не только существование, но и тип/slug: одинаковые ID могут относиться к разным статьям.
		if ( ! $post || $post->post_type !== $type || $post->post_name !== $identity['slug'] || ( 'attachment' !== $type && ( 'publish' !== $post->post_status || $post->post_password ) ) ) { return null; }
		if ( 'attachment' === $type && ! wp_attachment_is_image( $post->ID ) ) { return null; }
		return $post->ID;
	}

	/** Отсутствующие ссылки остаются в экспортном файле, но не портят работающую конфигурацию получателя. */
	public static function resolve( $data ) {
		$same = untrailingslashit( $data['site_url'] ) === untrailingslashit( home_url( '/' ) );
		$skipped = array();
		foreach ( array( 'home_featured_post' => 'post', 'home_about_page' => 'page' ) as $key => $type ) {
			if ( ! isset( $data['settings'][ $key ] ) ) { continue; }
			$id = self::post( $data['settings'][ $key ], $type, $data, $same );
			if ( null === $id ) { unset( $data['settings'][ $key ] ); $skipped[] = $key; }
			else { $data['settings'][ $key ] = $id; }
		}
		if ( ! empty( $data['settings']['home_editorial_posts'] ) ) {
			$mapped = array();
			foreach ( explode( ',', $data['settings']['home_editorial_posts'] ) as $id ) {
				$mapped[] = self::post( (int) $id, 'post', $data, $same );
			}
			// Частично найденная подборка не заменяет всю текущую: администратор увидит предупреждение.
			if ( in_array( null, $mapped, true ) ) { unset( $data['settings']['home_editorial_posts'] ); $skipped[] = 'home_editorial_posts'; }
			else { $data['settings']['home_editorial_posts'] = implode( ',', $mapped ); }
		}
		foreach ( array( 'custom_logo', 'site_icon' ) as $key ) {
			$value = 'site_icon' === $key ? $data[ $key ] : ( $data['mods'][ $key ] ?? null );
			if ( null === $value ) { continue; }
			$id = self::post( $value, 'attachment', $data, $same );
			if ( null === $id ) {
				if ( 'site_icon' === $key ) { unset( $data[ $key ] ); } else { unset( $data['mods'][ $key ] ); }
				$skipped[] = $key;
			} elseif ( 'site_icon' === $key ) { $data[ $key ] = $id; } else { $data['mods'][ $key ] = $id; }
		}
		foreach ( $data['mods']['nav_menu_locations'] ?? array() as $location => $id ) {
			$slug = $data['references']['menus'][ $id ] ?? '';
			$menu = $id && is_string( $slug ) && $slug ? get_term_by( 'slug', $slug, 'nav_menu' ) : null;
			if ( $id && ! $menu ) { unset( $data['mods']['nav_menu_locations'][ $location ] ); $skipped[] = 'nav_menu_locations.' . $location; }
			elseif ( $menu ) { $data['mods']['nav_menu_locations'][ $location ] = (int) $menu->term_id; }
		}
		// ID записи custom CSS переносить нельзя: сам текст CSS восстанавливается штатным API ядра.
		unset( $data['mods']['custom_css_post_id'] );
		return array( 'data' => $data, 'skipped' => $skipped );
	}
}
