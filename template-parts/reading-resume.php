<?php
/** Персональное место остановки выводится только после чтения localStorage. */
defined( 'ABSPATH' ) || exit;
if ( ! ioblog_get_setting( 'library_enabled' ) ) { return; }
?>
<div class="io-reading-resume" hidden><span class="io-reading-resume__text"></span><button type="button" class="io-reading-resume__go"><?php esc_html_e( 'Continue reading', 'ioblog-editorial' ); ?></button><button type="button" class="io-reading-resume__dismiss" aria-label="<?php esc_attr_e( 'Dismiss reading position', 'ioblog-editorial' ); ?>">&#215;</button></div>
