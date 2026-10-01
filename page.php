<?php
/**
 * Стандартная информационная страница.
 *
 * @package IoblogEditorial
 */

get_header();
while ( have_posts() ) {
	the_post();
	?>
	<article <?php post_class( 'io-page io-container' ); ?>>
		<header class="io-page__header"><h1><?php the_title(); ?></h1></header>
		<div class="io-article-content entry-content"><?php the_content(); ?></div>
	</article>
	<?php
}
get_footer();
