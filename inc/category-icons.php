<?php
/**
 * Самостоятельный набор SVG-иконок рубрик IO Blog.
 *
 * Здесь сосредоточена только логика подключения фронтенд-ресурсов,
 * которые заменяют проблемные растровые иконки рубрик на предсказуемые SVG.
 *
 * @package IoblogEditorial
 */

if ( ! class_exists( 'Ioblog_Category_Icons' ) ) {
	/**
	 * Подключает ресурсы для рубричных иконок.
	 */
	class Ioblog_Category_Icons {

		/**
		 * Регистрирует хуки WordPress.
		 *
		 * @return void
		 */
		public static function register() {
			// Самостоятельная тема вызывает компонент только из home.php,
			// поэтому глобальные хуки здесь намеренно не регистрируются.
		}

		/**
		 * Подменяет вывод родительской темы после регистрации её хуков.
		 *
		 * @return void
		 */
		public static function replace_parent_output() {
			// Метод оставлен пустым для обратной совместимости с ранней версией нашего модуля.
		}

		/**
		 * Выводит список популярных рубрик с едиными SVG-иконками.
		 *
		 * @return void
		 */
		public static function render_categories( $force = true ) {
			// Защищаем компонент от случайного использования за пределами главной ленты.
			if ( ! $force || ! is_home() ) {
				return;
			}

			$heading = __( 'Popular topics', 'ioblog-editorial' );
			$filter  = '';
			$limit   = 13;
			$args    = array(
				'taxonomy' => 'category',
				'orderby'  => 'count',
				'order'    => 'DESC',
				'number'   => $limit,
			);

			if ( $filter ) {
				// Фильтр Customizer задаёт порядок, поэтому не заменяем его сортировкой по популярности.
				$args['slug']    = explode( ',', $filter );
				$args['orderby'] = 'slug__in';
				$args['order']   = 'ASC';
				$args['number']  = 0;
			}

			$categories = get_categories( $args );
			if ( ! $categories ) {
				return;
			}
			?>
			<div class="cs-categories-list cs-categories-list-container">
				<?php if ( $heading ) { ?>
					<h2 class="cs-categories-list__heading"><?php echo esc_html( $heading ); ?></h2>
				<?php } ?>
				<div class="cs-categories-list__wrapper">
					<?php foreach ( $categories as $category ) { ?>
						<div class="cs-category-item" data-category-slug="<?php echo esc_attr( $category->slug ); ?>">
							<div class="cs-category-item__icon-box"><span class="cs-category-item__icon" aria-hidden="true"><?php echo self::get_icon( $category->slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG формируется только внутренними методами темы. ?></span></div>
							<div class="cs-category-item__title"><?php echo esc_html( $category->name ); ?></div>
							<a href="<?php echo esc_url( get_term_link( $category ) ); ?>" class="cs-category-item__link" aria-label="<?php echo esc_attr( $category->name ); ?>"></a>
						</div>
					<?php } ?>
				</div>
			</div>
			<?php
		}

		/**
		 * Возвращает SVG рубрики или нейтральную иконку для новой рубрики.
		 *
		 * @param string $slug Ярлык рубрики WordPress.
		 *
		 * @return string
		 */
		public static function get_icon( $slug ) {
			$icons = self::get_icon_map();
			return isset( $icons[ $slug ] ) ? $icons[ $slug ] : self::build_folder_icon();
		}

		/**
		 * Возвращает карту "slug рубрики -> SVG-иконка".
		 *
		 * Иконки сделаны монохромными через currentColor,
		 * поэтому они одинаково хорошо живут и в светлой, и в тёмной схемах.
		 *
		 * @return array<string, string>
		 */
		private static function get_icon_map() {
			return array(
				'avto'                  => self::build_car_icon(),
				'video'                 => self::build_video_icon(),
				'games'                 => self::build_gamepad_icon(),
				'ai'                    => self::build_brain_icon(),
				'internet-and-networks' => self::build_network_icon(),
				'cybersecurity'         => self::build_shield_icon(),
				'computers'             => self::build_monitor_icon(),
				'lifehacks'             => self::build_spark_icon(),
				'mobile'                => self::build_phone_icon(),
				'science-space'         => self::build_rocket_icon(),
				'soft'                  => self::build_window_icon(),
				'technology'            => self::build_chip_icon(),
				'facts'                 => self::build_info_icon(),
			);
		}

		/**
		 * Формирует общий SVG-контейнер.
		 *
		 * @param string $content SVG-пути конкретной иконки.
		 *
		 * @return string
		 */
		private static function wrap_svg( $content ) {
			return sprintf(
				'<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">%s</svg>',
				$content
			);
		}

		/**
		 * Иконка автомобилей.
		 *
		 * @return string
		 */
		private static function build_car_icon() {
			return self::wrap_svg( '<path d="M5 16l1.4-4.3A2 2 0 0 1 8.3 10h7.4a2 2 0 0 1 1.9 1.4L19 16"/><path d="M4 16h16"/><path d="M6 16v2"/><path d="M18 16v2"/><circle cx="7.5" cy="16.5" r="1.5"/><circle cx="16.5" cy="16.5" r="1.5"/>' );
		}

		/**
		 * Иконка видео.
		 *
		 * @return string
		 */
		private static function build_video_icon() {
			return self::wrap_svg( '<rect x="3" y="6" width="12" height="12" rx="2"/><path d="M15 10l5-3v10l-5-3z"/><path d="M9 10l4 2-4 2z"/>' );
		}

		/**
		 * Иконка игр.
		 *
		 * @return string
		 */
		private static function build_gamepad_icon() {
			return self::wrap_svg( '<path d="M7 10h10a3 3 0 0 1 2.9 3.8l-.6 2.2a1.8 1.8 0 0 1-2.8 1l-2.2-1.5a4 4 0 0 0-4.6 0L7.5 17a1.8 1.8 0 0 1-2.8-1l-.6-2.2A3 3 0 0 1 7 10z"/><path d="M8 13v3"/><path d="M6.5 14.5h3"/><circle cx="15.5" cy="13.5" r=".8"/><circle cx="17.8" cy="15.2" r=".8"/>' );
		}

		/**
		 * Иконка ИИ и нейросетей.
		 *
		 * @return string
		 */
		private static function build_brain_icon() {
			return self::wrap_svg( '<path d="M9.2 6.2A2.7 2.7 0 0 1 12 4.5a2.9 2.9 0 0 1 2.8 2 2.6 2.6 0 0 1 2.1 2.6 2.7 2.7 0 0 1 1.6 2.5 2.8 2.8 0 0 1-1.8 2.6 2.8 2.8 0 0 1-2.7 3.3 3.1 3.1 0 0 1-2-.7 3.1 3.1 0 0 1-2 .7 2.8 2.8 0 0 1-2.7-3.3A2.8 2.8 0 0 1 5.5 12a2.7 2.7 0 0 1 1.6-2.5 2.6 2.6 0 0 1 2.1-3.3z"/><path d="M10 8.5v7"/><path d="M14 8.5v7"/><path d="M10 11.2l-2-.9"/><path d="M14 11.2l2-.9"/><path d="M10.2 14.1l-1.7 1.1"/><path d="M13.8 14.1l1.7 1.1"/>' );
		}

		/**
		 * Иконка интернета и сетей.
		 *
		 * @return string
		 */
		private static function build_network_icon() {
			return self::wrap_svg( '<circle cx="12" cy="5.5" r="2"/><circle cx="6" cy="17" r="2"/><circle cx="18" cy="17" r="2"/><path d="M12 7.5v4"/><path d="M12 11.5L7.5 15"/><path d="M12 11.5l4.5 3.5"/><path d="M8 17h8"/>' );
		}

		/**
		 * Иконка кибербезопасности.
		 *
		 * @return string
		 */
		private static function build_shield_icon() {
			return self::wrap_svg( '<path d="M12 3l6 2.5v5.8c0 4-2.5 7.5-6 9.2-3.5-1.7-6-5.2-6-9.2V5.5L12 3z"/><rect x="9" y="10.2" width="6" height="4.8" rx="1"/><path d="M10.2 10.2V9a1.8 1.8 0 1 1 3.6 0v1.2"/>' );
		}

		/**
		 * Иконка компьютеров.
		 *
		 * @return string
		 */
		private static function build_monitor_icon() {
			return self::wrap_svg( '<rect x="4" y="5" width="16" height="11" rx="2"/><path d="M10 19h4"/><path d="M12 16v3"/><path d="M7 8h10"/>' );
		}

		/**
		 * Иконка лайфхаков.
		 *
		 * @return string
		 */
		private static function build_spark_icon() {
			return self::wrap_svg( '<path d="M12 4l1.3 3.7L17 9l-3.7 1.3L12 14l-1.3-3.7L7 9l3.7-1.3L12 4z"/><path d="M18.5 4.5l.6 1.8 1.9.6-1.9.6-.6 1.8-.6-1.8-1.8-.6 1.8-.6.6-1.8z"/><path d="M5.5 14.5l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7.7-2z"/>' );
		}

		/**
		 * Иконка мобильных устройств.
		 *
		 * @return string
		 */
		private static function build_phone_icon() {
			return self::wrap_svg( '<rect x="7" y="3.5" width="10" height="17" rx="2.2"/><path d="M10.5 6.2h3"/><circle cx="12" cy="17.5" r=".8"/>' );
		}

		/**
		 * Иконка науки и космоса.
		 *
		 * @return string
		 */
		private static function build_rocket_icon() {
			return self::wrap_svg( '<path d="M13.5 4.5c3.4.8 5.2 3.6 6 6.8-2.2.8-4.3 1.9-5.9 3.6-1.6 1.6-2.7 3.7-3.5 5.8-3.2-.8-6-2.6-6.9-6 .8-1.5 2-3.2 3.7-4.8 1.6-1.6 3.2-2.8 4.6-3.6z"/><circle cx="14.8" cy="9.2" r="1.3"/><path d="M8.2 15.8L5 19"/><path d="M9.1 12.1l-4.4.6"/>' );
		}

		/**
		 * Иконка программ.
		 *
		 * @return string
		 */
		private static function build_window_icon() {
			return self::wrap_svg( '<rect x="4" y="5" width="16" height="14" rx="2"/><path d="M4 9h16"/><path d="M8 13h3"/><path d="M13 13h3"/><path d="M8 16h3"/><path d="M13 16h3"/><circle cx="7" cy="7" r=".6"/><circle cx="9.5" cy="7" r=".6"/>' );
		}

		/**
		 * Иконка технологий.
		 *
		 * @return string
		 */
		private static function build_chip_icon() {
			return self::wrap_svg( '<rect x="7" y="7" width="10" height="10" rx="2"/><path d="M10 10h4v4h-4z"/><path d="M9 3v2"/><path d="M12 3v2"/><path d="M15 3v2"/><path d="M9 19v2"/><path d="M12 19v2"/><path d="M15 19v2"/><path d="M3 9h2"/><path d="M3 12h2"/><path d="M3 15h2"/><path d="M19 9h2"/><path d="M19 12h2"/><path d="M19 15h2"/>' );
		}

		/**
		 * Иконка фактов.
		 *
		 * @return string
		 */
		private static function build_info_icon() {
			return self::wrap_svg( '<circle cx="12" cy="12" r="8"/><path d="M12 11.3v4.2"/><circle cx="12" cy="8.2" r=".8" fill="currentColor" stroke="none"/>' );
		}

		/**
		 * Иконка для рубрик, которые появятся позже и ещё не получили отдельный символ.
		 *
		 * @return string
		 */
		private static function build_folder_icon() {
			return self::wrap_svg( '<path d="M3.5 7.5A2.5 2.5 0 0 1 6 5h4l2 2h6A2.5 2.5 0 0 1 20.5 9.5v7A2.5 2.5 0 0 1 18 19H6a2.5 2.5 0 0 1-2.5-2.5z"/><path d="M3.5 10h17"/>' );
		}
	}
}

// Регистрируем модуль сразу,
// потому что на этапе подключения требуется только навесить WordPress-хуки.
Ioblog_Category_Icons::register();
