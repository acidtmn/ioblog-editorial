<?php
/** Нативный dialog удерживает фокус; список собирается из личного браузерного хранилища. */
defined( 'ABSPATH' ) || exit;
?>
<dialog id="io-library" class="io-library" aria-labelledby="io-library-title">
	<header class="io-library__header"><div><span class="io-eyebrow"><?php esc_html_e( 'Your reading space', 'ioblog-editorial' ); ?></span><h2 id="io-library-title"><?php esc_html_e( 'My library', 'ioblog-editorial' ); ?></h2></div><button type="button" class="io-icon-button io-library-close" aria-label="<?php esc_attr_e( 'Close library', 'ioblog-editorial' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>
	<p class="io-library__hint"><?php esc_html_e( 'Saved articles and reading progress are kept on this device.', 'ioblog-editorial' ); ?></p>
	<div class="io-library__views" role="group" aria-label="<?php esc_attr_e( 'Library view', 'ioblog-editorial' ); ?>">
		<button type="button" data-library-view="saved" aria-pressed="true"><?php esc_html_e( 'Bookmarks', 'ioblog-editorial' ); ?></button>
		<button type="button" data-library-view="history" aria-pressed="false"><?php esc_html_e( 'Reading history', 'ioblog-editorial' ); ?></button>
	</div>
	<div class="io-library__tools">
		<label><span class="screen-reader-text"><?php esc_html_e( 'Search your library', 'ioblog-editorial' ); ?></span><input type="search" class="io-library__search" maxlength="300" placeholder="<?php esc_attr_e( 'Find an article...', 'ioblog-editorial' ); ?>"></label>
		<label><span class="screen-reader-text"><?php esc_html_e( 'Reading status', 'ioblog-editorial' ); ?></span><select class="io-library__filter">
			<option value="all"><?php esc_html_e( 'All statuses', 'ioblog-editorial' ); ?></option>
			<option value="unread"><?php esc_html_e( 'Not started', 'ioblog-editorial' ); ?></option>
			<option value="reading"><?php esc_html_e( 'In progress', 'ioblog-editorial' ); ?></option>
			<option value="complete"><?php esc_html_e( 'Finished', 'ioblog-editorial' ); ?></option>
		</select></label>
	</div>
	<p class="io-library__summary" aria-live="polite"></p>
	<ul class="io-library__list"></ul>
	<p class="io-library__status" role="status" aria-live="polite"></p>
	<footer class="io-library__transfer">
		<div><button type="button" class="io-library__export"><?php esc_html_e( 'Export library', 'ioblog-editorial' ); ?></button><button type="button" class="io-library__import"><?php esc_html_e( 'Import library', 'ioblog-editorial' ); ?></button></div>
		<input type="file" class="io-library__file" accept=".json,application/json" hidden aria-label="<?php esc_attr_e( 'Library backup file', 'ioblog-editorial' ); ?>">
		<p><?php esc_html_e( 'The file contains your reading history. Keep it private. Import merges data for this site without removing existing bookmarks.', 'ioblog-editorial' ); ?></p>
	</footer>
</dialog>
