<?php
/**
 * The single project template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['post'] = Timber::get_post();

if ( $context['post']->thumbnail() ) {
	$image = $context['post']->thumbnail();
	$context['header_image'] = $image;
}

$context['contributors'] = get_users( array(
	'connected_type' => 'project_contributors',
	'connected_items' => $post,
) );

Timber::render( 'project.twig', $context );
