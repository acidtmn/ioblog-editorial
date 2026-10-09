<?php
/** Представление студии: поля описаны контрактом, изменение сайта выполняется сервисом. */
if ( ! current_user_can( 'manage_options' ) ) { return; }
$config = Ioblog_Design_Service::current();
$presets = Ioblog_Design_Presets::colors();
$preset_labels = Ioblog_Design_Presets::labels();
$preset = Ioblog_Design_Presets::matching( $config );
$groups = array( 'light' => __( 'Light palette', 'ioblog-editorial' ), 'dark' => __( 'Dark palette', 'ioblog-editorial' ), 'fonts' => __( 'Fonts and rhythm', 'ioblog-editorial' ), 'geometry' => __( 'Sizes and spacing', 'ioblog-editorial' ), 'regions' => __( 'Site regions', 'ioblog-editorial' ) );
?>
<div class="wrap io-admin io-design">
	<header class="io-admin-hero"><div><span class="io-admin-eyebrow">IO BLOG</span><h1><?php esc_html_e( 'Design studio', 'ioblog-editorial' ); ?></h1><p><?php esc_html_e( 'Preview your changes before publishing.', 'ioblog-editorial' ); ?></p></div></header>
	<details class="io-admin-card"><summary><?php esc_html_e( 'Upload your own font', 'ioblog-editorial' ); ?></summary><form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><input type="hidden" name="action" value="io_design_font"><?php wp_nonce_field( 'io_design_font' ); ?><label>WOFF2 · 2 MB <input type="file" name="font" accept=".woff2" required></label><?php submit_button( __( 'Upload font', 'ioblog-editorial' ), 'secondary' ); ?></form></details>
	<div class="io-design-toolbar"><label><?php esc_html_e( 'Find a setting', 'ioblog-editorial' ); ?><input type="search" id="io-design-search"></label><button type="button" class="button" data-design-preview><?php esc_html_e( 'Preview', 'ioblog-editorial' ); ?></button><button type="button" class="button button-primary" data-design-publish><?php esc_html_e( 'Publish design', 'ioblog-editorial' ); ?></button><p role="status" aria-live="polite" id="io-design-status"></p></div>
	<div class="io-design-workspace"><form id="io-design-form">
		<fieldset class="io-admin-card io-design-palettes" aria-describedby="io-design-palette-hint" hidden>
			<legend><?php esc_html_e( 'Color presets', 'ioblog-editorial' ); ?></legend>
			<p id="io-design-palette-hint"><?php esc_html_e( 'Paired light and dark colors. Fonts and layout stay unchanged.', 'ioblog-editorial' ); ?></p>
			<div class="io-design-palette-grid">
			<?php foreach ( $presets as $id => $colors ) { ?>
				<button type="button" class="io-design-palette" data-design-palette="<?php echo esc_attr( $id ); ?>" aria-pressed="<?php echo $preset === $id ? 'true' : 'false'; ?>">
					<span class="io-design-palette-swatches" aria-hidden="true"><?php foreach ( array( 'light', 'dark' ) as $scheme ) { ?><span><?php foreach ( array( 'bg', 'surface', 'text', 'accent' ) as $token ) { ?><i style="background-color:<?php echo esc_attr( $colors[ $scheme . '_' . $token ] ); ?>"></i><?php } ?></span><?php } ?></span>
					<strong><?php echo esc_html( $preset_labels[ $id ] ); ?></strong>
				</button>
			<?php } ?>
			</div>
			<p id="io-design-palette-status" role="status" aria-live="polite"><?php echo esc_html( $preset_labels[ $preset ] ?? __( 'Custom palette', 'ioblog-editorial' ) ); ?></p>
		</fieldset>
	<?php foreach ( $groups as $group => $title ) { ?>
		<section class="io-admin-card"><h2><?php echo esc_html( $title ); ?></h2><div class="io-design-fields">
		<?php foreach ( Ioblog_Design_Schema::fields() as $key => $field ) { if ( $group !== $field['group'] ) { continue; } ?>
			<label data-design-field><strong><?php echo esc_html( Ioblog_Design_Labels::text( $field['label'] ) ); ?></strong>
			<?php if ( 'font_attachment' === $key ) { ?><select name="config[font_attachment]"><option value="0"><?php esc_html_e( 'No uploaded font', 'ioblog-editorial' ); ?></option><?php foreach ( get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_mime_type' => 'font/woff2', 'numberposts' => 100 ) ) as $font ) { ?><option value="<?php echo esc_attr( $font->ID ); ?>" <?php selected( $config[ $key ], $font->ID ); ?>><?php echo esc_html( $font->post_title ); ?></option><?php } ?></select>
			<?php } elseif ( 'select' === $field['type'] ) { ?>
				<select name="config[<?php echo esc_attr( $key ); ?>]"><?php foreach ( $field['choices'] as $value => $label ) { ?><option value="<?php echo esc_attr( $value ); ?>" <?php selected( $config[ $key ], $value ); ?>><?php echo esc_html( Ioblog_Design_Labels::text( $label ) ); ?></option><?php } ?></select>
			<?php } elseif ( 'checkbox' === $field['type'] ) { ?>
				<input type="checkbox" name="config[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( $config[ $key ], 1 ); ?>>
			<?php } else { ?>
				<input type="<?php echo esc_attr( $field['type'] ); ?>" name="config[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $config[ $key ] ); ?>" <?php if ( 'number' === $field['type'] ) { echo 'min="' . esc_attr( $field['min'] ) . '" max="' . esc_attr( $field['max'] ) . '"'; } ?>>
			<?php } ?>
			<button type="button" class="io-design-reset" data-reset="<?php echo esc_attr( $field['default'] ); ?>"><?php esc_html_e( 'Reset', 'ioblog-editorial' ); ?></button></label>
		<?php } ?></div></section>
	<?php } ?></form>
	<aside class="io-design-preview"><div><button type="button" class="button" data-preview-width="100%"><?php esc_html_e( 'Desktop', 'ioblog-editorial' ); ?></button><button type="button" class="button" data-preview-width="768px"><?php esc_html_e( 'Tablet', 'ioblog-editorial' ); ?></button><button type="button" class="button" data-preview-width="390px"><?php esc_html_e( 'Mobile', 'ioblog-editorial' ); ?></button></div><div id="io-design-contrast" role="status" aria-live="polite"></div><iframe title="<?php esc_attr_e( 'Site preview', 'ioblog-editorial' ); ?>" src="<?php echo esc_url( home_url( '/' ) ); ?>"></iframe></aside></div>
</div>
