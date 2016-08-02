<?php
/**
 * Template Name: Wishlist
 * The wishlist template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['post'] = Timber::get_post();
$context['title'] = false;
Timber::render( 'wishlist.twig', $context );
