<?php
/**
 * Template name: Page Builder
 * The page builder template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['post'] = Timber::get_post();
if ( ! is_singular() ) {
	$context['is_archive'] = true;
	$context['pagination'] = Timber::get_pagination();
}
$context['title'] = false;

if ( get_field( 'hide_title' ) ) {
	$context['hide_title'] = true;
}

Timber::render( 'builder.twig', $context );
