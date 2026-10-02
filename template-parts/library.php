<?php
/** Нативный dialog удерживает фокус; список собирается из личного браузерного хранилища. */
defined( 'ABSPATH' ) || exit;
?>
<dialog id="io-library" class="io-library" aria-labelledby="io-library-title">
	<header class="io-library__header"><div><span class="io-eyebrow"><?php esc_html_e( 'Your reading space', 'ioblog-editorial' ); ?></span><h2 id="io-library-title"><?php esc_html_e( 'My library', 'ioblog-editorial' ); ?></h2></div><button type="button" class="io-icon-button io-library-close" aria-label="<?php esc_attr_e( 'Close library', 'ioblog-editorial' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M6 18 18 6"/></svg></button></header>
	<p class="io-library__hint"><?php esc_html_e( 'Saved articles and reading progress are kept on this device.', 'ioblog-editorial' ); ?></p>
	<ul class="io-library__list"></ul>
	<p class="io-library__status" role="status" aria-live="polite"></p>
</dialog>
