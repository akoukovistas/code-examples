<?php
/**
 * Archive: Team Members (meet-the-team)
 */

get_header();

// Set the title in the header.
$page_title_override   = get_post_field( 'post_title', HADD_PAGE_ID_TEAM_MEMBERS_ARCHIVE );
$page_excerpt_override = get_post_field( 'post_excerpt', HADD_PAGE_ID_TEAM_MEMBERS_ARCHIVE );

require locate_template( 'template-parts/header-standard.php' );

// Team Members Query.
// $paged    = get_query_var( 'paged' ) ?: 1;
$args     = [
	'post_type'      => 'team_members',
	'post_status'    => 'publish',
	'posts_per_page' => -1,
	// 'paged'       => $paged,
];
$wp_query = new WP_Query( $args );

// Page content.
$content = get_post_field( 'post_content', HADD_PAGE_ID_TEAM_MEMBERS_ARCHIVE );

// Card filter title.
$card_filter_group = [
	'id'    => 'team_members',
	'slug'  => 'departments',
	'title' => __( 'Filter by department:', 'haddonstone' ),
];

// Set card filter options ($card_filter_options).
$card_filter_options = [];
foreach ( $wp_query->posts as $post ) {
	$terms = wp_get_post_terms( $post->ID, 'departments' );
	foreach ( $terms as $term ) {
		if ( ! isset( $card_filter_options[ $term->slug ] ) ) {
			$card_filter_options[ $term->slug ] = [
				'slug' => $term->slug,
				'name' => $term->name,
			];
		}
	}
}
?>

<div class="main-container">
	<div class="main-grid">
		<main class="main-content-full-width">
			<div class="grid-x grid-margin-x align-center intro-content">
				<div class="cell small-12 medium-10 large-8 ">
					<?php echo apply_filters( 'the_content', $content ); ?>
				</div>
			</div>
		<?php require locate_template( 'template-parts/card-filter.php' ); ?>
		<?php if ( have_posts() ) : ?>
			<div class="card-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					$team_member = [
						'name' => get_the_title( $post ),
						'role' => get_field( 'hadd_team_member_role', $post ),
					];
					$terms       = get_the_terms( $post, 'departments' );
					foreach ( $terms as $term ) {
						$team_member['department'][] = $term->name;
					};
					?>
					<?php include locate_template( 'template-parts/card-team-member.php' ); ?>
				<?php endwhile; ?>
			</div>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
			<?php
			if ( function_exists( 'foundationpress_pagination' ) ) :
				foundationpress_pagination();
			elseif ( is_paged() ) :
			?>
				<nav id="post-nav">
					<div class="post-previous"><?php next_posts_link( __( '&larr; Older posts', 'foundationpress' ) ); ?></div>
					<div class="post-next"><?php previous_posts_link( __( 'Newer posts &rarr;', 'foundationpress' ) ); ?></div>
				</nav>
			<?php endif; ?>
		</main>
	</div>
</div>

<?php
get_footer();
