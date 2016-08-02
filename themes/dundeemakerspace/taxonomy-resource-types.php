<?php
/**
 * The resource type taxonomy archive template file
 *
 * @package dundee-makerspace
 */

global $wp_query;
$query = $wp_query->query;
$query['posts_per_page'] = -1;

$context = Timber::get_context();
$context['resources'] = Timber::get_posts( $query );
$context['term_description'] = term_description();

Timber::render( 'resource-type.twig', $context );
