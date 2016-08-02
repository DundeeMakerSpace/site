<?php
/**
 * Plugin Name: MakerSpace Projects
 * Description: The project post type for MakerSpace projects
 * Version: 1.0.0
 * Author: Grant Richmond
 * Author URI: https://grant.codes
 *
 * @package  dundee-makerspace
 */

if ( ! class_exists( 'MakerspaceProjects' ) ) {

	/**
	 * The makerspace project post type and functionality
	 */
	class MakerspaceProjects {

		/**
		 * Add hooks and filters
		 */
		function __construct() {
			// Hook into the 'init' action.
			add_action( 'init', array( $this, 'post_type' ), 0 );
			add_action( 'init', array( $this, 'taxonomies' ), 0 );
			// Relationships.
			add_action( 'p2p_init', array( $this, 'relationships' ) );
			// Metaboxes.
			add_action( 'cmb2_admin_init', array( $this, 'metaboxes' ) );
			add_action( 'admin_menu' , array( $this, 'remove_default_metabox' ) );
		}

		/**
		 * Register the custom post type
		 *
		 * @return void
		 */
		function post_type() {

			$labels = array(
				'name'                => _x( 'Projects', 'Post Type General Name', 'makerspace-projects' ),
				'singular_name'       => _x( 'Project', 'Post Type Singular Name', 'makerspace-projects' ),
				'menu_name'           => __( 'Projects', 'makerspace-projects' ),
				'parent_item_colon'   => __( 'Parent Project:', 'makerspace-projects' ),
				'all_items'           => __( 'All Projects', 'makerspace-projects' ),
				'view_item'           => __( 'View Project', 'makerspace-projects' ),
				'add_new_item'        => __( 'Add New Project', 'makerspace-projects' ),
				'add_new'             => __( 'Add Project', 'makerspace-projects' ),
				'edit_item'           => __( 'Edit Project', 'makerspace-projects' ),
				'update_item'         => __( 'Update Project', 'makerspace-projects' ),
				'search_items'        => __( 'Search Project', 'makerspace-projects' ),
				'not_found'           => __( 'Not found', 'makerspace-projects' ),
				'not_found_in_trash'  => __( 'Not found in Trash', 'makerspace-projects' ),
			);
			$args = array(
				'label'               => __( 'projects', 'makerspace-projects' ),
				'description'         => __( 'MakerSpace Projects', 'makerspace-projects' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-lightbulb',
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
			);
			register_post_type( 'projects', $args );

		}

		/**
		 * Register custom taxonomies
		 *
		 * @return void
		 */
		function taxonomies() {
			$labels = array(
					'name'                       => _x( 'Categories', 'Taxonomy General Name', 'makerspace-projects' ),
					'singular_name'              => _x( 'Category', 'Taxonomy Singular Name', 'makerspace-projects' ),
					'menu_name'                  => __( 'Categories', 'makerspace-projects' ),
					'all_items'                  => __( 'All Categories', 'makerspace-projects' ),
					'parent_item'                => __( 'Parent Category', 'makerspace-projects' ),
					'parent_item_colon'          => __( 'Parent Category:', 'makerspace-projects' ),
					'new_item_name'              => __( 'New Category', 'makerspace-projects' ),
					'add_new_item'               => __( 'Add New Category', 'makerspace-projects' ),
					'edit_item'                  => __( 'Edit Category', 'makerspace-projects' ),
					'update_item'                => __( 'Update Category', 'makerspace-projects' ),
					'separate_items_with_commas' => __( 'Separate items with commas', 'makerspace-projects' ),
					'search_items'               => __( 'Search Categories', 'makerspace-projects' ),
					'add_or_remove_items'        => __( 'Add or remove categories', 'makerspace-projects' ),
					'choose_from_most_used'      => __( 'Choose from the most used categories', 'makerspace-projects' ),
					'not_found'                  => __( 'Not Found', 'makerspace-projects' ),
				);
				$args = array(
					'labels'                     => $labels,
					'hierarchical'               => true,
					'public'                     => true,
					'show_ui'                    => true,
					'show_admin_column'          => true,
					'show_in_nav_menus'          => true,
					'show_tagcloud'              => true,
				);
				register_taxonomy( 'project-categories', array( 'projects' ), $args );

				$labels = array(
					'name'                       => _x( 'Statuses', 'Taxonomy General Name', 'makerspace-projects' ),
					'singular_name'              => _x( 'Status', 'Taxonomy Singular Name', 'makerspace-projects' ),
					'menu_name'                  => __( 'Statuses', 'makerspace-projects' ),
					'all_items'                  => __( 'All Statuses', 'makerspace-projects' ),
					'parent_item'                => __( 'Parent Status', 'makerspace-projects' ),
					'parent_item_colon'          => __( 'Parent Status:', 'makerspace-projects' ),
					'new_item_name'              => __( 'New Status', 'makerspace-projects' ),
					'add_new_item'               => __( 'Add New Status', 'makerspace-projects' ),
					'edit_item'                  => __( 'Edit Status', 'makerspace-projects' ),
					'update_item'                => __( 'Update Status', 'makerspace-projects' ),
					'separate_items_with_commas' => __( 'Separate items with commas', 'makerspace-projects' ),
					'search_items'               => __( 'Search Statuses', 'makerspace-projects' ),
					'add_or_remove_items'        => __( 'Add or remove statuses', 'makerspace-projects' ),
					'choose_from_most_used'      => __( 'Choose from the most used statuses', 'makerspace-projects' ),
					'not_found'                  => __( 'Not Found', 'makerspace-projects' ),
				);
				$args = array(
					'labels'                     => $labels,
					'hierarchical'               => false,
					'public'                     => true,
					'show_ui'                    => true,
					'show_admin_column'          => true,
					'show_in_nav_menus'          => true,
					'show_tagcloud'              => true,
				);
				register_taxonomy( 'project-status', array( 'projects' ), $args );
		}

		/**
		 * Set p2p relationships
		 *
		 * @return void
		 */
		function relationships() {
			if ( function_exists( 'p2p_register_connection_type' ) ) {
				p2p_register_connection_type( array(
					'name' => 'project_contributors',
					'from' => 'projects',
					'to' => 'user',
					'title' => array(
						'from' => 'Makers',
						'to' => 'Projects',
					),
				) );
			}
		}

		/**
		 * Remove the default status metabox so it can be replaced with our custom one.
		 *
		 * @return void
		 */
		function remove_default_metabox() {
			remove_meta_box( 'tagsdiv-project-status', 'projects', 'side' );
		}

		/**
		 * The cmb2 metaboxes
		 *
		 * @return void
		 */
		public function metaboxes() {
			$prefix = '_projects_'; // Prefix for all fields.
			$cmb = new_cmb2_box( array(
				'id' => 'project_status',
				'title' => 'Project Status',
				'object_types' => array( 'projects' ),
				'context' => 'side',
				'priority' => 'default',
				'show_names' => false,
			) );

			$cmb->add_field( array(
				'show_names' => false,
				'id' => $prefix . 'project_status',
				'type' => 'taxonomy_select',
				'taxonomy' => 'project-status',
			) );
		}
	}

	new MakerspaceProjects;
}
