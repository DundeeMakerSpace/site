<?php
/**
 * Template name: Login Page
 * The login page template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['post'] = Timber::get_post();

if ( get_current_user_id() ) {
	$context['logged_in'] = true;
}

Timber::render( 'login.twig', $context );
