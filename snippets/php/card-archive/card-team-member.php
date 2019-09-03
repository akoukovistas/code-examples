<?php
$aria_label = __( 'Link to ', 'monolith' ) . esc_html( $team_member['name'] ) . ', ' . esc_html( $team_member['role'] );
?>
<a
	<?php post_class( 'card card--team-member' ); ?>

	 href="<?php the_permalink(); ?>"
	 aria-label="<?php echo $aria_label; ?>"

>
	<div class="card__image">
		<?php the_post_thumbnail( 'medium' ); ?>
	</div>
	<div class="card__inner">
		<div class="card__content">
			<h2 class="card__title"><?php echo esc_html( $team_member['name'] ); ?></h2>
			<div class="card__role">
				<?php echo esc_html( $team_member['role'] ); ?>
			</div>
			<ul class="card__department">
				<?php foreach ( $team_member['department'] as $department ) { ?>
					<li class="card__department-item"><?php echo esc_html( $department ); ?></li>
				<?php } ?>
			</ul>
		</div>
	</div>
</a>
