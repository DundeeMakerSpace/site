<?php
/**
 * MakerSpace Rest API
 *
 * @package dundee-makerspace
 */

if ( ! class_exists( 'MakerspaceRestApi' ) ) {

	/**
	 * The makerspace rest api class
	 */
	class MakerspaceRestApi {

		/**
		 * The rest endpoint
		 *
		 * @var string
		 */
		public static $endpoint = 'makerspace/v1';

		/**
		 * Add hooks
		 *
		 * @return void
		 */
		function __construct() {
			add_action( 'rest_api_init', array( $this, 'routes' ) );
		}

		/**
		 * Add the rest routes
		 *
		 * @return void
		 */
		function routes() {
			register_rest_route( static::$endpoint, '/spaceapi', array(
				'methods' => 'GET',
				'callback' => array( $this, 'spaceapi' ),
			) );
		}

		function spaceapi( WP_REST_Request $request ) {
			// $parameters = $request->get_params();
			$data = array(
				'api' => '0.13',
				'space' => get_bloginfo( 'name' ),
				'url' => get_bloginfo( 'url' ),
				'location' => array(
					'address' => get_option( 'terminally_pixelated_location_address' ),
					'lat' => (float) get_option( 'terminally_pixelated_location_latitude' ),
					'lon' => (float) get_option( 'terminally_pixelated_location_longitude' ),
				),
				'contact' => array(
					'email' => get_bloginfo( 'admin_email' ),
				),
				'issue_report_channels' => array(
					'email',
				),
				'state' => array(
					'open' => false,
				),
				'feeds' => array(
					'blog' => array(
						'type' => 'application/rss+xml',
						'url' => get_bloginfo( 'rss2_url' ),
					),
				),
			);

			// Get logo.
			if ( $custom_logo_id = get_theme_mod( 'custom_logo' ) ) {
				$data['logo'] = wp_get_attachment_url( $custom_logo_id );
			}

			// Get contact info.
			if ( function_exists( 'get_dev_profile_links' ) ) {
				$links = get_dev_profile_links();
				if ( isset( $links['facebook'] ) ) {
					$data['contact']['facebook'] = $links['facebook']['url'];
				}
				if ( isset( $links['twitter'] ) ) {
					if ( $url = wp_parse_url( $links['twitter']['url'] ) ) {
						$data['contact']['twitter'] = '@' . str_replace( '/', '', $url['path'] );
					}
				}
				if ( isset( $links['google'] ) ) {
					$data['contact']['google'] = array( 'plus' => $links['google']['url'] );
				}
				// foreach ( $links as $link ) {
				// 	$data['contact'][ $link['id'] ] = $link['url'];
				// }
			}

			// Get calendar feed.
			if ( function_exists( 'tribe_get_ical_link' ) ) {
				$data['feeds']['calendar'] = array(
					'type' => 'text/calendar',
					'url' => tribe_get_ical_link(),
				);
			}

			// Get projects.
			$projects = new WP_Query( array(
				'post_type' => 'projects',
				'posts_per_page' => -1,
				'fields' => 'ids',
			) );
			if ( $projects->posts ) {
				$data['projects'] = array();
				foreach ( $projects->posts as $project ) {
					$data['projects'][] = get_permalink( $project );
				}
			}
			return apply_filters( 'makerspace_spaceapi', $data );
		}
	}

	add_action( 'plugins_loaded', function() {
		new MakerspaceRestApi;
	});
}

register_activation_hook( __FILE__, array( 'MakerspaceRestApi', 'activate' ) );
