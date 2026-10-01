<?php
/**
 * Встроенная строка поиска для архивов и 404.
 *
 * @package IoblogEditorial
 */
?>
<form class="io-inline-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="io-inline-search-input"><?php esc_html_e( 'Search the site', 'ioblog-editorial' ); ?></label>
	<input id="io-inline-search-input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'What are you looking for?', 'ioblog-editorial' ); ?>">
	<button class="io-button" type="submit"><?php esc_html_e( 'Search', 'ioblog-editorial' ); ?></button>
</form>
