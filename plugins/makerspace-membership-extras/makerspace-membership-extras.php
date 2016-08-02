<?php
/**
 * Plugin Name: MakerSpace Membership Extras
 * Description: Adds and fixes functionality in Paid Memberships Pros for MakerSpace use
 * Version: 1.0.0
 * Author: Grant Richmond
 * Author URI: https://grant.codes
 *
 * @package  dundee-makerspace
 */

if ( ! class_exists( 'MakerspaceMembershipExtras' ) ) {

	/**
	 * Class to tweak some settings with the membership plugin
	 */
	class MakerspaceMembershipExtras {

		/**
		 * Add filters, hooks and shortcodes
		 */
		function __construct() {
		   	add_filter( 'pmpro_default_country', array( $this, 'default_country' ) );
		   	add_action( 'admin_init', array( $this, 'add_member_caps' ) );
		   	// This shortcode doesn't work so let's add our own one.
		   	if ( ! shortcode_exists( 'pmpro_levels' ) ) {
			  	add_shortcode( 'pmpro_levels', array( $this, 'pmpro_levels_fix' ) );
		   	}
		}

		/**
		 * Sets default country to Great Britain
		 *
		 * @param  string $default Default country code.
		 * @return string          Desired country code
		 */
		function default_country( $default ) {
			return 'GB';
		}

		/**
		 * Allows authors to list site users
		 */
		function add_member_caps() {
			$roles = array( 'author', 'editor', 'administrator' );
			foreach ( $roles as $role ) {
				$role = get_role( $role );
				$role->add_cap( 'list_users' );
				$role->add_cap( 'makerspace_resource' );
			}
		}

		/**
		 * Hack to fix the pmpro_levels shortcode
		 *
		 * @return string the shortcode html
		 */
		function pmpro_levels_fix() {
			if ( defined( 'PMPRO_DIR' ) ) {
				$view = PMPRO_DIR . '/pages/levels.php';
				ob_start();
				include( $view );
				return ob_get_clean();
			}
		}
	}

	new MakerspaceMembershipExtras;
}
