<?php
/** Контракт оформления: допустимые значения общие для формы, предпросмотра и импорта. */
final class Ioblog_Design_Schema {
	public static function fields() {
		$fields = array();
		// Цвета хранятся отдельно для двух схем; зависимые компоненты наследуют эти токены.
		$palette = Ioblog_Design_Presets::colors()['emerald'];
		$labels = array( 'bg' => 'Page background', 'surface' => 'Card background', 'surface_soft' => 'Soft background', 'text' => 'Main text', 'muted' => 'Secondary text', 'border' => 'Borders', 'accent' => 'Accent color', 'accent_hover' => 'Accent on hover', 'accent_soft' => 'Soft accent', 'danger' => 'Errors', 'button_text' => 'Button text', 'focus' => 'Keyboard focus' );
		foreach ( $palette as $key => $value ) {
			list( $scheme, $token ) = explode( '_', $key, 2 );
			$fields[ $key ] = array( 'type' => 'color', 'default' => $value, 'label' => $labels[ $token ], 'group' => $scheme );
		}
		// Набор семейств закрытый: из формы нельзя внедрить CSS или загрузить сторонний шрифт.
		$fonts = array( 'dm' => 'DM Sans', 'serif' => 'Georgia', 'sans' => 'Verdana', 'mono' => 'Monospace', 'custom' => 'Uploaded font' );
		foreach ( array( 'body' => 'Body font', 'heading' => 'Heading font', 'menu' => 'Navigation font', 'button' => 'Button font', 'meta' => 'Metadata font', 'code' => 'Code font' ) as $key => $label ) {
			$fields[ 'font_' . $key ] = array( 'type' => 'select', 'default' => 'code' === $key ? 'mono' : 'dm', 'choices' => $fonts, 'label' => $label, 'group' => 'fonts' );
		}
		foreach ( array(
			'body_size' => array( 16, 14, 22, 'Interface text size' ), 'heading_weight' => array( 750, 400, 800, 'Heading weight' ),
			'heading_scale' => array( 100, 80, 125, 'Heading scale, %' ), 'paragraph_gap' => array( 24, 12, 48, 'Paragraph spacing' ),
			'mobile_reading_size' => array( 17, 16, 22, 'Mobile article text size' ), 'letter_spacing' => array( 0, -1, 2, 'Letter spacing' ),
			'container_width' => array( 1240, 960, 1600, 'Site width' ), 'sidebar_width' => array( 280, 220, 380, 'Sidebar width' ),
			'radius' => array( 16, 0, 32, 'Corner radius' ), 'section_gap' => array( 64, 24, 120, 'Section spacing' ),
			'card_gap' => array( 24, 12, 48, 'Card spacing' ), 'footer_columns' => array( 3, 1, 4, 'Footer columns' ),
		) as $key => $rule ) {
			$fields[ $key ] = array( 'type' => 'number', 'default' => $rule[0], 'min' => $rule[1], 'max' => $rule[2], 'label' => $rule[3], 'group' => in_array( $key, array( 'body_size', 'heading_weight', 'heading_scale', 'paragraph_gap', 'mobile_reading_size', 'letter_spacing' ), true ) ? 'fonts' : 'geometry' );
		}
		foreach ( array( 'header_sticky' => 'Sticky header', 'header_search' => 'Header search', 'header_theme_switch' => 'Color scheme switch', 'header_tagline' => 'Logo tagline', 'article_cover' => 'Article cover', 'article_author' => 'Author card', 'article_navigation' => 'Adjacent articles', 'article_related' => 'Related articles', 'article_toc' => 'Table of contents', 'sidebar_recent' => 'Sidebar articles', 'card_excerpt' => 'Card excerpts', 'card_meta' => 'Card metadata', 'footer_social' => 'Footer social links', 'reader_mode' => 'Reader mode', 'print_button' => 'Print article' ) as $key => $label ) {
			$fields[ $key ] = array( 'type' => 'checkbox', 'default' => 1, 'label' => $label, 'group' => 'regions' );
		}
		$fields['card_ratio'] = array( 'type' => 'select', 'default' => 'wide', 'choices' => array( 'wide' => '16:9', 'classic' => '4:3', 'square' => '1:1' ), 'label' => 'Card image ratio', 'group' => 'geometry' );
		$fields['header_navigation'] = array( 'type' => 'checkbox', 'default' => 1, 'label' => 'Header navigation', 'group' => 'regions' );
		$fields['mobile_navigation'] = array( 'type' => 'checkbox', 'default' => 1, 'label' => 'Mobile navigation', 'group' => 'regions' );
		$fields['shadow'] = array( 'type' => 'select', 'default' => 'soft', 'choices' => array( 'none' => 'No shadow', 'soft' => 'Soft shadow', 'strong' => 'Strong shadow' ), 'label' => 'Shadows', 'group' => 'geometry' );
		$fields['font_attachment'] = array( 'type' => 'number', 'default' => 0, 'min' => 0, 'max' => 2147483647, 'label' => 'Uploaded font', 'group' => 'fonts' );
		foreach ( array( 'h1' => 64, 'h2' => 34, 'h3' => 28, 'h4' => 24, 'h5' => 21, 'h6' => 18 ) as $level => $size ) { $fields[ 'size_' . $level ] = array( 'type' => 'number', 'default' => $size, 'min' => 16, 'max' => 96, 'label' => strtoupper( $level ) . ', px', 'group' => 'fonts' ); }
		foreach ( array( 'menu' => 650, 'button' => 750, 'meta' => 500, 'body' => 400 ) as $role => $weight ) { $fields[ 'weight_' . $role ] = array( 'type' => 'number', 'default' => $weight, 'min' => 400, 'max' => 800, 'label' => 'Weight: ' . $role, 'group' => 'fonts' ); }
		return $fields;
	}
	public static function defaults() { return array_map( static fn( $field ) => $field['default'], self::fields() ); }
	public static function validate( $input ) {
		// Импорт также использует строгий контракт: неизвестное поле не отбрасывается незаметно.
		if ( ! is_array( $input ) || array_diff( array_keys( $input ), array_keys( self::fields() ) ) ) { throw new InvalidArgumentException( 'design' ); }
		$output = self::defaults();
		foreach ( $input as $key => $value ) {
			$rule = self::fields()[ $key ];
			if ( 'color' === $rule['type'] ) {
				if ( ! is_string( $value ) || ! preg_match( '/^#[a-f0-9]{6}$/iD', $value ) ) { throw new InvalidArgumentException( $key ); }
				$output[ $key ] = strtolower( $value );
			} elseif ( 'select' === $rule['type'] ) {
				if ( ! is_string( $value ) || ! isset( $rule['choices'][ $value ] ) ) { throw new InvalidArgumentException( $key ); }
				$output[ $key ] = $value;
			} else {
				if ( ! is_scalar( $value ) || ! is_numeric( $value ) || (float) $value !== (float) (int) $value || (int) $value < ( $rule['min'] ?? 0 ) || (int) $value > ( $rule['max'] ?? 1 ) ) { throw new InvalidArgumentException( $key ); }
				$output[ $key ] = (int) $value;
			}
		}
		return $output;
	}
}
