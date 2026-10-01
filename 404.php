<?php
/**
 * Страница ненайденного адреса.
 *
 * @package IoblogEditorial
 */

get_header();
?>
<section class="io-error io-container">
	<span>404</span>
	<h1><?php esc_html_e( 'Page not found', 'ioblog-editorial' ); ?></h1>
	<p><?php esc_html_e( 'The address may have changed. Try searching for the material you need.', 'ioblog-editorial' ); ?></p>
	<?php get_search_form(); ?>
	<a class="io-button io-button--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Return to homepage', 'ioblog-editorial' ); ?></a>
</section>
<?php get_footer(); ?>
