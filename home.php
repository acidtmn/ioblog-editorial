<?php
/**
 * Универсальная редакционная главная.
 *
 * @package IoblogEditorial
 */

get_header();
$paged          = max( 1, (int) get_query_var( 'paged' ) );
$post_ids       = Ioblog_Homepage_Layout::post_ids( $wp_query->posts );
// Исключаем из общей ленты только действительно показанные в других секциях записи.
$featured_id    = ioblog_get_setting( 'home_show_hero' ) ? Ioblog_Homepage_Layout::featured_id( $post_ids ) : 0;
$editorial_ids  = ioblog_get_setting( 'home_show_editorial' ) ? Ioblog_Homepage_Layout::editorial_ids( $post_ids, $featured_id ) : array();
$latest_ids     = 1 === $paged ? Ioblog_Homepage_Layout::latest_ids( $post_ids, $featured_id, $editorial_ids ) : $post_ids;
$about_url      = Ioblog_Homepage_Layout::about_url();
$section_order  = 1 === $paged ? Ioblog_Homepage_Layout::section_order() : array( 'latest' );
?>
<div class="io-home">
	<?php if ( 1 === $paged && $featured_id && ioblog_get_setting( 'home_show_hero' ) ) { ?>
		<section class="io-home-hero io-container">
			<div class="io-home-hero__copy">
				<span class="io-eyebrow"><?php echo esc_html( get_theme_mod( 'ioblog_hero_eyebrow', __( 'Independent technology journal', 'ioblog-editorial' ) ) ); ?></span>
				<h1><?php echo esc_html( get_theme_mod( 'ioblog_hero_title', __( 'Technology', 'ioblog-editorial' ) ) ); ?><strong><?php echo esc_html( get_theme_mod( 'ioblog_hero_accent', __( 'without the noise', 'ioblog-editorial' ) ) ); ?></strong></h1>
				<p><?php echo esc_html( get_theme_mod( 'ioblog_hero_description', __( 'Windows, Docker, gadgets, cars, and practical ideas that actually work.', 'ioblog-editorial' ) ) ); ?></p>
				<div class="io-home-hero__benefits">
					<?php foreach ( array( __( 'Practical guides', 'ioblog-editorial' ), __( 'Honest reviews', 'ioblog-editorial' ), __( 'First-hand experience', 'ioblog-editorial' ) ) as $index => $default ) {
						$benefit = get_theme_mod( 'ioblog_hero_benefit_' . ( $index + 1 ), $default );
						// Пустое поле скрывает отдельную подпись, не оставляя пустую плашку.
						if ( $benefit ) { ?><span><?php echo esc_html( $benefit ); ?></span><?php }
					} ?>
				</div>
				<div class="io-home-hero__actions">
					<a class="io-button" href="#fresh"><?php esc_html_e( 'Read new articles', 'ioblog-editorial' ); ?> <span aria-hidden="true">→</span></a>
					<?php if ( $about_url ) { ?><a class="io-button io-button--secondary" href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'About the blog', 'ioblog-editorial' ); ?></a><?php } ?>
				</div>
			</div>
			<div class="io-home-hero__featured"><?php ioblog_render_card( $featured_id, 'featured' ); ?></div>
		</section>
	<?php } ?>
	<?php if ( 1 === $paged ) { do_action( 'ioblog_ad_slot', 'home' ); } ?>

	<?php foreach ( $section_order as $section ) { ?>
		<?php if ( 'categories' === $section && ioblog_get_setting( 'home_show_categories' ) ) { ?>
			<section class="io-home-categories"><div class="io-container"><?php Ioblog_Category_Icons::render_categories( true ); ?></div></section>
		<?php } ?>

		<?php if ( 'editorial' === $section && ioblog_get_setting( 'home_show_editorial' ) && $editorial_ids ) { ?>
			<section class="io-home-section io-container">
				<div class="io-section-heading"><h2><?php esc_html_e( 'Editor’s choice', 'ioblog-editorial' ); ?></h2><p><?php esc_html_e( 'A hand-picked collection from the editorial team', 'ioblog-editorial' ); ?></p></div>
				<div class="io-bento">
					<?php foreach ( $editorial_ids as $index => $post_id ) { ioblog_render_card( $post_id, 0 === $index ? 'bento-large' : 'compact' ); } ?>
				</div>
			</section>
		<?php } ?>

		<?php if ( 'latest' === $section ) { ?>
			<section id="fresh" class="io-home-section io-container">
				<div class="io-section-heading"><h2><?php echo esc_html( 1 === $paged ? __( 'Latest articles', 'ioblog-editorial' ) : __( 'All articles', 'ioblog-editorial' ) ); ?></h2><p><?php esc_html_e( 'New articles, reviews, and guides', 'ioblog-editorial' ); ?></p></div>
				<?php if ( $latest_ids ) { ?>
					<div class="io-post-grid"><?php foreach ( $latest_ids as $post_id ) { ioblog_render_card( $post_id, 'standard' ); } ?></div>
				<?php } else { ?>
					<div class="io-home-empty"><strong><?php esc_html_e( 'More stories are on the way', 'ioblog-editorial' ); ?></strong><p><?php esc_html_e( 'The newest articles will appear here.', 'ioblog-editorial' ); ?></p></div>
				<?php } ?>
				<?php the_posts_pagination( array( 'mid_size' => 2, 'prev_text' => '←', 'next_text' => '→' ) ); ?>
			</section>
		<?php } ?>
	<?php } ?>
	<?php if ( 1 === $paged ) { do_action( 'ioblog_home_after_sections' ); } ?>
</div>
<?php get_footer(); ?>
