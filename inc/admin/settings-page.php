<?php
/**
 * Административная страница самостоятельной темы IO Blog.
 *
 * @package IoblogEditorial
 */

/**
 * Регистрирует option темы и страницу в разделе «Внешний вид».
 *
 * @return void
 */
function ioblog_register_theme_settings() {
	if ( false === get_option( 'ioblog_settings', false ) ) {
		add_option( 'ioblog_settings', ioblog_settings_defaults(), '', false );
	}

	register_setting(
		'ioblog_settings_group',
		'ioblog_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'ioblog_sanitize_settings',
			'default'           => ioblog_settings_defaults(),
		)
	);
}
add_action( 'admin_init', 'ioblog_register_theme_settings' );

/**
 * Добавляет единственный пункт управления темой в штатный раздел WordPress.
 *
 * @return void
 */
function ioblog_register_settings_page() {
	$icon = 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#a7aaad" d="M4 4h3v16H4zm10.5 0a8 8 0 1 1 0 16 8 8 0 0 1 0-16zm0 3a5 5 0 1 0 0 10 5 5 0 0 0 0-10z"/></svg>' );
	add_menu_page( __( 'IO Blog settings', 'ioblog-editorial' ), 'IO Blog', 'manage_options', 'ioblog-settings', 'ioblog_render_settings_page', $icon, 3 );
}
add_action( 'admin_menu', 'ioblog_register_settings_page' );

/**
 * Подключает стили только на собственной странице, не засоряя остальную админку.
 *
 * @param string $hook Суффикс текущего экрана.
 *
 * @return void
 */
function ioblog_enqueue_admin_assets( $hook ) {
	if ( 'toplevel_page_ioblog-settings' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'ioblog-admin', get_theme_file_uri( 'assets/css/admin.css' ), array(), ioblog_asset_version( 'assets/css/admin.css' ) );
	wp_enqueue_script( 'ioblog-admin', get_theme_file_uri( 'assets/js/admin.js' ), array(), ioblog_asset_version( 'assets/js/admin.js' ), true );
}
add_action( 'admin_enqueue_scripts', 'ioblog_enqueue_admin_assets' );

/**
 * Выводит переключатель в едином стиле страницы настроек.
 *
 * @param string $name  Ключ настройки.
 * @param string $label Подпись.
 * @param array  $settings Текущие настройки.
 *
 * @return void
 */
function ioblog_admin_checkbox( $name, $label, $settings ) {
	?>
	<label class="io-admin-toggle">
		<input type="checkbox" name="ioblog_settings[<?php echo esc_attr( $name ); ?>]" value="1" <?php checked( ! empty( $settings[ $name ] ) ); ?>>
		<span aria-hidden="true"></span><?php echo esc_html( $label ); ?>
	</label>
	<?php
}

/** Выводит ограниченный список опубликованных материалов для настроек главной. */
function ioblog_admin_content_options( $post_type, $selected ) {
	$items = get_posts( array( 'post_type' => $post_type, 'post_status' => 'publish', 'numberposts' => 100, 'orderby' => 'date', 'order' => 'DESC' ) );
	$selected_ids = array_map( 'absint', (array) $selected );
	// Ранее выбранная старая запись остаётся в списке, даже если вышла за предел последних ста материалов.
	foreach ( array_diff( $selected_ids, wp_list_pluck( $items, 'ID' ) ) as $post_id ) {
		$item = get_post( $post_id );
		if ( $item && $post_type === $item->post_type && 'publish' === $item->post_status ) {
			$items[] = $item;
		}
	}
	echo '<option value="0">' . esc_html( 'page' === $post_type ? __( 'Do not show', 'ioblog-editorial' ) : __( 'Automatic selection', 'ioblog-editorial' ) ) . '</option>';
	foreach ( $items as $item ) {
		printf( '<option value="%d" %s>%s</option>', $item->ID, selected( in_array( $item->ID, $selected_ids, true ), true, false ), esc_html( get_the_title( $item ) ) );
	}
}

/**
 * Выводит сообщения сохранения без системного класса notice.
 *
 * WordPress автоматически перемещает стандартные notice после первого H1,
 * поэтому собственный класс сохраняет уведомление в ожидаемом месте страницы.
 *
 * @return void
 */
