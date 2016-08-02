<?php
/**
 * The home page template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['post'] = Timber::get_post();
$image = $context['post']->thumbnail();
$context['header_image'] = $image;
$context['is_archive'] = false;
$context['title'] = false;

if ( function_exists( 'tribe_get_events' ) ) {
	$events = tribe_get_events( array(
		'eventDisplay'   => 'list',
		'posts_per_page' => 3,
		'tribe_render_context' => 'widget',
	) );
	$context['next_events'] = Timber::get_posts( $events );
}

Timber::render( 'home.twig', $context );
