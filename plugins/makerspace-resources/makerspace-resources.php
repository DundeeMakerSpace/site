<?php
/**
 * Plugin Name: MakerSpace Resources
 * Description: The Resource post type for MakerSpace resources
 * Version: 1.0.0
 * Author: Grant Richmond
 * Author URI: https://grant.codes
 *
 * @package  dundee-makerspace
 */

if ( ! class_exists( 'MakerspaceResources' ) ) {

	/**
	 * Post type and functionality to manage resources
	 */
	class MakerspaceResources {

		/**
		 * Add hooks and actions
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
			add_action( 'cmb_render_resource_qrcode', array( $this, 'cmb_render_resource_qrcode' ), 10 );
			require( 'makerspace-wishlist.php' );
		}

		/**
		 * Create the resources post type
		 *
		 * @return void
		 */
		function post_type() {

			$labels = array(
				'name'                => _x( 'Resources', 'Post Type General Name', 'makerspace-resources' ),
				'singular_name'       => _x( 'Resource', 'Post Type Singular Name', 'makerspace-resources' ),
				'menu_name'           => __( 'Resources', 'makerspace-resources' ),
				'parent_item_colon'   => __( 'Parent Resource:', 'makerspace-resources' ),
				'all_items'           => __( 'All Resources', 'makerspace-resources' ),
				'view_item'           => __( 'View Resource', 'makerspace-resources' ),
				'add_new_item'        => __( 'Add New Resource', 'makerspace-resources' ),
				'add_new'             => __( 'Add Resource', 'makerspace-resources' ),
				'edit_item'           => __( 'Edit Resource', 'makerspace-resources' ),
				'update_item'         => __( 'Update Resource', 'makerspace-resources' ),
				'search_items'        => __( 'Search Resource', 'makerspace-resources' ),
				'not_found'           => __( 'Not found', 'makerspace-resources' ),
				'not_found_in_trash'  => __( 'Not found in Trash', 'makerspace-resources' ),
			);
			$args = array(
				'label'               => __( 'Resources', 'makerspace-resources' ),
				'description'         => __( 'MakerSpace Resources', 'makerspace-resources' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'menu_icon'           => 'dashicons-clipboard',
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capabilities' => array(
					'edit_post'          => 'makerspace_resource',
					'read_post'          => 'makerspace_resource',
					'delete_post'        => 'makerspace_resource',
					'edit_posts'         => 'makerspace_resource',
					'edit_others_posts'  => 'makerspace_resource',
					'publish_posts'      => 'makerspace_resource',
					'read_private_posts' => 'makerspace_resource',
					'create_posts'       => 'makerspace_resource',
					'delete_posts' => 'makerspace_resource',
					'delete_private_posts' => 'makerspace_resource',
					'delete_published_posts' => 'makerspace_resource',
					'delete_others_posts' => 'makerspace_resource',
					'edit_private_posts' => 'makerspace_resource',
					'edit_published_posts' => 'makerspace_resource',
					'create_posts' => 'makerspace_resource',
				),
			);
			register_post_type( 'resources', $args );

		}

		/**
		 * Register custom taxonomies
		 *
		 * @return void
		 */
		function taxonomies() {
			$labels = array(
				'name'                       => _x( 'Types', 'Taxonomy General Name', 'makerspace-resources' ),
				'singular_name'              => _x( 'Type', 'Taxonomy Singular Name', 'makerspace-resources' ),
				'menu_name'                  => __( 'Resource Types', 'makerspace-resources' ),
				'all_items'                  => __( 'All Types', 'makerspace-resources' ),
				'parent_item'                => __( 'Parent Type', 'makerspace-resources' ),
				'parent_item_colon'          => __( 'Parent Type:', 'makerspace-resources' ),
				'new_item_name'              => __( 'New Type', 'makerspace-resources' ),
				'add_new_item'               => __( 'Add New Type', 'makerspace-resources' ),
				'edit_item'                  => __( 'Edit Type', 'makerspace-resources' ),
				'update_item'                => __( 'Update Type', 'makerspace-resources' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'makerspace-resources' ),
				'search_items'               => __( 'Search Types', 'makerspace-resources' ),
				'add_or_remove_items'        => __( 'Add or remove Types', 'makerspace-resources' ),
				'choose_from_most_used'      => __( 'Choose from the most used Types', 'makerspace-resources' ),
				'not_found'                  => __( 'Not Found', 'makerspace-resources' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => false,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'capabilities' => array(
					'manage_terms' => 'makerspace_resource',
					'edit_terms' => 'makerspace_resource',
					'delete_terms' => 'makerspace_resource',
					'assign_terms' => 'makerspace_resource',
				),
			);
			register_taxonomy( 'resource-types', array( 'resources' ), $args );

			$labels = array(
				'name'                       => _x( 'Locations', 'Taxonomy General Name', 'makerspace-resources' ),
				'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'makerspace-resources' ),
				'menu_name'                  => __( 'Resource Locations', 'makerspace-resources' ),
				'all_items'                  => __( 'All Locations', 'makerspace-resources' ),
				'parent_item'                => __( 'Parent Location', 'makerspace-resources' ),
				'parent_item_colon'          => __( 'Parent Location:', 'makerspace-resources' ),
				'new_item_name'              => __( 'New Location', 'makerspace-resources' ),
				'add_new_item'               => __( 'Add New Location', 'makerspace-resources' ),
				'edit_item'                  => __( 'Edit Location', 'makerspace-resources' ),
				'update_item'                => __( 'Update Location', 'makerspace-resources' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'makerspace-resources' ),
				'search_items'               => __( 'Search Locations', 'makerspace-resources' ),
				'add_or_remove_items'        => __( 'Add or remove locations', 'makerspace-resources' ),
				'choose_from_most_used'      => __( 'Choose from the most used locations', 'makerspace-resources' ),
				'not_found'                  => __( 'Not Found', 'makerspace-resources' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => true,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => true,
				'capabilities' => array(
					'manage_terms' => 'makerspace_resource',
					'edit_terms' => 'makerspace_resource',
					'delete_terms' => 'makerspace_resource',
					'assign_terms' => 'makerspace_resource',
				),
			);
			register_taxonomy( 'resource-locations', array( 'resources' ), $args );
		}

		/**
		 * Set up p2p relationships
		 * @return void
		 */
		function relationships() {
			if ( function_exists( 'p2p_register_connection_type' ) ) {
				p2p_register_connection_type( array(
					'name' => 'resource_training',
					'from' => 'resources',
					'to' => 'user',
					'title' => array(
						'from' => 'Trainers',
						'to' => 'Trainer For',
					),
				) );

				p2p_register_connection_type( array(
					'name' => 'resource_training',
					'from' => 'resources',
					'to' => 'user',
					'title' => array(
						'from' => 'Trained',
						'to' => 'Trained In',
					),
					'fields' => array(
						'time' => array(
							'title' => 'Date',
							'type' => 'date',
						),
					),
					'admin_box' => array(
						'context' => 'advanced',
					),
				) );

				p2p_register_connection_type( array(
					'name' => 'resource_project',
					'from' => 'resources',
					'to' => 'projects',
					'title' => array(
						'from' => 'Projects Used In',
						'to' => 'Resources Used',
					),
					'admin_box' => array(
						'show' => 'to',
					),
				) );
			}
		}

		/**
		 * Remove default custom tax metabox to be replaced with our custom one
		 *
		 * @return void
		 */
		function remove_default_metabox() {
			remove_meta_box( 'tagsdiv-resource-types', 'resources', 'side' );
		}

		/**
		 * Set up the custom meta boxes
		 *
		 * @return void
		 */
		public function metaboxes() {
			$prefix = '_resources_'; // Prefix for all fields.

			$cmb_resource_type = new_cmb2_box( array(
				'id' => 'resource_type',
				'title' => 'Resource Type',
				'object_types' => array( 'resources' ),
				'context' => 'side',
				'priority' => 'default',
				'show_names' => false,
			) );
			$cmb_resource_type->add_field( array(
				'show_names' => false,
				'id' => $prefix . 'resource_type',
				'type' => 'taxonomy_select',
				'taxonomy' => 'resource-types',
			) );

			$cmb_resource_price = new_cmb2_box( array(
				'id' => 'resource_pricing',
				'title' => 'Pricing',
				'object_types' => array( 'resources' ),
				'context' => 'side',
				'priority' => 'default',
				'show_names' => true,
			) );
			$cmb_resource_price->add_field( array(
				'name' => 'Members',
				'show_names' => false,
				'desc' => 'Leave blank for free',
				'id' => $prefix . 'pricing_members',
				'type' => 'text',
				'before' => '£',
			) );
			$cmb_resource_price->add_field( array(
				'name' => 'Non-Members',
				'show_names' => false,
				'desc' => 'Leave blank for free',
				'id' => $prefix . 'pricing_non_members',
				'type' => 'text',
				'before' => '£',
			) );

			$cmb_resource_training = new_cmb2_box( array(
				'id' => 'resource_training',
				'title' => 'Training',
				'object_types' => array( 'resources' ),
				'context' => 'side',
				'priority' => 'default',
				'show_names' => false,
			) );
			$cmb_resource_training->add_field( array(
				'name' => 'Training Available',
				'show_names' => false,
				'id' => $prefix . 'training_available',
				'type' => 'checkbox',
			) );
			$cmb_resource_training->add_field( array(
				'name' => 'Time',
				'show_names' => false,
				'id' => $prefix . 'training_time',
				'type' => 'text',
			) );
			$cmb_resource_training->add_field( array(
				'name' => 'Type',
				'show_names' => false,
				'id' => $prefix . 'training_type',
				'type' => 'select',
				'options' => array(
					'Mandatory' => 'Mandatory',
					'Optional' => 'Optional',
				),
			) );

			$cmb_resource_qrcode = new_cmb2_box( array(
				'id' => 'resource_qrcode',
				'title' => 'QR Code',
				'object_types' => array( 'resources' ),
				'context' => 'side',
				'priority' => 'low',
			) );
			$cmb_resource_qrcode->add_field( array(
				'name' => '',
				'id' => $prefix . 'qrcode',
				'type' => 'resource_qrcode',
			) );
		}

		/**
		 * Gets the directory for the QR codes
		 *
		 * @return string  The path to the upload directory
		 */
		public static function get_qrcode_dir() {
			$upload_dir = wp_upload_dir();
			$dir = $upload_dir['basedir'] . '/makerspace-resources-qrcodes/';

			if ( file_exists( $dir ) ) {
				return $dir;
			}

			if ( mkdir( $dir ) ) {
				return $dir;
			}

			return false;
		}

		/**
		 * Get the QR code path for a post
		 *
		 * @param  integer $id The post_id.
		 * @return string      The path the QR code image
		 */
		public static function get_qrcode_file_path( $id ) {
			if ( $dir = static::get_qrcode_dir() ) {
				return $dir . $id . '.png';
			}
			return '';
		}

		/**
		 * Generate the QR code for a post
		 *
		 * @param  integer $id The post_id.
		 * @return mixed       False on failure
		 */
		public static function generate_qrcode( $id = 0 ) {
			if ( ! $id ) {
				$id = get_the_id();
			}

			if ( ! $id ) {
				return false;
			}

			$url = get_site_url( ) . '?p=' . $id;

			$file = static::get_qrcode_file_path( $id );

			if ( ! $file ) {
				return false;
			}

			if ( file_exists( $file ) ) {
				return 'file exists';
			}

			$qrCode = new Endroid\QrCode\QrCode();
			$qrCode->setText( $url );
			$qrCode->setSize( 300 );
			$qrCode->setPadding( 10 );
			$qrCode->save( $file );
			// chmod( $file, 777/ );

			return file_exists( $file );
		}

		/**
		 * Get the QR code url for a post
		 *
		 * @param  integer $id The post_id.
		 * @return string      The qrcode url
		 */
		public static function get_qrcode( $id = 0 ) {
			if ( ! $id ) {
				$id = get_the_id();
			}
			if ( ! $id ) {
				return false;
			}

			$file = static::get_qrcode_file_path( $id );

			if ( ! $file ) {
				return false;
			}

			if ( ! file_exists( $file ) ) {
				static::generate_qrcode( $id );
			}

			$upload_dir = wp_upload_dir();
			$url = $upload_dir['baseurl'] . '/makerspace-resources-qrcodes/';
			$url .= $id . '.png';

			return $url;
		}

		/**
		 * Callback to render the qrcode image in a custom metabox
		 *
		 * @return void Echos and image tag
		 */
		function cmb_render_resource_qrcode() {
			global $post;
			$qrcode = static::get_qrcode( $post->ID );
			echo '<img style="max-width: 100%; height: auto;" src="' . $qrcode . '" alt="Resource QR Code" />';
		}
	}

	new MakerspaceResources;
}
