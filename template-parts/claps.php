<?php
/** Представление общедоступной кнопки; персональные значения поступают через некешируемый API. */
defined( 'ABSPATH' ) || exit;
if ( ! ioblog_get_setting( 'claps_enabled' ) || 'publish' !== get_post_status() || get_post_field( 'post_password', get_the_ID() ) ) { return; }
?>
<section class="io-claps" aria-labelledby="io-claps-title">
	<div class="io-claps__intro">
		<span class="io-claps__eyebrow"><?php esc_html_e( 'A little applause goes a long way', 'ioblog-editorial' ); ?></span>
		<h2 id="io-claps-title"><?php esc_html_e( 'Was this useful?', 'ioblog-editorial' ); ?></h2>
		<p><?php esc_html_e( 'Give the author a round of applause. You can clap up to 10 times.', 'ioblog-editorial' ); ?></p>
	</div>
	<div class="io-claps__interaction">
		<button class="io-claps__button" type="button" disabled aria-label="<?php esc_attr_e( 'Clap for this article', 'ioblog-editorial' ); ?>" aria-describedby="io-claps-mine">
			<svg class="io-claps__icon" viewBox="0 0 48 48" aria-hidden="true"><g class="io-claps__hand io-claps__hand--back"><path d="m24 31-9-12a2 2 0 0 1 3-2l6 7-7-13a2 2 0 0 1 4-2l7 13-4-15a2 2 0 0 1 4-1l4 15 1-12a2 2 0 0 1 4 0l-1 17-2 8z"/></g><g class="io-claps__hand io-claps__hand--front"><path d="m13 39-7-13a2 2 0 0 1 3-2l5 7-2-18a2 2 0 0 1 4 0l2 13 1-17a2 2 0 0 1 4 0l-1 17 4-14a2 2 0 0 1 4 1l-3 16 5-6a3 3 0 0 1 4 4l-8 13a9 9 0 0 1-15-1z"/></g><path class="io-claps__rays" d="m37 4 2-2m1 10 4-1M9 6 7 3"/></svg>
			<span class="io-claps__burst" aria-hidden="true">+1</span>
		</button>
		<span id="io-claps-mine" class="io-claps__mine"></span>
		<button class="io-claps__undo" type="button" hidden><?php esc_html_e( 'Undo my claps', 'ioblog-editorial' ); ?></button>
	</div>
	<div class="io-claps__footer">
		<div class="io-claps__stats"><span><strong data-claps-total>—</strong> <?php esc_html_e( 'Claps', 'ioblog-editorial' ); ?></span><span><strong data-claps-readers>—</strong> <?php esc_html_e( 'Supporters', 'ioblog-editorial' ); ?></span></div>
		<p class="io-claps__status" role="status" aria-live="polite"></p>
		<p class="io-claps__privacy"><?php esc_html_e( 'No account needed. Your choice is remembered in this browser.', 'ioblog-editorial' ); ?></p>
	</div>
	<noscript><p><?php esc_html_e( 'Enable JavaScript to give claps.', 'ioblog-editorial' ); ?></p></noscript>
</section>
