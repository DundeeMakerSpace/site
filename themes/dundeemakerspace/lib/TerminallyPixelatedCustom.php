<?php
/**
 * Add any extra minor functions in here
 *
 * @package dundee-makerspace
 */

/**
 * Custom functionality class
 */
class TerminallyPixelatedCustom extends TerminallyPixelatedBase {

	/**
	 * Let's go!
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Enqueue / register styles
	 */
	public function add_styles() {
		TPHelpers::enqueue( 'style.css' );
		// Remove pmpro plugin css.
		wp_dequeue_style( 'pmpro_frontend' );
	}

	/**
	 * Register / enqueue scripts
	 */
	public function add_scripts() {
		TPHelpers::register( 'js/app.js' );
		$settings = TPHelpers::get_setting();
		$settings['location'] = array(
			'address' => nl2br( get_option( 'terminally_pixelated_location_address' ) ),
			'latitude' => get_option( 'terminally_pixelated_location_latitude' ),
			'longitude' => get_option( 'terminally_pixelated_location_longitude' ),
		);
		wp_localize_script( 'js/app.js', 'TerminallyPixelated', $settings );
		wp_enqueue_script( 'js/app.js' );
	}

	/**
	 * Require plugins for installation using tgmpa plugin activation
	 *
	 * @return void
	 */
	public function require_plugins() {
		$plugins = array(
			array(
				'name'             => 'Timber Library',
				'slug'             => 'timber-library',
				'required'         => true,
				'force_activation' => true,
			),
			array(
				'name'             => 'Paid Memberships Pro',
				'slug'             => 'paid-memberships-pro',
				'required'         => true,
				'force_activation' => true,
			),
			array(
				'name'             => 'The Events Calendar',
				'slug'             => 'the-events-calendar',
				'required'         => true,
				'force_activation' => true,
			),
			array(
				'name'             => 'Posts 2 Posts',
				'slug'             => 'posts-to-posts',
				'required'         => true,
				'force_activation' => true,
			),
		);
		tgmpa( $plugins );
	}
}
