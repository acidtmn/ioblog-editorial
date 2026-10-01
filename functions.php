<?php
/**
 * Точка входа самостоятельной темы IO Blog Editorial.
 *
 * Каждый подключаемый модуль отвечает за один слой темы, поэтому этот файл
 * только собирает конфигурацию и не содержит разметку или бизнес-логику.
 *
 * @package IoblogEditorial
 */

$ioblog_modules = array(
	'setup',
	'settings',
	'extensions',
	'services/class-homepage-layout',
	'migration',
	'assets',
	'typography',
	'customizer',
	'category-icons',
	'cards',
	'toc',
	'search',
	'captcha',
	'anti-spam',
	'comments',
	'social',
	'svg',
	'services/class-css-cover-generator',
	'css-covers',
	'seo',
	'admin/site-language',
	'admin/settings-page',
);

foreach ( $ioblog_modules as $ioblog_module ) {
	require_once get_theme_file_path( 'inc/' . $ioblog_module . '.php' );
}
