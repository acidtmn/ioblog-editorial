<?php
/** Разметка отдельного экрана; содержимое загруженного файла выводится только как текст. */
defined( 'ABSPATH' ) || exit;
?>
<div class="wrap io-admin io-transfer">
	<header class="io-admin-hero">
		<div><span>IO BLOG EDITORIAL <b class="io-admin-version"><?php echo esc_html( wp_get_theme()->get( 'Version' ) ); ?></b></span><h1><?php esc_html_e( 'Export and import', 'ioblog-editorial' ); ?></h1><p><?php esc_html_e( 'One backup for all theme and Pro settings, including advertising code and integrations.', 'ioblog-editorial' ); ?></p></div>
		<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=ioblog-settings' ) ); ?>"><?php esc_html_e( 'Site management', 'ioblog-editorial' ); ?></a>
	</header>
	<div class="io-transfer-warning" role="note"><strong><?php esc_html_e( 'Confidential backup', 'ioblog-editorial' ); ?></strong><p><?php esc_html_e( 'The JSON file includes CAPTCHA keys, advertising and analytics code, links, and all saved settings. It is not encrypted. Keep it private; never publish it on GitHub or send it to strangers.', 'ioblog-editorial' ); ?></p></div>
	<div class="io-transfer-grid">
		<section class="io-admin-card">
			<div class="io-admin-card__heading"><span>01</span><div><h2><?php esc_html_e( 'Download settings', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'Save a current backup before importing or moving your site.', 'ioblog-editorial' ); ?></p></div></div>
			<ul class="io-transfer-list">
				<li><?php esc_html_e( 'All interface, reading, homepage, comment, and CAPTCHA settings.', 'ioblog-editorial' ); ?></li>
				<li><?php esc_html_e( 'Every advertising slot: code, enabled state, and paragraph position.', 'ioblog-editorial' ); ?></li>
				<li><?php esc_html_e( 'Analytics, short links, social networks, and saved Pro options.', 'ioblog-editorial' ); ?></li>
				<li><?php esc_html_e( 'Customizer values, site title and tagline, custom CSS, and references to menus, logo, and site icon.', 'ioblog-editorial' ); ?></li>
			</ul>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="ioblog_transfer_export">
				<?php wp_nonce_field( 'ioblog_transfer_export' ); ?>
				<button class="button button-primary" type="submit"><?php esc_html_e( 'Download JSON backup', 'ioblog-editorial' ); ?></button>
			</form>
			<p class="description"><?php esc_html_e( 'This is a settings backup, not a full site backup. Posts, media files, menu items, visitor data, and license activation are not copied. References are included and matched to existing objects during import.', 'ioblog-editorial' ); ?></p>
		</section>
		<section class="io-admin-card">
			<div class="io-admin-card__heading"><span>02</span><div><h2><?php esc_html_e( 'Restore settings', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'Preview first. Nothing is saved until you confirm.', 'ioblog-editorial' ); ?></p></div></div>
			<form id="io-transfer-upload">
				<label for="io-transfer-file"><strong><?php esc_html_e( 'Settings backup file', 'ioblog-editorial' ); ?></strong></label>
				<input id="io-transfer-file" type="file" name="backup" accept=".json,application/json" required>
				<p class="description"><?php esc_html_e( 'JSON up to 1 MB. Upload only a file you trust: importing code can change what runs on your site.', 'ioblog-editorial' ); ?></p>
				<button class="button button-secondary" type="submit"><?php esc_html_e( 'Preview import', 'ioblog-editorial' ); ?></button>
			</form>
			<noscript><p><?php esc_html_e( 'Enable JavaScript to preview and confirm an import.', 'ioblog-editorial' ); ?></p></noscript>
			<p class="io-transfer-status" id="io-transfer-status" role="status" aria-live="polite"></p>
		</section>
	</div>
	<section class="io-admin-card io-transfer-preview" id="io-transfer-preview" hidden>
		<h2><?php esc_html_e( 'Review changes', 'ioblog-editorial' ); ?></h2>
		<p id="io-transfer-source"></p><p id="io-transfer-skipped" class="io-transfer-warning" hidden></p>
		<fieldset><legend><?php esc_html_e( 'Sections to restore', 'ioblog-editorial' ); ?></legend><div class="io-transfer-groups">
			<?php foreach ( Ioblog_Settings_Transfer_Schema::groups() as $id => $label ) { ?>
				<label><input type="checkbox" name="transfer-group" value="<?php echo esc_attr( $id ); ?>" checked> <?php echo esc_html( $label ); ?></label>
			<?php } ?>
		</div></fieldset>
		<div class="io-transfer-table-wrap"><table class="widefat striped"><thead><tr><th><?php esc_html_e( 'Setting', 'ioblog-editorial' ); ?></th><th><?php esc_html_e( 'Current value', 'ioblog-editorial' ); ?></th><th><?php esc_html_e( 'Backup value', 'ioblog-editorial' ); ?></th></tr></thead><tbody id="io-transfer-rows"></tbody></table></div>
		<label class="io-transfer-confirm"><input id="io-transfer-trust" type="checkbox"> <?php esc_html_e( 'I trust this backup and have saved a copy of the current settings.', 'ioblog-editorial' ); ?></label>
		<button id="io-transfer-apply" class="button button-primary" type="button" disabled><?php esc_html_e( 'Apply selected settings', 'ioblog-editorial' ); ?></button>
		<p class="description"><?php esc_html_e( 'Keys and code are included but masked in this preview. No URLs are rewritten automatically. Review partner links and CAPTCHA domain restrictions after moving to another domain.', 'ioblog-editorial' ); ?></p>
	</section>
</div>
