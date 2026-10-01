<?php
/**
 * Архивы, рубрики, метки и результаты поиска.
 *
 * @package IoblogEditorial
 */

get_header();
?>
<section class="io-archive io-container">
	<header class="io-archive__header">
		<span class="io-eyebrow"><?php echo esc_html( is_search() ? __( 'Site search', 'ioblog-editorial' ) : __( 'IO Blog archive', 'ioblog-editorial' ) ); ?></span>
		<h1><?php echo is_search() ? esc_html( sprintf( /* translators: %s: search query. */ __( 'Search results for: %s', 'ioblog-editorial' ), get_search_query() ) ) : wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php if ( is_search() ) { get_search_form(); } else { the_archive_description( '<div class="io-archive__description">', '</div>' ); } ?>
	</header>

	<?php if ( have_posts() ) { ?>
		<div class="io-post-grid io-post-grid--archive">
			<?php while ( have_posts() ) { the_post(); ioblog_render_card( get_the_ID(), 'standard' ); } ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?>
	<?php } else { ?>
		<div class="io-empty-state"><strong><?php esc_html_e( 'Nothing found', 'ioblog-editorial' ); ?></strong><p><?php esc_html_e( 'Try a different search or browse the latest articles.', 'ioblog-editorial' ); ?></p><a class="io-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Homepage', 'ioblog-editorial' ); ?></a></div>
	<?php } ?>
</section>
<?php get_footer(); ?>
