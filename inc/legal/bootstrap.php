<?php
/** Сборка независимых настроек, представления и административных маршрутов приватности. */
foreach ( array( 'settings', 'view', 'admin' ) as $module ) { require_once __DIR__ . '/' . $module . '.php'; }
add_filter( 'ioblog_settings_defaults', static function ( $settings ) { $settings['legal_config'] = array(); return $settings; } );
add_filter( 'ioblog_sanitize_settings', static function ( $settings, $input, $current ) { $settings['legal_config'] = $current['legal_config'] ?? array(); return $settings; }, 30, 3 );
