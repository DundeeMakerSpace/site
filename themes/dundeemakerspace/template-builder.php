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

if ( carbon_get_post_meta( $context['post']->id, 'crb_hide_title', 'checkbox' ) ) {
	$context['hide_title'] = true;
}

$context['blocks'] = carbon_get_post_meta( $context['post']->id, 'crb_page_builder_blocks', 'complex' );

Timber::render( 'builder.twig', $context );
