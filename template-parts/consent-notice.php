<?php
/** Уведомление не выдаётся за согласие на регистрацию или публичное распространение профиля. */
$privacy = Ioblog_Legal_Settings::page( 'privacy' );
?>
<button type="button" class="io-consent-reopen" data-consent-open hidden><?php esc_html_e( 'Privacy settings', 'ioblog-editorial' ); ?></button>
<section class="io-consent-notice" data-consent-notice aria-labelledby="io-consent-title" hidden>
	<span class="io-consent-eyebrow">IO BLOG · <?php esc_html_e( 'Your choice', 'ioblog-editorial' ); ?></span>
	<h2 id="io-consent-title"><?php esc_html_e( 'Privacy and cookies', 'ioblog-editorial' ); ?></h2>
	<p><?php esc_html_e( 'Essential storage supports sign-in, security and your reading preferences. Optional analytics and advertising code run only after you allow them.', 'ioblog-editorial' ); ?></p>
	<?php if ( $privacy ) { ?><a href="<?php echo esc_url( get_permalink( $privacy ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Personal data policy', 'ioblog-editorial' ); ?></a><?php } ?>
	<div class="io-consent-choices"><label><input type="checkbox" data-consent-category="analytics"> <?php esc_html_e( 'Analytics', 'ioblog-editorial' ); ?></label><label><input type="checkbox" data-consent-category="marketing"> <?php esc_html_e( 'Advertising integrations', 'ioblog-editorial' ); ?></label></div>
	<div class="io-consent-buttons"><button type="button" data-consent-save="essential"><?php esc_html_e( 'Only essential', 'ioblog-editorial' ); ?></button><button type="button" data-consent-save="selected"><?php esc_html_e( 'Save my choice', 'ioblog-editorial' ); ?></button><button type="button" data-consent-save="all"><?php esc_html_e( 'Allow optional', 'ioblog-editorial' ); ?></button></div>
	<p class="io-consent-small"><?php esc_html_e( 'You can change this choice later. Registration and public-profile consent are requested separately.', 'ioblog-editorial' ); ?></p>
</section>
