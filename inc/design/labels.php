<?php
/** Литералы для стандартного gettext и POT: динамическая схема не должна оставаться непереводимой. */
final class Ioblog_Design_Labels {
	public static function text( $label ) {
		$labels = array(
			'Header navigation' => __( 'Header navigation', 'ioblog-editorial' ),
			'Page background' => __( 'Page background', 'ioblog-editorial' ), 'Card background' => __( 'Card background', 'ioblog-editorial' ), 'Soft background' => __( 'Soft background', 'ioblog-editorial' ),
			'Main text' => __( 'Main text', 'ioblog-editorial' ), 'Secondary text' => __( 'Secondary text', 'ioblog-editorial' ), 'Borders' => __( 'Borders', 'ioblog-editorial' ),
			'Accent color' => __( 'Accent color', 'ioblog-editorial' ), 'Accent on hover' => __( 'Accent on hover', 'ioblog-editorial' ), 'Soft accent' => __( 'Soft accent', 'ioblog-editorial' ),
			'Errors' => __( 'Errors', 'ioblog-editorial' ), 'Button text' => __( 'Button text', 'ioblog-editorial' ), 'Keyboard focus' => __( 'Keyboard focus', 'ioblog-editorial' ),
			'Body font' => __( 'Body font', 'ioblog-editorial' ), 'Heading font' => __( 'Heading font', 'ioblog-editorial' ), 'Navigation font' => __( 'Navigation font', 'ioblog-editorial' ), 'Button font' => __( 'Button font', 'ioblog-editorial' ), 'Metadata font' => __( 'Metadata font', 'ioblog-editorial' ), 'Code font' => __( 'Code font', 'ioblog-editorial' ),
			'Interface text size' => __( 'Interface text size', 'ioblog-editorial' ), 'Heading weight' => __( 'Heading weight', 'ioblog-editorial' ), 'Heading scale, %' => __( 'Heading scale, %', 'ioblog-editorial' ), 'Paragraph spacing' => __( 'Paragraph spacing', 'ioblog-editorial' ), 'Mobile article text size' => __( 'Mobile article text size', 'ioblog-editorial' ), 'Letter spacing' => __( 'Letter spacing', 'ioblog-editorial' ),
			'Site width' => __( 'Site width', 'ioblog-editorial' ), 'Sidebar width' => __( 'Sidebar width', 'ioblog-editorial' ), 'Corner radius' => __( 'Corner radius', 'ioblog-editorial' ), 'Section spacing' => __( 'Section spacing', 'ioblog-editorial' ), 'Card spacing' => __( 'Card spacing', 'ioblog-editorial' ), 'Footer columns' => __( 'Footer columns', 'ioblog-editorial' ),
			'Sticky header' => __( 'Sticky header', 'ioblog-editorial' ), 'Header search' => __( 'Header search', 'ioblog-editorial' ), 'Color scheme switch' => __( 'Color scheme switch', 'ioblog-editorial' ), 'Logo tagline' => __( 'Logo tagline', 'ioblog-editorial' ), 'Article cover' => __( 'Article cover', 'ioblog-editorial' ), 'Author card' => __( 'Author card', 'ioblog-editorial' ), 'Adjacent articles' => __( 'Adjacent articles', 'ioblog-editorial' ), 'Related articles' => __( 'Related articles', 'ioblog-editorial' ), 'Table of contents' => __( 'Table of contents', 'ioblog-editorial' ), 'Sidebar articles' => __( 'Sidebar articles', 'ioblog-editorial' ), 'Card excerpts' => __( 'Card excerpts', 'ioblog-editorial' ), 'Card metadata' => __( 'Card metadata', 'ioblog-editorial' ), 'Footer social links' => __( 'Footer social links', 'ioblog-editorial' ),
			'Reader mode' => __( 'Reader mode', 'ioblog-editorial' ), 'Print article' => __( 'Print article', 'ioblog-editorial' ), 'Card image ratio' => __( 'Card image ratio', 'ioblog-editorial' ), 'Shadows' => __( 'Shadows', 'ioblog-editorial' ), 'No shadow' => __( 'No shadow', 'ioblog-editorial' ), 'Soft shadow' => __( 'Soft shadow', 'ioblog-editorial' ), 'Strong shadow' => __( 'Strong shadow', 'ioblog-editorial' ),
			'Monospace' => __( 'Monospace', 'ioblog-editorial' ),
			'Uploaded font' => __( 'Uploaded font', 'ioblog-editorial' ), 'Weight: menu' => __( 'Weight: menu', 'ioblog-editorial' ), 'Weight: button' => __( 'Weight: button', 'ioblog-editorial' ), 'Weight: meta' => __( 'Weight: meta', 'ioblog-editorial' ), 'Weight: body' => __( 'Weight: body', 'ioblog-editorial' ),
		);
		return $labels[ $label ] ?? $label;
	}
}
