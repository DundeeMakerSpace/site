<?php
/**
 * The resources archive template
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();

// Get resource types.
$context['resource_types'] = Timber::get_terms( 'resource-types', array(
	'orderby' => 'count',
	'hide_empty' => true,
) );

Timber::render( 'resources.twig', $context );
