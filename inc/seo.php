<?php
/**
 * Полноценный SEO-модуль самостоятельной темы.
 *
 * Модуль отвечает за метатеги и структурированные данные и не вмешивается,
 * если на сайте уже работает специализированный SEO-плагин.
 *
 * @package IoblogEditorial
 */

if ( ! class_exists( 'Ioblog_Seo' ) ) {
	/**
	 * Fallback SEO для темы.
	 */
	class Ioblog_Seo {

		/**
		 * Регистрирует SEO-хуки.
		 *
		 * @return void
		 */
		public static function register() {
			if ( ! self::has_external_seo_plugin() ) {
				remove_action( 'wp_head', 'rel_canonical' );
			}

			add_action( 'wp_head', array( __CLASS__, 'render_meta_tags' ), 1 );
		}

		/**
		 * Выводит базовые метатеги, если SEO-плагин не обнаружен.
		 *
		 * @return void
		 */
		public static function render_meta_tags() {
			// В админке, фидах и служебных ответах фронтовое SEO не нужно.
			if ( is_admin() || is_feed() || is_robots() || is_trackback() ) {
				return;
			}

			// Если на сайте уже установлен SEO-плагин, не дублируем его работу.
			if ( self::has_external_seo_plugin() ) {
				return;
			}

			$title       = self::get_meta_title();
			$description = self::get_meta_description();
			$canonical   = self::get_canonical_url();
			$image_data  = apply_filters( 'ioblog_meta_image_data', self::get_meta_image_data(), get_queried_object_id() );
			$image       = $image_data['url'] ?? '';
			$type        = is_singular( 'post' ) ? 'article' : 'website';

			if ( $description ) {
				printf( '<meta name="description" content="%s">' . PHP_EOL, esc_attr( $description ) );
			}

			if ( $canonical ) {
				printf( '<link rel="canonical" href="%s">' . PHP_EOL, esc_url( $canonical ) );
			}

			if ( $title ) {
				printf( '<meta property="og:title" content="%s">' . PHP_EOL, esc_attr( $title ) );
				printf( '<meta name="twitter:title" content="%s">' . PHP_EOL, esc_attr( $title ) );
			}

			if ( $description ) {
				printf( '<meta property="og:description" content="%s">' . PHP_EOL, esc_attr( $description ) );
				printf( '<meta name="twitter:description" content="%s">' . PHP_EOL, esc_attr( $description ) );
			}

			if ( $canonical ) {
				printf( '<meta property="og:url" content="%s">' . PHP_EOL, esc_url( $canonical ) );
			}

			printf( '<meta property="og:type" content="%s">' . PHP_EOL, esc_attr( $type ) );
			echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . PHP_EOL;
			echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '">' . PHP_EOL;
			echo '<meta name="twitter:card" content="summary_large_image">' . PHP_EOL;

			if ( $image ) {
				printf( '<meta property="og:image" content="%s">' . PHP_EOL, esc_url( $image ) );
				printf( '<meta property="og:image:secure_url" content="%s">' . PHP_EOL, esc_url( $image ) );
				if ( ! empty( $image_data['mime'] ) ) {
					printf( '<meta property="og:image:type" content="%s">' . PHP_EOL, esc_attr( $image_data['mime'] ) );
				}
				if ( ! empty( $image_data['width'] ) && ! empty( $image_data['height'] ) ) {
					printf( '<meta property="og:image:width" content="%d">' . PHP_EOL, absint( $image_data['width'] ) );
					printf( '<meta property="og:image:height" content="%d">' . PHP_EOL, absint( $image_data['height'] ) );
				}
				if ( ! empty( $image_data['alt'] ) ) {
					printf( '<meta property="og:image:alt" content="%s">' . PHP_EOL, esc_attr( $image_data['alt'] ) );
					printf( '<meta name="twitter:image:alt" content="%s">' . PHP_EOL, esc_attr( $image_data['alt'] ) );
				}
				printf( '<meta name="twitter:image" content="%s">' . PHP_EOL, esc_url( $image ) );
			}

			self::render_article_meta();

			self::render_legacy_robots();
			self::render_schema( $title, $description, $canonical, $image );
		}

		/**
		 * Проверяет наличие популярных SEO-плагинов.
		 *
		 * @return bool
		 */
		private static function has_external_seo_plugin() {
			return defined( 'WPSEO_VERSION' ) ||
				defined( 'RANK_MATH_VERSION' ) ||
				defined( 'SEOPRESS_VERSION' ) ||
				defined( 'AIOSEO_VERSION' ) ||
				class_exists( 'Jetpack' );
		}

		/**
		 * Возвращает SEO-заголовок страницы.
		 *
		 * @return string
		 */
		private static function get_meta_title() {
			if ( is_singular() ) {
				$legacy = (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_title', true );
				if ( $legacy ) {
					return self::replace_legacy_variables( $legacy );
				}

				return wp_strip_all_tags( single_post_title( '', false ) );
			}

			if ( is_category() || is_tag() || is_tax() ) {
				return wp_strip_all_tags( single_term_title( '', false ) . ' | ' . get_bloginfo( 'name' ) );
			}

			if ( is_home() || is_front_page() ) {
				return wp_strip_all_tags( get_bloginfo( 'name' ) . ' | ' . get_bloginfo( 'description' ) );
			}

			return wp_strip_all_tags( wp_get_document_title() );
		}

		/**
		 * Возвращает SEO-описание страницы.
		 *
		 * @return string
		 */
		private static function get_meta_description() {
			if ( is_singular() ) {
				$post = get_queried_object();

				if ( ! $post instanceof WP_Post ) {
					return '';
				}

				$legacy = (string) get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );
				if ( $legacy ) {
					return self::normalize_description( self::replace_legacy_variables( $legacy ) );
				}

				// Сначала берём ручной excerpt, потому что он обычно ближе к редакторскому описанию.
				if ( has_excerpt( $post ) ) {
					return self::normalize_description( $post->post_excerpt );
				}

				return self::normalize_description( $post->post_content );
			}

			if ( is_category() || is_tag() || is_tax() ) {
				$term_description = term_description();

				if ( $term_description ) {
					return self::normalize_description( $term_description );
				}
			}

			return self::normalize_description( get_bloginfo( 'description' ) );
		}

		/**
		 * Возвращает canonical URL текущей страницы.
		 *
		 * @return string
		 */
		private static function get_canonical_url() {
			if ( is_singular() ) {
				$legacy = esc_url_raw( get_post_meta( get_queried_object_id(), '_yoast_wpseo_canonical', true ) );
				if ( $legacy ) {
					return $legacy;
				}

				return get_permalink();
			}

			if ( is_home() ) {
				$page_for_posts = (int) get_option( 'page_for_posts' );
				return $page_for_posts ? get_permalink( $page_for_posts ) : home_url( '/' );
			}

			if ( is_front_page() ) {
				return home_url( '/' );
			}

			if ( is_category() || is_tag() || is_tax() ) {
				$term = get_queried_object();

				if ( $term && ! is_wp_error( $term ) ) {
					return get_term_link( $term ) ?: '';
				}
			}

			return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
		}

		/**
		 * Возвращает широкую обложку и её параметры для OG/Twitter.
		 *
		 * @return array<string, int|string>
		 */
		private static function get_meta_image_data() {
			if ( ! is_singular() ) {
				return array();
			}

			$post_id       = get_queried_object_id();
			$legacy_url    = esc_url_raw( get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true ) );
			$attachment_id = $legacy_url ? attachment_url_to_postid( $legacy_url ) : get_post_thumbnail_id( $post_id );

			// Ручная OG-картинка редактора важнее миниатюры, даже если она находится вне медиатеки.
			if ( $legacy_url && ! $attachment_id ) {
				return array(
					'url' => $legacy_url,
					'alt' => get_the_title( $post_id ),
				);
			}

			if ( ! $attachment_id ) {
				return array();
			}

			$image = wp_get_attachment_image_src( $attachment_id, 'ioblog-social' );
			if ( ! $image ) {
				return array();
			}

			$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

			return array(
				'url'    => $image[0],
				'width'  => (int) $image[1],
				'height' => (int) $image[2],
				'mime'   => (string) get_post_mime_type( $attachment_id ),
				'alt'    => $alt ? $alt : get_the_title( $post_id ),
			);
		}

		/**
		 * Очищает и обрезает описание до безопасного SEO-формата.
		 *
		 * @param string $value Исходный текст.
		 *
		 * @return string
		 */
		private static function normalize_description( $value ) {
			$value = wp_strip_all_tags( $value );
			$value = preg_replace( '/\s+/u', ' ', $value );
			$value = trim( (string) $value );

			if ( ! $value ) {
				return '';
			}

			return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 160 ) : substr( $value, 0, 160 );
		}

		/**
		 * Заменяет основные переменные сохранённых шаблонов Yoast.
		 *
		 * @param string $value Сохранённая строка.
		 *
		 * @return string
		 */
		private static function replace_legacy_variables( $value ) {
			return strtr(
				$value,
				array(
					'%%title%%'    => get_the_title( get_queried_object_id() ),
					'%%sitename%%' => get_bloginfo( 'name' ),
					'%%sep%%'      => '—',
				)
			);
		}

		/**
		 * Выводит свойства статьи, которые Telegram и другие сети используют в карточках.
		 *
		 * @return void
		 */
		private static function render_article_meta() {
			if ( ! is_singular( 'post' ) ) {
				return;
			}

			$post = get_queried_object();
			if ( ! $post instanceof WP_Post ) {
				return;
			}

			printf( '<meta property="article:published_time" content="%s">' . PHP_EOL, esc_attr( get_the_date( DATE_W3C, $post ) ) );
			printf( '<meta property="article:modified_time" content="%s">' . PHP_EOL, esc_attr( get_the_modified_date( DATE_W3C, $post ) ) );

			$categories = get_the_category( $post->ID );
			if ( $categories ) {
				printf( '<meta property="article:section" content="%s">' . PHP_EOL, esc_attr( $categories[0]->name ) );
			}

			$tags = get_the_tags( $post->ID );
			if ( $tags ) {
				foreach ( $tags as $tag ) {
					printf( '<meta property="article:tag" content="%s">' . PHP_EOL, esc_attr( $tag->name ) );
				}
			}
		}

		/**
		 * Сохраняет noindex/nofollow для записей, где они были явно заданы редактором.
		 *
		 * @return void
		 */
		private static function render_legacy_robots() {
			if ( ! is_singular() ) {
				return;
			}

			$rules = array();
			if ( '1' === (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_meta-robots-noindex', true ) ) {
				$rules[] = 'noindex';
			}
			if ( '1' === (string) get_post_meta( get_queried_object_id(), '_yoast_wpseo_meta-robots-nofollow', true ) ) {
				$rules[] = 'nofollow';
			}

			if ( $rules ) {
				printf( '<meta name="robots" content="%s">' . PHP_EOL, esc_attr( implode( ', ', $rules ) ) );
			}
		}

		/**
		 * Выводит schema.org для сайта или текущей статьи.
		 *
		 * @param string $title       SEO-заголовок.
		 * @param string $description SEO-описание.
		 * @param string $canonical   Канонический URL.
		 * @param string $image       Главное изображение.
		 *
		 * @return void
		 */
		private static function render_schema( $title, $description, $canonical, $image ) {
			if ( is_singular( 'post' ) ) {
				$post   = get_queried_object();
				$schema = array(
					'@context'         => 'https://schema.org',
					'@type'            => 'BlogPosting',
					'headline'         => $title,
					'description'      => $description,
					'url'              => $canonical,
					'datePublished'    => get_the_date( DATE_W3C, $post ),
					'dateModified'     => get_the_modified_date( DATE_W3C, $post ),
					'mainEntityOfPage' => array( '@type' => 'WebPage', '@id' => $canonical ),
					'author'           => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', $post->post_author ) ),
					'publisher'        => array( '@type' => 'Organization', 'name' => get_bloginfo( 'name' ), 'logo' => array( '@type' => 'ImageObject', 'url' => get_site_icon_url( 512 ) ) ),
				);
				if ( $image ) {
					$schema['image'] = array( $image );
				}
			} else {
				$schema = array(
					'@context'        => 'https://schema.org',
					'@type'           => 'WebSite',
					'name'            => get_bloginfo( 'name' ),
					'url'             => home_url( '/' ),
					'potentialAction' => array( '@type' => 'SearchAction', 'target' => home_url( '/?s={search_term_string}' ), 'query-input' => 'required name=search_term_string' ),
				);
			}

			echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . PHP_EOL;
		}
	}
}

Ioblog_Seo::register();
