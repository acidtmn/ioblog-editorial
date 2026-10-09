<?php
/** Парные цветовые пресеты не затрагивают типографику и расположение блоков. */
final class Ioblog_Design_Presets {
	public static function labels() {
		return array(
			'emerald' => __( 'Emerald', 'ioblog-editorial' ),
			'ocean' => __( 'Ocean', 'ioblog-editorial' ),
			'forest' => __( 'Forest', 'ioblog-editorial' ),
			'amber' => __( 'Amber', 'ioblog-editorial' ),
			'terracotta' => __( 'Terracotta', 'ioblog-editorial' ),
			'graphite' => __( 'Graphite', 'ioblog-editorial' ),
		);
	}
	public static function colors() {
		$palettes = array(
			'emerald' => array(
				'light' => array( 'bg' => '#f4f8fc', 'surface' => '#ffffff', 'surface_soft' => '#edf4fa', 'text' => '#101a2d', 'muted' => '#647087', 'border' => '#dce5ee', 'accent' => '#00866f', 'accent_hover' => '#006f5d', 'accent_soft' => '#ddf5ee', 'danger' => '#d52c56', 'button_text' => '#ffffff', 'focus' => '#006f5d' ),
				'dark' => array( 'bg' => '#081120', 'surface' => '#101b2d', 'surface_soft' => '#17243a', 'text' => '#f4f7fb', 'muted' => '#aab6c8', 'border' => '#2a3951', 'accent' => '#38c7ad', 'accent_hover' => '#68ddc8', 'accent_soft' => '#153d3b', 'danger' => '#ff718a', 'button_text' => '#081120', 'focus' => '#68ddc8' ),
			),
			'ocean' => array(
				'light' => array( 'bg' => '#f2f7fc', 'surface' => '#ffffff', 'surface_soft' => '#e8f1fa', 'text' => '#12253a', 'muted' => '#53677d', 'border' => '#cfdfed', 'accent' => '#1764ad', 'accent_hover' => '#104f8d', 'accent_soft' => '#dfedfb', 'danger' => '#c1284d', 'button_text' => '#ffffff', 'focus' => '#104f8d' ),
				'dark' => array( 'bg' => '#0a1524', 'surface' => '#112238', 'surface_soft' => '#192e47', 'text' => '#edf5ff', 'muted' => '#adc2db', 'border' => '#304964', 'accent' => '#72baff', 'accent_hover' => '#a0d2ff', 'accent_soft' => '#173c5e', 'danger' => '#ff8da3', 'button_text' => '#0a1524', 'focus' => '#a0d2ff' ),
			),
			'forest' => array(
				'light' => array( 'bg' => '#f4f7f0', 'surface' => '#ffffff', 'surface_soft' => '#eaf0e3', 'text' => '#1c2b1b', 'muted' => '#58694f', 'border' => '#d5dfce', 'accent' => '#386b30', 'accent_hover' => '#295224', 'accent_soft' => '#e1efda', 'danger' => '#bb3045', 'button_text' => '#ffffff', 'focus' => '#295224' ),
				'dark' => array( 'bg' => '#111a12', 'surface' => '#1a281b', 'surface_soft' => '#243526', 'text' => '#eff7ea', 'muted' => '#b5c9aa', 'border' => '#3b503a', 'accent' => '#9bcc7d', 'accent_hover' => '#b9e29f', 'accent_soft' => '#2b4524', 'danger' => '#ff96a3', 'button_text' => '#111a12', 'focus' => '#b9e29f' ),
			),
			'amber' => array(
				'light' => array( 'bg' => '#fcf8ee', 'surface' => '#fffefa', 'surface_soft' => '#f6eedb', 'text' => '#352612', 'muted' => '#756044', 'border' => '#e7dac0', 'accent' => '#8a5600', 'accent_hover' => '#6c4100', 'accent_soft' => '#f9e9bf', 'danger' => '#bb2f3e', 'button_text' => '#ffffff', 'focus' => '#6c4100' ),
				'dark' => array( 'bg' => '#1c160d', 'surface' => '#2a2114', 'surface_soft' => '#382d1d', 'text' => '#fff6e4', 'muted' => '#d0bb94', 'border' => '#55442a', 'accent' => '#edbd60', 'accent_hover' => '#f5d38d', 'accent_soft' => '#493719', 'danger' => '#ff98a2', 'button_text' => '#1c160d', 'focus' => '#f5d38d' ),
			),
			'terracotta' => array(
				'light' => array( 'bg' => '#fcf5f1', 'surface' => '#fffdfb', 'surface_soft' => '#f6e9e1', 'text' => '#38231d', 'muted' => '#795e52', 'border' => '#e7d4c9', 'accent' => '#a3462d', 'accent_hover' => '#813420', 'accent_soft' => '#fae2d6', 'danger' => '#b92b4b', 'button_text' => '#ffffff', 'focus' => '#813420' ),
				'dark' => array( 'bg' => '#211512', 'surface' => '#30201b', 'surface_soft' => '#3f2c25', 'text' => '#fff1e9', 'muted' => '#d4b4a5', 'border' => '#5b4035', 'accent' => '#efa080', 'accent_hover' => '#fac2a8', 'accent_soft' => '#513126', 'danger' => '#ff96ad', 'button_text' => '#211512', 'focus' => '#fac2a8' ),
			),
			'graphite' => array(
				'light' => array( 'bg' => '#f5f6f7', 'surface' => '#ffffff', 'surface_soft' => '#eceef0', 'text' => '#23272d', 'muted' => '#626a75', 'border' => '#d9dde2', 'accent' => '#4e5b6d', 'accent_hover' => '#354151', 'accent_soft' => '#e6ebf1', 'danger' => '#c02c49', 'button_text' => '#ffffff', 'focus' => '#354151' ),
				'dark' => array( 'bg' => '#15171b', 'surface' => '#202329', 'surface_soft' => '#2c3037', 'text' => '#f3f5f8', 'muted' => '#bac1cb', 'border' => '#434a55', 'accent' => '#b8c7dd', 'accent_hover' => '#d6e1f1', 'accent_soft' => '#343e4e', 'danger' => '#ff92a4', 'button_text' => '#15171b', 'focus' => '#d6e1f1' ),
			),
		);
		// Ключи совпадают с контрактом формы и экспорта; отдельный идентификатор не сохраняется.
		$result = array();
		foreach ( $palettes as $id => $schemes ) {
			foreach ( $schemes as $scheme => $colors ) {
				foreach ( $colors as $key => $value ) { $result[ $id ][ $scheme . '_' . $key ] = $value; }
			}
		}
		return $result;
	}
	public static function matching( $config ) {
		foreach ( self::colors() as $id => $colors ) {
			if ( ! array_diff_assoc( $colors, $config ) ) { return $id; }
		}
		return 'custom';
	}
}
