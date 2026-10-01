<?php
/**
 * Стандартные настройки внешнего вида WordPress Customizer.
 *
 * @package IoblogEditorial
 */

/**
 * Регистрирует редактируемые тексты и ссылки, не зашивая их в шаблоны.
 *
 * @param WP_Customize_Manager $customizer Менеджер Customizer.
 *
 * @return void
 */
function ioblog_customize_register( $customizer ) {
	$customizer->add_section( 'ioblog_home', array( 'title' => __( 'IO Blog homepage', 'ioblog-editorial' ), 'priority' => 30 ) );
	$customizer->add_section( 'ioblog_footer', array( 'title' => __( 'IO Blog footer', 'ioblog-editorial' ), 'priority' => 31 ) );
	$customizer->add_section( 'ioblog_interface', array( 'title' => __( 'IO Blog interface', 'ioblog-editorial' ), 'priority' => 32 ) );

	$text_settings = array(
		'ioblog_hero_eyebrow'     => array( 'ioblog_home', __( 'Homepage eyebrow', 'ioblog-editorial' ), __( 'Independent technology journal', 'ioblog-editorial' ) ),
		'ioblog_hero_benefit_1'   => array( 'ioblog_home', __( 'First highlight', 'ioblog-editorial' ), __( 'Practical guides', 'ioblog-editorial' ) ),
		'ioblog_hero_benefit_2'   => array( 'ioblog_home', __( 'Second highlight', 'ioblog-editorial' ), __( 'Honest reviews', 'ioblog-editorial' ) ),
		'ioblog_hero_benefit_3'   => array( 'ioblog_home', __( 'Third highlight', 'ioblog-editorial' ), __( 'First-hand experience', 'ioblog-editorial' ) ),
		'ioblog_hero_title'       => array( 'ioblog_home', __( 'First title line', 'ioblog-editorial' ), __( 'Technology', 'ioblog-editorial' ) ),
		'ioblog_hero_accent'      => array( 'ioblog_home', __( 'Accent line', 'ioblog-editorial' ), __( 'without the noise', 'ioblog-editorial' ) ),
		'ioblog_hero_description' => array( 'ioblog_home', __( 'Description', 'ioblog-editorial' ), __( 'Windows, Docker, gadgets, cars, and practical ideas that actually work.', 'ioblog-editorial' ) ),
		'ioblog_footer_text'      => array( 'ioblog_footer', __( 'Site description', 'ioblog-editorial' ), __( 'An independent blog about technology, computers, software, and practical solutions without the noise.', 'ioblog-editorial' ) ),
		'ioblog_footer_copyright' => array( 'ioblog_footer', __( 'Copyright text', 'ioblog-editorial' ), __( '© 2026 IO Blog. All rights reserved.', 'ioblog-editorial' ) ),
		'ioblog_search_heading'   => array( 'ioblog_interface', __( 'Search heading', 'ioblog-editorial' ), __( 'What are you looking for?', 'ioblog-editorial' ) ),
	);

	foreach ( $text_settings as $setting_id => $setting ) {
		$customizer->add_setting( $setting_id, array( 'default' => $setting[2], 'sanitize_callback' => 'sanitize_text_field' ) );
		$customizer->add_control( $setting_id, array( 'section' => $setting[0], 'label' => $setting[1], 'type' => 'text' ) );
	}

	$customizer->add_setting( 'ioblog_color_scheme', array( 'default' => 'system', 'sanitize_callback' => 'ioblog_sanitize_color_scheme' ) );
	$customizer->add_control(
		'ioblog_color_scheme',
		array(
			'label'   => __( 'Default color scheme', 'ioblog-editorial' ),
			'section' => 'ioblog_interface',
			'type'    => 'select',
			'choices' => array( 'system' => __( 'Follow system', 'ioblog-editorial' ), 'light' => __( 'Light', 'ioblog-editorial' ), 'dark' => __( 'Dark', 'ioblog-editorial' ) ),
		)
	);
}
add_action( 'customize_register', 'ioblog_customize_register' );

/**
 * Разрешает только поддерживаемые значения цветовой схемы.
 *
 * @param string $value Введённое значение.
 *
 * @return string
 */
function ioblog_sanitize_color_scheme( $value ) {
	return in_array( $value, array( 'system', 'light', 'dark' ), true ) ? $value : 'system';
}
