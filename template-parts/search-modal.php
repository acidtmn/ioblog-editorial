<?php
/**
 * Центрированное окно живого поиска.
 *
 * @package IoblogEditorial
 */

$categories = get_categories( array( 'orderby' => 'count', 'order' => 'DESC', 'number' => 8, 'hide_empty' => true ) );
?>
<div id="io-search-modal" class="io-search-modal" hidden>
	<button class="io-search-modal__backdrop" type="button" aria-label="<?php esc_attr_e( 'Close search', 'ioblog-editorial' ); ?>"></button>
	<section class="io-search-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="io-search-title">
		<div class="io-search-modal__header">
			<div><span class="io-eyebrow"><?php esc_html_e( 'Site search', 'ioblog-editorial' ); ?></span><h2 id="io-search-title"><?php echo esc_html( get_theme_mod( 'ioblog_search_heading', __( 'What are you looking for?', 'ioblog-editorial' ) ) ); ?></h2></div>
			<button class="io-icon-button io-search-close" type="button" aria-label="<?php esc_attr_e( 'Close search', 'ioblog-editorial' ); ?>">×</button>
		</div>
		<form class="io-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get" role="search">
			<label class="screen-reader-text" for="io-search-input"><?php esc_html_e( 'Search', 'ioblog-editorial' ); ?></label>
			<input id="io-search-input" class="io-search-form__input" type="search" name="s" placeholder="<?php esc_attr_e( 'Article title or topic…', 'ioblog-editorial' ); ?>" autocomplete="off">
			<button class="io-button" type="submit"><?php esc_html_e( 'Search', 'ioblog-editorial' ); ?></button>
		</form>
		<p class="io-search-status" aria-live="polite"><?php echo esc_html( sprintf( /* translators: %d: minimum number of search characters. */ __( 'Enter at least %d characters', 'ioblog-editorial' ), (int) ioblog_get_setting( 'search_min_chars' ) ) ); ?></p>
		<div class="io-search-results" hidden></div>
		<?php if ( $categories ) { ?>
			<div class="io-search-categories">
				<?php foreach ( $categories as $category ) { ?>
					<a href="<?php echo esc_url( get_term_link( $category ) ); ?>">#<?php echo esc_html( $category->name ); ?></a>
				<?php } ?>
			</div>
		<?php } ?>
	</section>
</div>
