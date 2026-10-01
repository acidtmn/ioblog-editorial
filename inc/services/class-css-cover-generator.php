<?php
/**
 * Детерминированная генерация данных для лёгких CSS-обложек.
 *
 * @package IoblogEditorial
 */

if ( ! class_exists( 'Ioblog_Css_Cover_Generator' ) ) {
	/**
	 * Отделяет правила автогенерации от WordPress-фильтров и HTML-шаблона.
	 */
	final class Ioblog_Css_Cover_Generator {

		/** @var string[] Варианты, которые можно назначить вручную. */
		private const VARIANTS = array(
			'mint-keycaps',
			'terminal',
			'signal',
			'mobile',
			'security',
			'steam',
			'road',
			'space',
			'paper',
			'media',
			'aurora',
			'cobalt',
			'coral',
			'plum',
		);

		/** @var string[] Специальная Win+V-обложка не участвует в автоматическом выборе. */
		private const AUTO_VARIANTS = array(
			'terminal',
			'signal',
			'mobile',
			'security',
			'steam',
			'road',
			'space',
			'paper',
			'media',
			'aurora',
			'cobalt',
			'coral',
			'plum',
		);

		/** @var string[] Геометрические мотивы добавляют разнообразие без дополнительных картинок. */
		private const MOTIFS = array( 'tiles', 'orbit', 'rails' );

		/**
		 * Возвращает полный закрытый список допустимых CSS-классов палитры.
		 *
		 * @return string[]
		 */
		public static function variants() {
			return self::VARIANTS;
		}

		/**
		 * Возвращает ручную палитру либо стабильно вычисляет автоматическую.
		 *
		 * @param int $post_id Идентификатор записи.
		 *
		 * @return string
		 */
		public static function resolve_variant( $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post instanceof WP_Post ) {
				return '';
			}

			$saved = sanitize_key( (string) get_post_meta( $post->ID, '_ioblog_css_cover_variant', true ) );
			if ( 'none' === $saved ) {
				return '';
			}

			// Ручной выбор редактора всегда важнее автоматической палитры.
			if ( in_array( $saved, self::VARIANTS, true ) ) {
				return $saved;
			}

			// Автообложка нужна только обычной записи без настоящей миниатюры.
			if ( ! self::is_eligible( $post ) ) {
				return '';
			}

			return self::automatic_variant( $post->ID );
		}

		/** Вычисляет автоцвет независимо от ручного выбора, чтобы редактор показывал будущий результат. */
		public static function automatic_variant( $post_id ) {
			$post = get_post( $post_id );
			// Новая запись ещё может не иметь объекта; нейтральная палитра сохраняет рабочий предпросмотр.
			if ( ! $post instanceof WP_Post ) {
				return 'paper';
			}
			$index = self::stable_index( $post, 'palette', count( self::AUTO_VARIANTS ) );
			return self::AUTO_VARIANTS[ $index ];
		}

		/**
		 * Определяет, была ли палитра создана автоматически, а не сохранена редактором.
		 *
		 * @param int $post_id Идентификатор записи.
		 *
		 * @return bool
		 */
		public static function is_automatic( $post_id ) {
			$saved = sanitize_key( (string) get_post_meta( $post_id, '_ioblog_css_cover_variant', true ) );
			return ! in_array( $saved, self::VARIANTS, true ) && 'none' !== $saved && '' !== self::resolve_variant( $post_id );
		}

		/**
		 * Собирает заголовок, рубрику и визуальные модификаторы для шаблона.
		 *
		 * @param int    $post_id Идентификатор записи.
		 * @param string $variant Уже проверенная палитра.
		 *
		 * @return array<string, string|bool>
		 */
		public static function build_data( $post_id, $variant ) {
			$post       = get_post( $post_id );
			$categories = get_the_category( $post_id );
			$category   = $categories ? $categories[0] : null;
			$automatic  = self::is_automatic( $post_id );
			$kicker     = sanitize_text_field( get_post_meta( $post_id, '_ioblog_css_cover_kicker', true ) );
			$title      = sanitize_text_field( get_post_meta( $post_id, '_ioblog_css_cover_title', true ) );
			$caption    = sanitize_text_field( get_post_meta( $post_id, '_ioblog_css_cover_caption', true ) );

			// Старые метаданные иногда уже содержат бренд, но шаблон выводит его самостоятельно.
			$kicker = preg_replace( '/^(?:IO\s*:?\s*BLOG\s*\/\s*)+/iu', '', $kicker );

			// Автоматическая обложка показывает исходный заголовок; ручная сохраняет прежнее компактное поведение.
			if ( ! $title ) {
				$title = $automatic ? get_the_title( $post_id ) : wp_trim_words( get_the_title( $post_id ), 8, '' );
			}
			$title = self::clean_title( $title );

			// Подпись следует тематике рубрики, а не случайному цвету выбранной палитры.
			if ( ! $caption ) {
				$caption = self::caption_for( $category, $variant );
			}

			return array(
				'variant'    => $variant,
				'kicker'     => $kicker ? $kicker : ( $category ? $category->name : __( 'Articles', 'ioblog-editorial' ) ),
				'title'      => $title ? $title : __( 'New publication', 'ioblog-editorial' ),
				'caption'    => $caption,
				'edition'    => 'IO · ' . str_pad( (string) $post_id, 3, '0', STR_PAD_LEFT ),
				'alt'        => $post instanceof WP_Post ? get_the_title( $post ) : $title,
				'automatic'  => $automatic,
				'motif'      => $automatic && $post instanceof WP_Post ? self::MOTIFS[ self::stable_index( $post, 'motif', count( self::MOTIFS ) ) ] : 'tiles',
				'title_size' => self::title_size( $title ),
			);
		}

		/**
		 * Проверяет тип записи и наличие реальной медиатеки без вызова фильтра has_post_thumbnail.
		 *
		 * @param WP_Post $post Проверяемая запись.
		 *
		 * @return bool
		 */
		private static function is_eligible( $post ) {
			if ( 'post' !== $post->post_type || in_array( $post->post_status, array( 'auto-draft', 'trash', 'inherit' ), true ) ) {
				return false;
			}

			// ID вложения проверяется напрямую, чтобы фильтр темы не принял CSS-обложку за файл.
			return 0 === (int) get_post_thumbnail_id( $post->ID );
		}

		/**
		 * Получает повторяемый индекс из заголовка, ID и назначения вариации.
		 *
		 * @param WP_Post $post  Запись-источник.
		 * @param string  $salt  Независимое пространство выбора.
		 * @param int     $count Количество вариантов.
		 *
		 * @return int
		 */
		private static function stable_index( $post, $salt, $count ) {
			$seed = hash( 'sha256', $post->ID . '|' . $post->post_title . '|' . $salt );
			return hexdec( substr( $seed, 0, 8 ) ) % max( 1, $count );
		}

		/**
		 * Очищает заголовок, не сокращая авторскую формулировку автоматической обложки.
		 *
		 * @param string $title Исходный заголовок.
		 *
		 * @return string
		 */
		private static function clean_title( $title ) {
			$title = html_entity_decode( wp_strip_all_tags( (string) $title ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
			return trim( preg_replace( '/\s+/u', ' ', $title ) );
		}

		/**
		 * Выбирает размер шрифта по длине, сохраняя полный заголовок на обложке.
		 *
		 * @param string $title Очищенный заголовок.
		 *
		 * @return string
		 */
		private static function title_size( $title ) {
			$length = mb_strlen( $title );
			if ( $length > 82 ) {
				return 'xlong';
			}
			if ( $length > 54 ) {
				return 'long';
			}

			return 'regular';
		}

		/**
		 * Возвращает редакционную подпись по slug основной рубрики.
		 *
		 * @param WP_Term|null $category Основная рубрика.
		 * @param string       $variant Проверенная палитра.
		 *
		 * @return string
		 */
		private static function caption_for( $category, $variant ) {
			$by_category = array(
				'soft'                  => __( 'A focused tool', 'ioblog-editorial' ),
				'computers'             => __( 'System under control', 'ioblog-editorial' ),
				'internet-and-networks' => __( 'Reliable connectivity', 'ioblog-editorial' ),
				'mobile'                => __( 'Phone under control', 'ioblog-editorial' ),
				'cybersecurity'         => __( 'Safe practices', 'ioblog-editorial' ),
				'lifehacks'             => __( 'Useful habit', 'ioblog-editorial' ),
				'games'                 => __( 'Gaming practice', 'ioblog-editorial' ),
				'avto'                  => __( 'Driver practice', 'ioblog-editorial' ),
				'ai'                    => __( 'Understanding AI', 'ioblog-editorial' ),
				'science-space'         => __( 'Explained simply', 'ioblog-editorial' ),
				'video'                 => __( 'Working with video', 'ioblog-editorial' ),
				'facts'                 => __( 'The essentials', 'ioblog-editorial' ),
				'technology'            => __( 'Technology without the noise', 'ioblog-editorial' ),
			);
			$by_variant  = array(
				'terminal' => __( 'Step-by-step setup', 'ioblog-editorial' ),
				'signal'   => __( 'Reliable connectivity', 'ioblog-editorial' ),
				'mobile'   => __( 'Phone under control', 'ioblog-editorial' ),
				'security' => __( 'Safe practices', 'ioblog-editorial' ),
				'steam'    => __( 'Game library', 'ioblog-editorial' ),
				'road'     => __( 'Driver practice', 'ioblog-editorial' ),
				'space'    => __( 'Explained simply', 'ioblog-editorial' ),
				'paper'    => __( 'Everyday organization', 'ioblog-editorial' ),
				'media'    => __( 'Working with video', 'ioblog-editorial' ),
			);

			$slug = $category instanceof WP_Term ? $category->slug : '';
			return $by_category[ $slug ] ?? $by_variant[ $variant ] ?? __( 'Useful and practical', 'ioblog-editorial' );
		}
	}
}
