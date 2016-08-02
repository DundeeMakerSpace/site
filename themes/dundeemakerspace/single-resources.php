<?php
/**
 * The single resource template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['posts'] = Timber::get_posts();

$context['trainers'] = get_users( array(
	'connected_type' => 'resource_training',
	'connected_items' => $post,
) );

Timber::render( 'index.twig', $context );
