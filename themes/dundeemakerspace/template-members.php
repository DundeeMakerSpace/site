<?php
/**
 * Template name: Members List
 * The members listing template file
 *
 * @package dundee-makerspace
 */

$context = Timber::get_context();
$context['post'] = Timber::get_post();
if ( ! is_singular() ) {
	$context['is_archive'] = true;
	$context['pagination'] = Timber::get_pagination();
}

$members = MakerspaceOpen::get_members();
$member_data = array();

foreach ( $members as $key => $member ) {
	$member->userdata = get_userdata( $member->ID );
	$member->meta = get_user_meta( $member->ID );
	$member->avatar = get_avatar( $member->ID, 300 );
	$member->posts = Timber::get_posts( array(
		'author' => $member->ID,
		'tax_query' => array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'category',
				'field' => 'slug',
				'terms' => array( 'blog' ),
			),
			array(
				'taxonomy' => 'post_format',
				'field' => 'slug',
				'terms' => array( 'post-format-standard' ),
			),
		),
	) );
	$member->projects = Timber::get_posts( array(
		'post_type' => 'projects',
		'connected_type' => 'project_contributors',
		'connected_items' => $member->ID,
		'suppress_filters' => false,
		'nopaging' => true,
	) );

	$member_data[] = $member;
}

shuffle( $member_data );
$context['members'] = $member_data;

Timber::render( 'members.twig', $context );
