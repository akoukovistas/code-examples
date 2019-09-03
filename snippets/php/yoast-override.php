<?php
//--------------------------------------------------------
// Overrides for filtered used products
//--------------------------------------------------------

/**
 * Handle custom title for used taxonomy
 */
add_filter( 'dw_tax_archive_heading', function ( $heading ) {
	return dw_get_used_field_override( $heading, 'heading' );
} );

/**
 * Handle custom content for used taxonomy
 */
add_filter( 'dw_tax_archive_content', function ( $content ) {
	return dw_get_used_field_override( $content, 'content' );
} );

/**
 * Yoast title tag override
 */
add_filter( 'wpseo_title', function ( $title ) {
	return dw_get_used_field_override( $title, 'title_tag' );
}, 100 );

/**
 * Yoast metadescription override
 */
add_filter( 'wpseo_metadesc', function ( $meta_desc ) {
	return dw_get_used_field_override( $meta_desc, 'meta_description' );
}, 100 );

/**
 * Yoast canonical URL
 */
add_filter( 'wpseo_canonical', function ( $canonical ) {
	//return dw_get_used_field_override( $canonical, 'canonical_url' );
	if ( has_term( 'used', 'condition' ) ) {
		$canonical   = str_replace( '/condition', '', $canonical );
		$filter_tax  = get_query_var( 'filter_tax' );
		$filter_slug = get_query_var( 'filter_slug' );
		if ( $filter_tax && $filter_slug ) {
			return $canonical . "$filter_tax/$filter_slug/";
		}
	}

	return $canonical;
} );

/**
 * Disable next tag for used process types as these do not currently have definition
 */
add_filter( 'wpseo_next_rel_link', function () {
	if ( has_term( 'used', 'condition' ) ) {
		$filter_tax  = get_query_var( 'filter_tax' );
		$filter_slug = get_query_var( 'filter_slug' );
		if ( $filter_tax && $filter_slug ) {
			return false;
		}
	}
} );

//--------------------------------------------------------
// End overrides for filtered used products
//--------------------------------------------------------