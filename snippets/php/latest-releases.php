<?php declare( strict_types=1 );

/**
 * Register new query var to be used with the monthly archives.
 *
 * @param  array  $query_vars
 *
 * @return array the updated $query_vars
 */
function europy_add_query_vars( array $query_vars ): array {

	$query_vars[] = 'archive-month';

	return $query_vars;
}

add_filter( 'query_vars', 'europy_add_query_vars' );

/**
 * If the archive-month has been set, filter the product archive based on the month.
 *
 * @param  WP_Query  $query
 */
function europy_latest_release_query( $query ) {

	// Only do this if the archive-month var has been set and it's a product archive.
	if ( get_query_var( 'archive-month' ) && is_post_type_archive( 'product' ) && $query->is_main_query() && ! $query->is_search() ) {

		// TODO: Confirm that this is a valid date, otherwise abort.
		$query_date = sanitize_text_field( $_GET['archive-month'] );

		// First day of the month.
		$first_day = date( 'Y-m-01', strtotime( $query_date ) );
		// Last day of the month.
		$last_day = date( 'Y-m-t', strtotime( $query_date ) );

		$query->set( 'meta_query', [
			[
				'key'     => 'release_date',
				'value'   => [ $first_day, $last_day ],
				'compare' => 'BETWEEN',
				'type'    => 'DATETIME',
			],
		] );

	}

}

add_filter( 'pre_get_posts', 'europy_latest_release_query', 10 );

/**
 * This is a debug meme so we can confirm counts and dates.
 */
function europy_latest_release_posts() {

	if ( isset( $_GET['archive-month'] ) ) {

		global $wp_query;
		$counter = 0;

		foreach ( $wp_query->posts as $post ) {
			echo $post->post_title;
			echo get_post_meta( $post->ID, 'release_date', true ) . '<br>';
			$counter ++;
		}
		echo $counter;
		die;
	}

}

//add_action( 'template_redirect', 'europy_latest_release_posts');

/**
 * Cheeky little function to get the last 6 months
 *
 * @return array containing the last 6 months
 */
function europy_find_last_six_months(): array {

	// We need the next month first.
	$monthname = date( 'F Y', strtotime( "+1 month" ) );
	$monthslug = str_replace( " ", "-", $monthname );

	$sixmonths[ $monthname ] = $monthslug;

	// Loop through the remaining 5 and add them in a key mapped array.
	for ( $i = 0; $i < 5; $i ++ ) {
		$monthname               = date( 'F Y', strtotime( "-$i month" ) );
		$monthslug               = str_replace( " ", "-", $monthname );
		$sixmonths[ $monthname ] = $monthslug;
	}

	return $sixmonths;
}

/**
 * This builds a menu item that gets appended at the end of the main-navigation-mega menu containing links to archives for the last six months.
 *
 * @param  string  $items  The HTML list content for the menu items.
 *
 * @return string The edited HTML list content for the menu items.
 */
function europy_create_latest_release_menu( string $items ): string {

	// Get our months and piece together the shop url.
	$month_slugs   = europy_find_last_six_months();
	$shop_page_url = wc_get_page_id( 'shop' ) ? get_permalink( wc_get_page_id( 'shop' ) ) : '';

	// Add a heading to the menu and start our submenu.
	$items .= "<li id='menu-item-latest-releases' class='menu-item menu-item-type-custom menu-item-object-custom menu-item-has-children menu-item-latest-releases'><a href='#'>Latest releases</a>";
	$items .= "<ul class='sub-menu'>";

	// Create a menu item for each archive.
	foreach ( $month_slugs as $month => $slug ) {
		$items .= "<li><a href='" . $shop_page_url . "?archive-month=" . $slug . "' class=''>" . $month . "</a></li>";
	}

	// Close the submenu.
	$items .= "</ul>";

	return $items;
}

add_filter( 'wp_nav_menu_main-navigation-mega_items', 'europy_create_latest_release_menu' );

// This is actually the mobile menu.
add_filter( 'wp_nav_menu_footer-menu_items', 'europy_create_latest_release_menu' );

/**
 * We need to take the filters in the monthly archive view into consideration so we are appending the appropriate query string to the filters.
 *
 * @param $link string the URL of each filter item in the widget sidebar.
 *
 * @return string the updated URL.
 */
function europy_append_latest_release_filtering_to_sidebar( $link ) {

	if ( get_query_var( 'archive-month' ) && is_post_type_archive( 'product' ) ) {

		$link .= '&archive-month=' . sanitize_text_field( $_GET['archive-month'] );
	}

	return $link;
}

add_filter( 'woocommerce_layered_nav_link', 'europy_append_latest_release_filtering_to_sidebar', 10 );
