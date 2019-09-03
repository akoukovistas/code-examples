<?php
/*
 * Template Name: Home Page
 */

$context = Timber::get_context();
$context['page'] = new TimberPost();
$args = array (
    'post_type' => 'post',
    'posts_per_page' => 3,
    'order' => 'ASC'
);
$context['posts'] = Timber::get_posts($args);
$args = array(
    'post_type' => 'case_study',
    'posts_per_page' => -1
);
$context['caseStudies'] = Timber::get_posts($args);
$templates = array( 'homepage.twig' );
Timber::render( $templates, $context );