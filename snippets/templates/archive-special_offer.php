<?php
/**
 * The template for displaying special offer archive pages.
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php if ( have_posts() ) : ?>

			<header class="archive-header">
				<?php echo "<h1 class='archive-header__title'>" . post_type_archive_title( '', false ) . "</h1>";	?>
			</header><!-- .page-header -->

			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/card-special-offer-archive' ); ?>
			<?php endwhile; // Main loop. ?>
		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; // End have_posts() check. ?>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
get_footer();


