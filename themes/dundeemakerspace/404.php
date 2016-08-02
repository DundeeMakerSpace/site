<?php
/**
 * The 404 template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['title'] = 'Uh oh!';
Timber::render( '404.twig', $context );
