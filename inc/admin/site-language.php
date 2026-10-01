<?php
/**
 * Управление языком WordPress из панели темы через штатный API локалей.
 *
 * @package IoblogEditorial
 */

/**
 * Возвращает локали, которые можно выбрать без произвольного пользовательского ввода.
 *
 * Русский включён в список всегда, потому что тема поставляет готовый перевод. Остальные
 * локали добавляются только после установки соответствующего пакета ядра WordPress.
 *
 * @return array<string, string>
 */
function ioblog_site_language_choices() {
	$choices = array(
		'en_US' => 'English (United States)',
		'ru_RU' => 'Русский',
	);

	foreach ( get_available_languages() as $locale ) {
		if ( ! isset( $choices[ $locale ] ) ) {
			$choices[ $locale ] = $locale;
		}
	}

	return $choices;
}

/**
 * Оставляет только локаль из закрытого списка панели темы.
 *
 * @param string $locale Значение из формы настроек.
 *
 * @return string
 */
function ioblog_sanitize_site_language( $locale ) {
	$locale = is_string( $locale ) ? sanitize_locale_name( $locale ) : '';
	return array_key_exists( $locale, ioblog_site_language_choices() ) ? $locale : ( get_option( 'WPLANG', '' ) ?: 'en_US' );
}

/**
 * Проверяет доступность перевода до сохранения: при сбое загрузки сохраняется действующий язык.
 *
 * @param mixed $locale Выбранная локаль.
 * @return string
 */
function ioblog_validate_site_language( $locale ) {
	$locale = ioblog_sanitize_site_language( $locale );
	if ( 'en_US' === $locale || in_array( $locale, get_available_languages(), true ) ) {
		return $locale;
	}

	// Установка выполняется только при изменении настроек, а не при обычном просмотре страницы.
	require_once ABSPATH . 'wp-admin/includes/translation-install.php';
	if ( wp_download_language_pack( $locale ) ) {
		return $locale;
	}

	add_settings_error( 'ioblog_settings', 'ioblog_language_unavailable', __( 'The language pack could not be installed. The current language has been kept.', 'ioblog-editorial' ) );
	return get_option( 'WPLANG', '' ) ?: 'en_US';
}

/**
 * Устанавливает языковой пакет только после явного сохранения администратором.
 *
 * Автоматическая смена языка при активации темы нарушила бы ожидания владельца сайта.
 * Здесь действие привязано к осознанному выбору в панели и использует стандартный
 * установщик переводов WordPress, поэтому обновления языка продолжит обслуживать ядро.
 *
 * @param array $new_value Новые настройки темы.
 * @param array $old_value Предыдущие настройки темы.
 *
 * @return array
 */
function ioblog_apply_site_language( $new_value, $old_value ) {
	unset( $old_value );
	$locale = ioblog_sanitize_site_language( $new_value['site_language'] ?? null );
	// Сохранение рекламы или поиска не должно менять язык профиля и откатывать настройки ядра.
	if ( $locale === ( get_option( 'WPLANG', '' ) ?: 'en_US' ) ) {
		return $new_value;
	}

	update_option( 'WPLANG', 'en_US' === $locale ? '' : $locale );

	// Профиль текущего администратора синхронизируется с явным выбором, иначе личная
	// локаль могла бы скрыть результат до следующего ручного изменения профиля.
	if ( get_current_user_id() ) {
		update_user_meta( get_current_user_id(), 'locale', 'en_US' === $locale ? '' : $locale );
	}
	return $new_value;
}
// pre_update вызывается даже при совпадении массива настроек: язык ядра мог измениться отдельно.
add_filter( 'pre_update_option_ioblog_settings', 'ioblog_apply_site_language', 10, 2 );
