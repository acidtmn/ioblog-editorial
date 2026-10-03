<?php
/** Читаемые подписи и скрытие конфиденциальных значений только для предварительного просмотра. */
final class Ioblog_Settings_Transfer_Presentation {
	public static function summary( $key, $value ) {
		if ( preg_match( '/(key|secret|token|password|credential|code|redirects|custom_css|community_config)/i', $key ) ) {
			return empty( $value ) ? __( 'Empty', 'ioblog-editorial' ) : __( 'Protected content included', 'ioblog-editorial' );
		}
		$value = is_scalar( $value ) ? (string) $value : wp_json_encode( $value );
		return mb_strimwidth( $value, 0, 140, '...', 'UTF-8' );
	}

	public static function label( $key, $section = 'settings' ) {
		$slots = array( 'home' => __( 'After the hero section', 'ioblog-editorial' ), 'inline' => __( 'Inside an article', 'ioblog-editorial' ), 'sidebar' => __( 'Right sidebar', 'ioblog-editorial' ), 'after' => __( 'After the article', 'ioblog-editorial' ) );
		if ( preg_match( '/^ad_(home|inline|sidebar|after)_(code|enabled)$/D', $key, $match ) ) { return $slots[ $match[1] ]; }
		$labels = array(
			'site_language' => __( 'WordPress language', 'ioblog-editorial' ),
			'search_min_chars' => __( 'Minimum search characters', 'ioblog-editorial' ),
			'search_limit' => __( 'Results in the search window', 'ioblog-editorial' ),
			'home_show_hero' => __( 'Hero section', 'ioblog-editorial' ),
			'home_show_categories' => __( 'Category shortcuts', 'ioblog-editorial' ),
			'home_show_editorial' => __( 'Editor’s choice', 'ioblog-editorial' ),
			'home_featured_post' => __( 'Featured article', 'ioblog-editorial' ),
			'home_about_page' => __( 'About page', 'ioblog-editorial' ),
			'home_editorial_posts' => __( 'Editor’s choice', 'ioblog-editorial' ),
			'home_posts_per_page' => __( 'Posts per page', 'ioblog-editorial' ),
			'home_section_order' => __( 'Section order', 'ioblog-editorial' ),
			'typography_preset' => __( 'Typography preset', 'ioblog-editorial' ),
			'article_layout' => __( 'Article layout', 'ioblog-editorial' ),
			'reading_font_size' => __( 'Body text size, px', 'ioblog-editorial' ),
			'reading_line_height' => __( 'Line height', 'ioblog-editorial' ),
			'reading_width' => __( 'Reading width, px', 'ioblog-editorial' ),
			'captcha_provider' => __( 'CAPTCHA provider', 'ioblog-editorial' ),
			'captcha_site_key' => __( 'Site key', 'ioblog-editorial' ),
			'captcha_secret_key' => __( 'Secret key', 'ioblog-editorial' ),
			'comments_images' => __( 'Images in comments', 'ioblog-editorial' ),
			'comments_emojis' => __( 'Emoji panel', 'ioblog-editorial' ),
			'claps_enabled' => __( 'Article claps', 'ioblog-editorial' ),
			'library_enabled' => __( 'Personal reading library', 'ioblog-editorial' ),
			'svg_uploads' => __( 'Safe SVG uploads', 'ioblog-editorial' ),
			'comment_image_max_mb' => __( 'Image limit, MB', 'ioblog-editorial' ),
			'social_telegram_url' => 'Telegram', 'social_max_url' => 'MAX', 'social_vk_url' => 'VK', 'social_ok_url' => __( 'Odnoklassniki', 'ioblog-editorial' ),
			'ioblog_hero_title' => __( 'First title line', 'ioblog-editorial' ),
			'ioblog_hero_accent' => __( 'Accent line', 'ioblog-editorial' ),
			'ioblog_hero_description' => __( 'Description', 'ioblog-editorial' ),
			'ioblog_footer_text' => __( 'Site description', 'ioblog-editorial' ),
			'ioblog_footer_copyright' => __( 'Copyright text', 'ioblog-editorial' ),
			'ioblog_color_scheme' => __( 'Default color scheme', 'ioblog-editorial' ),
			'ioblog_search_heading' => __( 'Search heading', 'ioblog-editorial' ),
			'nav_menu_locations' => __( 'Menus', 'ioblog-editorial' ),
			'custom_logo' => __( 'Logo', 'ioblog-editorial' ),
			'site_icon' => __( 'Site icon', 'ioblog-editorial' ),
			'custom_css' => __( 'Custom CSS', 'ioblog-editorial' ),
			'blogname' => __( 'Site title', 'ioblog-editorial' ),
			'blogdescription' => __( 'Tagline', 'ioblog-editorial' ),
			'ad_inline_paragraph' => __( 'After paragraph number', 'ioblog-editorial' ),
			'head_code' => __( 'Code before </head>', 'ioblog-editorial' ),
			'footer_code' => __( 'Code before </body>', 'ioblog-editorial' ),
			'redirects' => __( 'Short links', 'ioblog-editorial' ),
		);
		return $labels[ $key ] ?? Ioblog_Settings_Transfer_Schema::groups()[ Ioblog_Settings_Transfer_Schema::group( $key, $section ) ];
	}
}
