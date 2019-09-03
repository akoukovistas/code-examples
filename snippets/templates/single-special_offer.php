<?php
/**
 * The template for displaying single special offers.
 */

get_header(); ?>
	<main class="main-container">
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class( 'main-content' ) ?> id="post-<?php the_ID(); ?>">
				<div class="entry-content">
					<?php
					the_post_thumbnail();
					the_content();
					if ( !empty ( get_field( 'ed_special_offer_end_date' ) ) ) {
						echo sprintf( '<p>Offer available until: %s </p>', get_field( 'ed_special_offer_end_date' ) );
					}
					?>
				</div>
				<?php get_template_part( 'template-parts/card-special-offer-single' ); ?>
			</article>
		<?php endwhile; ?>
	</main>
<?php get_footer();
