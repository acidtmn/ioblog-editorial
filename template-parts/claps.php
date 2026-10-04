<?php
/** Представление общедоступной кнопки; персональные значения поступают через некешируемый API. */
defined( 'ABSPATH' ) || exit;
if ( ! ioblog_get_setting( 'claps_enabled' ) || 'publish' !== get_post_status() || get_post_field( 'post_password', get_the_ID() ) ) { return; }
?>
<section id="io-claps" class="io-claps" aria-labelledby="io-claps-title">
	<div class="io-claps__intro">
		<span class="io-claps__eyebrow"><?php esc_html_e( 'A little applause goes a long way', 'ioblog-editorial' ); ?></span>
		<h2 id="io-claps-title"><?php esc_html_e( 'Was this useful?', 'ioblog-editorial' ); ?></h2>
		<p><?php esc_html_e( 'Give the author a round of applause. You can clap up to 10 times.', 'ioblog-editorial' ); ?></p>
	</div>
	<div class="io-claps__interaction">
		<button class="io-claps__button" type="button" disabled aria-label="<?php esc_attr_e( 'Clap for this article', 'ioblog-editorial' ); ?>" aria-describedby="io-claps-mine">
			<?php get_template_part( 'template-parts/clap-icon' ); ?>
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
