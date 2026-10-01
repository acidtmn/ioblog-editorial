<?php
/**
 * Одиночная статья с редакционной сеткой и липкой боковой колонкой.
 *
 * @package IoblogEditorial
 */

get_header();
while ( have_posts() ) {
	the_post();
	$post_id = get_the_ID();
	$category = ioblog_primary_category( $post_id );
	$prepared = ioblog_prepare_table_of_contents( apply_filters( 'the_content', get_the_content() ) );
	?>
	<article <?php post_class( 'io-single io-container' ); ?>>
		<nav class="io-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'ioblog-editorial' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Homepage', 'ioblog-editorial' ); ?></a><span>›</span>
			<?php if ( $category ) { ?><a href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a><span>›</span><?php } ?>
			<span><?php the_title(); ?></span>
		</nav>

		<header class="io-article-header">
			<?php if ( $category ) { ?><a class="io-article-header__category" href="<?php echo esc_url( get_term_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a><?php } ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( has_excerpt() ) { ?><p class="io-article-header__lead"><?php echo esc_html( get_the_excerpt() ); ?></p><?php } ?>
			<div class="io-article-header__bottom">
				<?php ioblog_post_meta( $post_id, true ); ?>
				<div class="io-article-actions" aria-label="<?php esc_attr_e( 'Article actions', 'ioblog-editorial' ); ?>">
					<button class="io-action-button io-copy-link" type="button" aria-label="<?php esc_attr_e( 'Copy link', 'ioblog-editorial' ); ?>" data-label="<?php esc_attr_e( 'Link copied', 'ioblog-editorial' ); ?>" data-tooltip="<?php esc_attr_e( 'Copy link', 'ioblog-editorial' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2"></rect><path d="M15 9V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h3"></path></svg>
						<span class="screen-reader-text"><?php esc_html_e( 'Copy link', 'ioblog-editorial' ); ?></span>
					</button>
					<a class="io-action-button" href="#comments" aria-label="<?php esc_attr_e( 'Go to comments', 'ioblog-editorial' ); ?>" data-tooltip="<?php esc_attr_e( 'Comments', 'ioblog-editorial' ); ?>">
						<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 15a4 4 0 0 1-4 4H8l-5 3 1.5-5A7 7 0 0 1 3 12V9a4 4 0 0 1 4-4h9a4 4 0 0 1 4 4z"></path></svg>
						<span class="screen-reader-text"><?php esc_html_e( 'Comments', 'ioblog-editorial' ); ?></span>
					</a>
				</div>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) { ?>
			<figure class="io-article-cover"><?php ioblog_post_thumbnail( $post_id, 'full', true ); ?></figure>
		<?php } ?>

		<div class="io-article-layout">
			<div class="io-article-main">
				<details class="io-mobile-toc"><summary><?php esc_html_e( 'Table of contents', 'ioblog-editorial' ); ?></summary><?php ioblog_render_toc( $prepared['items'], 'io-toc--mobile' ); ?></details>
				<div class="io-article-content entry-content"><?php echo $prepared['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Контент прошёл штатные фильтры WordPress. ?></div>

				<footer class="io-article-footer">
					<?php the_tags( '<div class="io-tags">', '', '</div>' ); ?>
					<div class="io-author-card">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
						<div><strong><?php the_author(); ?></strong><span><?php esc_html_e( 'IO Blog editorial team', 'ioblog-editorial' ); ?></span><p><?php echo esc_html( get_the_author_meta( 'description' ) ?: __( 'We write about technology that makes everyday life easier.', 'ioblog-editorial' ) ); ?></p></div>
					</div>
				</footer>

				<nav class="io-post-navigation" aria-label="<?php esc_attr_e( 'Adjacent articles', 'ioblog-editorial' ); ?>">
					<?php $previous = get_previous_post(); $next = get_next_post(); ?>
					<?php if ( $previous ) { ?><a rel="prev" href="<?php echo esc_url( get_permalink( $previous ) ); ?>"><span>← <?php esc_html_e( 'Previous article', 'ioblog-editorial' ); ?></span><strong><?php echo esc_html( get_the_title( $previous ) ); ?></strong></a><?php } ?>
					<?php if ( $next ) { ?><a rel="next" href="<?php echo esc_url( get_permalink( $next ) ); ?>"><span><?php esc_html_e( 'Next article', 'ioblog-editorial' ); ?> →</span><strong><?php echo esc_html( get_the_title( $next ) ); ?></strong></a><?php } ?>
				</nav>

				<div id="comments" class="io-comments"><?php comments_template(); ?></div>
			</div>

			<aside class="io-article-sidebar">
				<?php ioblog_render_toc( $prepared['items'], 'io-toc--desktop' ); ?>
				<?php do_action( 'ioblog_ad_slot', 'sidebar' ); ?>
				<?php if ( is_active_sidebar( 'article-sidebar' ) ) { dynamic_sidebar( 'article-sidebar' ); } ?>
				<section class="io-widget io-widget--popular"><h2 class="io-widget__title"><?php esc_html_e( 'New articles', 'ioblog-editorial' ); ?></h2>
					<?php $sidebar_query = ioblog_recent_posts_query( $post_id, 5 ); ?>
					<?php foreach ( $sidebar_query->posts as $index => $sidebar_post ) { ?><a href="<?php echo esc_url( get_permalink( $sidebar_post ) ); ?>"><span><?php echo esc_html( $index + 1 ); ?></span><strong><?php echo esc_html( get_the_title( $sidebar_post ) ); ?></strong></a><?php } ?>
					<?php wp_reset_postdata(); ?>
				</section>
			</aside>
		</div>
	</article>
	<?php do_action( 'ioblog_ad_slot', 'after' ); ?>

	<section class="io-related io-container">
		<div class="io-section-heading"><h2><?php esc_html_e( 'Read next', 'ioblog-editorial' ); ?></h2><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'All articles', 'ioblog-editorial' ); ?> →</a></div>
		<div class="io-post-grid io-post-grid--related">
			<?php $related = ioblog_recent_posts_query( $post_id, 3 ); ?>
			<?php foreach ( $related->posts as $related_post ) { ioblog_render_card( $related_post->ID, 'standard' ); } ?>
			<?php wp_reset_postdata(); ?>
		</div>
	</section>
	<?php
}
get_footer();