function ioblog_render_admin_messages() {
	$messages = get_settings_errors();
	if ( empty( $messages ) ) {
		return;
	}
	?>
	<div class="io-admin-messages" role="status" aria-live="polite">
		<?php foreach ( $messages as $message ) { ?>
			<div class="io-admin-message io-admin-message--<?php echo esc_attr( $message['type'] ); ?>">
				<span aria-hidden="true"></span><?php echo wp_kses_post( $message['message'] ); ?>
			</div>
		<?php } ?>
	</div>
	<?php
}

/**
 * Рисует полноценную панель управления модулями темы.
 *
 * @return void
 */
function ioblog_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings = ioblog_get_settings();
	?>
	<div class="wrap io-admin">
		<header class="io-admin-hero">
			<div><span>IO BLOG EDITORIAL <b class="io-admin-version"><?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?> · <?php echo ioblog_has_pro() ? 'PRO' : 'FREE'; ?></b></span><h1><?php esc_html_e( 'Site management', 'ioblog-editorial' ); ?></h1><p><?php esc_html_e( 'Design, reading, and publishing settings for your blog.', 'ioblog-editorial' ); ?></p></div>
			<div class="io-admin-hero__status"><i aria-hidden="true"></i><strong><?php esc_html_e( 'Theme is active', 'ioblog-editorial' ); ?></strong><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'View site', 'ioblog-editorial' ); ?> <span aria-hidden="true">&#8599;</span></a></div>
		</header>

		<nav class="io-admin-links">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ioblog-settings-transfer' ) ); ?>"><?php esc_html_e( 'Export and import', 'ioblog-editorial' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=ioblog-design' ) ); ?>"><?php esc_html_e( 'Design studio', 'ioblog-editorial' ); ?></a>
			<?php if ( class_exists( 'Ioblog_Community_Settings' ) ) { ?><a href="<?php echo esc_url( admin_url( 'admin.php?page=ioblog-community' ) ); ?>"><?php esc_html_e( 'Reader accounts', 'ioblog-editorial' ); ?></a><?php } ?>
			<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Appearance', 'ioblog-editorial' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menus', 'ioblog-editorial' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=category' ) ); ?>"><?php esc_html_e( 'Categories', 'ioblog-editorial' ); ?></a>
			<a href="<?php echo esc_url( admin_url( 'edit-comments.php' ) ); ?>"><?php esc_html_e( 'Comments', 'ioblog-editorial' ); ?></a>
		</nav>

		<?php ioblog_render_admin_messages(); ?>
		<form action="options.php" method="post" class="io-admin-form">
			<?php settings_fields( 'ioblog_settings_group' ); ?>
			<nav class="io-admin-tabs" aria-label="<?php esc_attr_e( 'Site management', 'ioblog-editorial' ); ?>" hidden></nav>
			<div class="io-admin-panels">

			<section class="io-admin-card" id="language">
				<div class="io-admin-card__heading"><span>00</span><div><h2><?php esc_html_e( 'Site language', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'The selected language is applied to WordPress, the public site, and your administrator profile.', 'ioblog-editorial' ); ?></p></div></div>
				<div class="io-admin-grid">
					<label><strong><?php esc_html_e( 'WordPress language', 'ioblog-editorial' ); ?></strong>
						<select name="ioblog_settings[site_language]">
							<?php foreach ( ioblog_site_language_choices() as $locale => $language_name ) { ?>
								<option value="<?php echo esc_attr( $locale ); ?>" <?php selected( $settings['site_language'], $locale ); ?>><?php echo esc_html( $language_name ); ?></option>
							<?php } ?>
						</select>
					</label>
				</div>
				<p class="description"><?php esc_html_e( 'If the selected WordPress language pack is missing, it will be installed through the standard WordPress translation service when settings are saved.', 'ioblog-editorial' ); ?></p>
			</section>

			<section class="io-admin-card" id="homepage">
				<div class="io-admin-card__heading"><span>01</span><div><h2><?php esc_html_e( 'Homepage', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'Choose the editorial sections and the content featured on the front page.', 'ioblog-editorial' ); ?></p></div></div>
				<div class="io-admin-toggles">
					<?php ioblog_admin_checkbox( 'home_show_hero', __( 'Hero section', 'ioblog-editorial' ), $settings ); ?>
					<?php ioblog_admin_checkbox( 'home_show_categories', __( 'Category shortcuts', 'ioblog-editorial' ), $settings ); ?>
					<?php ioblog_admin_checkbox( 'home_show_editorial', __( 'Editor’s choice', 'ioblog-editorial' ), $settings ); ?>
				</div>
				<div class="io-admin-grid io-admin-grid--three io-admin-fields">
					<label><strong><?php esc_html_e( 'Featured article', 'ioblog-editorial' ); ?></strong><select name="ioblog_settings[home_featured_post]"><?php ioblog_admin_content_options( 'post', $settings['home_featured_post'] ); ?></select></label>
					<label><strong><?php esc_html_e( 'About page', 'ioblog-editorial' ); ?></strong><select name="ioblog_settings[home_about_page]"><?php ioblog_admin_content_options( 'page', $settings['home_about_page'] ); ?></select></label>
					<label><strong><?php esc_html_e( 'Posts per page', 'ioblog-editorial' ); ?></strong><input type="number" min="6" max="24" name="ioblog_settings[home_posts_per_page]" value="<?php echo esc_attr( $settings['home_posts_per_page'] ); ?>"></label>
					<label><strong><?php esc_html_e( 'Editor’s choice', 'ioblog-editorial' ); ?></strong><select multiple size="5" name="ioblog_settings[home_editorial_posts][]"><?php ioblog_admin_content_options( 'post', explode( ',', $settings['home_editorial_posts'] ) ); ?></select><small><?php esc_html_e( 'Select up to five articles with Ctrl or Command. Leave empty for automatic selection.', 'ioblog-editorial' ); ?></small></label>
					<label><strong><?php esc_html_e( 'Section order', 'ioblog-editorial' ); ?></strong><select name="ioblog_settings[home_section_order]"><?php foreach ( array( 'categories,editorial,latest' => __( 'Categories → choice → latest', 'ioblog-editorial' ), 'editorial,categories,latest' => __( 'Choice → categories → latest', 'ioblog-editorial' ), 'editorial,latest,categories' => __( 'Choice → latest → categories', 'ioblog-editorial' ) ) as $value => $label ) { ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $settings['home_section_order'], $value ); ?>><?php echo esc_html( $label ); ?></option><?php } ?></select></label>
				</div>
			</section>

			<section class="io-admin-card" id="reading">
				<div class="io-admin-card__heading"><span>02</span><div><h2><?php esc_html_e( 'Typography and article layout', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'Reading presets keep font size, rhythm, and article width balanced.', 'ioblog-editorial' ); ?></p></div></div>
				<div class="io-admin-grid io-admin-grid--three io-admin-fields">
					<label><strong><?php esc_html_e( 'Typography preset', 'ioblog-editorial' ); ?></strong><select name="ioblog_settings[typography_preset]"><option value="editorial" <?php selected( $settings['typography_preset'], 'editorial' ); ?>><?php esc_html_e( 'Editorial', 'ioblog-editorial' ); ?></option><option value="compact" <?php selected( $settings['typography_preset'], 'compact' ); ?>><?php esc_html_e( 'Compact', 'ioblog-editorial' ); ?></option><option value="comfortable" <?php selected( $settings['typography_preset'], 'comfortable' ); ?>><?php esc_html_e( 'Comfortable', 'ioblog-editorial' ); ?></option></select></label>
					<label><strong><?php esc_html_e( 'Article layout', 'ioblog-editorial' ); ?></strong><select name="ioblog_settings[article_layout]"><option value="sidebar" <?php selected( $settings['article_layout'], 'sidebar' ); ?>><?php esc_html_e( 'Article with sidebar', 'ioblog-editorial' ); ?></option><option value="focused" <?php selected( $settings['article_layout'], 'focused' ); ?>><?php esc_html_e( 'Focused reading', 'ioblog-editorial' ); ?></option><option value="wide" <?php selected( $settings['article_layout'], 'wide' ); ?>><?php esc_html_e( 'Wide article', 'ioblog-editorial' ); ?></option></select></label>
					<label><strong><?php esc_html_e( 'Body text size, px', 'ioblog-editorial' ); ?></strong><input type="number" min="16" max="22" name="ioblog_settings[reading_font_size]" value="<?php echo esc_attr( $settings['reading_font_size'] ); ?>"></label>
					<label><strong><?php esc_html_e( 'Line height', 'ioblog-editorial' ); ?></strong><input type="number" min="1.45" max="2" step="0.01" name="ioblog_settings[reading_line_height]" value="<?php echo esc_attr( $settings['reading_line_height'] ); ?>"></label>
					<label><strong><?php esc_html_e( 'Reading width, px', 'ioblog-editorial' ); ?></strong><input type="number" min="680" max="920" step="10" name="ioblog_settings[reading_width]" value="<?php echo esc_attr( $settings['reading_width'] ); ?>"></label>
				</div>
			</section>

			<section class="io-admin-card" id="interface">
				<div class="io-admin-card__heading"><span>03</span><div><h2><?php esc_html_e( 'Interface and content', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'Core settings for live search and user-generated content.', 'ioblog-editorial' ); ?></p></div></div>
				<div class="io-admin-grid io-admin-grid--three">
					<label><strong><?php esc_html_e( 'Minimum search characters', 'ioblog-editorial' ); ?></strong><input type="number" min="2" max="6" name="ioblog_settings[search_min_chars]" value="<?php echo esc_attr( $settings['search_min_chars'] ); ?>"></label>
					<label><strong><?php esc_html_e( 'Results in the search window', 'ioblog-editorial' ); ?></strong><input type="number" min="3" max="20" name="ioblog_settings[search_limit]" value="<?php echo esc_attr( $settings['search_limit'] ); ?>"></label>
					<label><strong><?php esc_html_e( 'Image limit, MB', 'ioblog-editorial' ); ?></strong><input type="number" min="1" max="10" name="ioblog_settings[comment_image_max_mb]" value="<?php echo esc_attr( $settings['comment_image_max_mb'] ); ?>"></label>
				</div>
				<div class="io-admin-toggles">
					<?php ioblog_admin_checkbox( 'comments_images', __( 'Images in comments', 'ioblog-editorial' ), $settings ); ?>
					<?php ioblog_admin_checkbox( 'comments_emojis', __( 'Emoji panel', 'ioblog-editorial' ), $settings ); ?>
					<?php ioblog_admin_checkbox( 'claps_enabled', __( 'Article claps', 'ioblog-editorial' ), $settings ); ?>
					<?php ioblog_admin_checkbox( 'library_enabled', __( 'Personal reading library', 'ioblog-editorial' ), $settings ); ?>
					<?php ioblog_admin_checkbox( 'svg_uploads', __( 'Safe SVG uploads', 'ioblog-editorial' ), $settings ); ?>
				</div>
				<p><?php esc_html_e( 'Claps: up to 10 per article from each browser, with undo. Disabling claps preserves existing reactions.', 'ioblog-editorial' ); ?></p>
				<p><?php esc_html_e( 'Readers can search bookmarks, filter their reading history, and export or import a private backup. Data stays in the browser without an account.', 'ioblog-editorial' ); ?></p>
			</section>

			<section class="io-admin-card" id="captcha">
				<div class="io-admin-card__heading"><span>04</span><div><h2><?php esc_html_e( 'Form protection', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'Built-in protection requires no keys. An external provider is enabled only after both keys are filled in.', 'ioblog-editorial' ); ?></p></div></div>
				<div class="io-admin-grid io-admin-grid--three">
					<label><strong><?php esc_html_e( 'CAPTCHA provider', 'ioblog-editorial' ); ?></strong><select name="ioblog_settings[captcha_provider]"><option value="builtin" <?php selected( $settings['captcha_provider'], 'builtin' ); ?>><?php esc_html_e( 'Built-in protection', 'ioblog-editorial' ); ?></option><option value="yandex" <?php selected( $settings['captcha_provider'], 'yandex' ); ?>>Yandex SmartCaptcha</option><option value="google" <?php selected( $settings['captcha_provider'], 'google' ); ?>>Google reCAPTCHA v2</option></select></label>
					<label><strong><?php esc_html_e( 'Site key', 'ioblog-editorial' ); ?></strong><input type="text" name="ioblog_settings[captcha_site_key]" value="<?php echo esc_attr( $settings['captcha_site_key'] ); ?>" autocomplete="off"></label>
					<label><strong><?php esc_html_e( 'Secret key', 'ioblog-editorial' ); ?></strong><input type="password" name="ioblog_settings[captcha_secret_key]" value="" placeholder="<?php echo esc_attr( $settings['captcha_secret_key'] ? __( 'Key saved', 'ioblog-editorial' ) : __( 'Enter key', 'ioblog-editorial' ) ); ?>" autocomplete="new-password"></label>
				</div>
			</section>

			<section class="io-admin-card" id="social">
				<div class="io-admin-card__heading"><span>05</span><div><h2><?php esc_html_e( 'Footer social networks', 'ioblog-editorial' ); ?></h2></div></div>
				<div class="io-admin-grid">
					<label><strong>Telegram</strong><input type="url" name="ioblog_settings[social_telegram_url]" value="<?php echo esc_attr( $settings['social_telegram_url'] ); ?>" placeholder="https://t.me/ioblogru"></label>
					<label><strong>MAX</strong><input type="url" name="ioblog_settings[social_max_url]" value="<?php echo esc_attr( $settings['social_max_url'] ); ?>" placeholder="https://max.ru/..."></label>
					<label><strong><?php esc_html_e( 'VK', 'ioblog-editorial' ); ?></strong><input type="url" name="ioblog_settings[social_vk_url]" value="<?php echo esc_attr( $settings['social_vk_url'] ); ?>" placeholder="https://vk.com/..."></label>
					<label><strong><?php esc_html_e( 'Odnoklassniki', 'ioblog-editorial' ); ?></strong><input type="url" name="ioblog_settings[social_ok_url]" value="<?php echo esc_attr( $settings['social_ok_url'] ); ?>" placeholder="https://ok.ru/..."></label>
				</div>
			</section>

			<?php do_action( 'ioblog_admin_settings_sections', $settings ); ?>
			<section class="io-admin-card" id="edition">
				<div class="io-admin-card__heading"><span><?php echo ioblog_has_pro() ? 'PRO' : 'FREE'; ?></span><div><h2><?php esc_html_e( 'Your edition', 'ioblog-editorial' ); ?></h2><p><?php echo esc_html( ioblog_has_pro() ? __( 'Pro publishing tools are active.', 'ioblog-editorial' ) : __( 'The free theme is ready for your blog. Install the Pro extension to add publishing tools.', 'ioblog-editorial' ) ); ?></p></div></div>
				<p><?php esc_html_e( 'Free: layouts, typography, dark mode, search, automatic covers, Gutenberg patterns, comments, form protection, claps, and a reading library.', 'ioblog-editorial' ); ?></p>
				<p><?php esc_html_e( 'Pro: cover editor, link cards, advertising, short links, integrations, article series, social covers, and reader interests.', 'ioblog-editorial' ); ?></p>
				<p><?php esc_html_e( 'Free also includes the design studio, reader mode, print layout and full configuration backup.', 'ioblog-editorial' ); ?></p>
				<p><?php esc_html_e( 'Pro 1.3 adds reader accounts, library sync, consenting private messages, notifications, social sign-in and conditional design/advertising.', 'ioblog-editorial' ); ?></p>
			</section>
			</div>
			<div class="io-admin-savebar">
				<p class="io-admin-save-status" role="status" aria-live="polite" data-clean="<?php esc_attr_e( 'All changes saved', 'ioblog-editorial' ); ?>" data-dirty="<?php esc_attr_e( 'You have unsaved changes', 'ioblog-editorial' ); ?>"></p>
				<?php submit_button( __( 'Save settings', 'ioblog-editorial' ), 'primary io-admin-submit', 'submit', false ); ?>
			</div>
		</form>
	</div>
	<?php
}
