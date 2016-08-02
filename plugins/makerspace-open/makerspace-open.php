<?php
/**
 * Plugin Name: MakerSpace Open Status
 * Description: A little plugin to determine whether the space is open or not and who is there
 * Version: 1.0.0
 * Author: Grant Richmond
 * Author URI: https://grant.codes
*/

if ( ! class_exists( 'MakerspaceOpen' ) ) {

	/**
	 * Determines if the makerspace is currently open or shut
	 */
	class MakerspaceOpen {

		/**
		 * The membership level that counts as an active member
		 *
		 * @var integer
		 */
		static $membership_level_id = 1;

		/**
		 * Initialize a bunch of stuff
		 */
		function __construct() {
			if ( ! function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
				return;
			}
			add_action( 'rest_api_init', function () {
				register_rest_route( 'makerspace', '/checkin', array(
					'methods' => array( 'POST', 'GET' ),
					'callback' => array( 'MakerspaceOpen', 'member_checkin' ),
				) );

				register_rest_route( 'makerspace', '/checkout', array(
					'methods' => array( 'POST', 'GET' ),
					'callback' => array( 'MakerspaceOpen', 'member_checkout' ),
				) );

				register_rest_route( 'makerspace', '/members', array(
					'methods' => 'GET',
					'callback' => array( 'MakerspaceOpen', 'get_members' ),
				) );
			} );

			// Profile fields.
			add_action( 'show_user_profile', array( $this, 'profile_fields' ) );
			add_action( 'edit_user_profile', array( $this, 'profile_fields' ) );
			add_action( 'personal_options_update', array( $this, 'save_profile_fields' ) );
			add_action( 'edit_user_profile_update', array( $this, 'save_profile_fields' ) );

			// Auto checkout cron job.
			add_action( 'makerspace_auto_checkout', array( $this, 'auto_checkout' ) );
			add_filter( 'cron_schedules', array( $this, 'auto_checkout_cron_schedule' ) );
			if ( ! wp_next_scheduled( 'makerspace_auto_checkout' ) ) {
				wp_schedule_event( time(), 'makerspace_auto_checkout_schedule', 'makerspace_auto_checkout' );
			}

			// Key saving and authentication.
			add_filter( 'rest_authentication_errors', array( $this, 'key_authentication' ) );
			add_action( 'pmpro_after_change_membership_level', array( $this, 'new_member_key' ) );

			// Admin bar checkin.
			add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar_menu' ), 99 );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		}

		/**
		 * Runs on plugin activation
		 *
		 * @return void
		 */
		function activate() {
			// Add keys for all members.
			$members = static::get_members( true );
			foreach ( $members as $member ) {
				static::generate_user_key( $member->ID );
			}
		}

		/**
		 * Checks in the currently logged in user
		 *
		 * @param  WP_REST_Request $request The rest api object.
		 * @return bool            Checked in or not
		 */
		public static function member_checkin( WP_REST_Request $request ) {
			if ( $user = static::get_rest_user() ) {
				$time = current_time( 'timestamp' );
				add_user_meta( $user->ID, 'makerspace_checkin', $time );
				return $user->ID;
			}
			return false;
		}

		/**
		 * Checks out the currently logged in user
		 *
		 * @param  WP_REST_Request $request The rest api object.
		 * @return bool            Checked in or not
		 */
		public static function member_checkout( WP_REST_Request $request ) {
			if ( $user = static::get_rest_user() ) {
				$time = current_time( 'timestamp' );
				if ( isset( $request['time'] ) && is_int( $request['time'] ) ) {
					$time = $request['time'];
				}
				add_user_meta( $user->ID, 'makerspace_checkout', $time );
				return $user->ID;
			}
			return false;
		}

		/**
		 * Gets members public information
		 *
		 * @param boolean $force_all If true then get all members regardless if they have chosen to be hidden.
		 * @return array   Array of member objects
		 */
		public static function get_members( $force_all = false ) {

			$data = array();
			$users = get_users();

			foreach ( $users as $user ) {
				$membership = pmpro_getMembershipLevelForUser( $user->ID );
				$hide_member = get_user_meta( $user->ID, 'makerspace_hide_from_listings', true );
				if ( ( $membership && $membership->id === static::$membership_level_id ) && ( ! $hide_member || true === $force_all ) ) {
					$member = new StdClass();
					$member->display_name = $user->data->display_name;
					$member->email = $user->data->user_email;
					$member->url = $user->data->user_url;
					$member->registered = $user->data->user_registered;
					$member->id = $user->ID;
					$member->ID = $user->ID;
					$member->checked_in = static::is_member_in( $user->ID );
					$member->last_checkin = static::get_last_checkin( $user->ID );
					$member->last_checkout = static::get_last_checkout( $user->ID );
					$data[] = $member;
				}
			}

			return $data;
		}

		/**
		 * Checks to see if the member is currently checked in
		 *
		 * @param  int $user_id The WP user ID.
		 * @return boolean      In or not
		 */
		public static function is_member_in( $user_id ) {
			$time = current_time( 'timestamp' );
			$last_checkin = static::get_last_checkin( $user_id );
			$last_checkout = static::get_last_checkout( $user_id );
			if ( $last_checkin && ( $last_checkin > $last_checkout || ! $last_checkout ) && $last_checkout < $time ) {
				return true;
			}
			return false;
		}

		/**
		 * Get a users last checkout time
		 *
		 * @param  int $user_id The user ID.
		 * @return string       The last checkout timestamp or false on failure
		 */
		public static function get_last_checkout( $user_id ) {
			$checkouts = get_user_meta( $user_id, 'makerspace_checkout', false );
			if ( is_array( $checkouts ) && count( $checkouts ) > 0 ) {
				return end( $checkouts );
			}
			return false;
		}

		/**
		 * Get a users last checkin time
		 *
		 * @param  int $user_id The user ID.
		 * @return string       The last checkin timestamp or false on failure
		 */
		public static function get_last_checkin( $user_id ) {
			$checkins = get_user_meta( $user_id, 'makerspace_checkin', false );
			if ( is_array( $checkins ) && count( $checkins ) > 0 ) {
				return end( $checkins );
			}
			return false;
		}

		/**
		 * Loop through all members and check them out if they are idling
		 *
		 * @return void
		 */
		function auto_checkout() {
			$time = current_time( 'timestamp' );
			$max_checkin = $time - ( 60 * 60 * 3 );
			$members = static::get_members( true );
			foreach ( $members as $member ) {
				 // If member checked in and not checked out later and checkin was a long time ago then check them out.
				if ( $member->last_checkin && $member->last_checkin > $member->last_checkout && $member->last_checkin < $max_checkin  ) {
					static::member_checkout( $member->ID );
					wp_mail(
						$member->email,
						'[Dundee MakerSpace Notification] You Have Been Automatically Checked Out' ,
						'Beep boop. This is the Dundee MakerSpace website telling you that you\'ve been automatically checked out of the MakerSpace.'
					);
				}
			}
		}

		/**
		 * Filter to add a quicker cron schedule for the auto checkout system
		 *
		 * @param  array $schedules The original schedules.
		 * @return array            The updated schedules
		 */
		public function auto_checkout_cron_schedule( $schedules ) {
			$schedules['makerspace_auto_checkout_schedule'] = array(
				'interval' => 60 * 25,
				'display' => 'Every 25 minutes',
			);
			return $schedules;
		}

		/**
		 * Send your makerspace key as a post or get variable to validate rest requests
		 *
		 * @param  boolean $result Whether the user is already authenticated or not.
		 * @return boolean         True if valid key or already authenticated
		 */
		function key_authentication( $result ) {
			if ( ! $result && static::get_rest_user() ) {
				$result = true;
			}
			return $result;
		}

		/**
		 * Gets user by their makerspace key
		 *
		 * @param  string $key The key for the user.
		 * @return mixed       False on failure, WP_User object on success
		 */
		public static function get_member_by_key( $key ) {
			$users = get_users( array(
				'meta_key' => 'makerspace_key',
				'meta_value' => $key,
			) );
			if ( count( $users ) === 1 ) {
				$user = $users[0];
				$membership = pmpro_getMembershipLevelForUser( $user->ID );
				if ( $membership && $membership->id === static::$membership_level_id ) {
					return $users[0];
				}
			}
			return false;
		}

		/**
		 * Helper function to the get the user currently making an api request
		 *
		 * @return object/boolean  WP_User object or false on failure
		 */
		public static function get_rest_user() {
			if ( strpos( $_SERVER['REQUEST_URI'], 'wp-json/makerspace' ) > -1 ) {
				if ( isset( $_REQUEST['makerspace_key'] ) && ( $user = static::get_member_by_key( $_REQUEST['makerspace_key'] ) ) ) {
					return $user;
				} elseif ( ( $user_id = get_current_user_id() ) && ( $user = get_user_by( 'id', $user_id ) ) ) {
					return $user;
				}
			}
			return false;
		}

		/**
		 * Generates and saves a key for a user
		 *
		 * @param  mixed $user User ID or WP_User object.
		 * @return boolean     True if successfully saved
		 */
		public static function generate_user_key( $user ) {
			if ( is_int( $user ) ) {
				$user = get_user_by( 'id', $user );
			}
			if ( is_object( $user ) ) {
				$login = $user->data->user_nicename;
				$key_generator = $user->ID . $login . uniqid();
				$key = md5( $key_generator );
				return add_user_meta( $user->ID, 'makerspace_key', $key, true );
			}
			return false;
		}

		/**
		 * Adds a key for new members
		 *
		 * @param  int $level_id The membership level ID.
		 * @param  int $user_id  The user ID.
		 * @return void
		 */
		function new_member_key( $level_id, $user_id ) {
			if ( $level_id === static::$membership_level_id ) {
				static::generate_user_key( $user_id );
			}
		}

		/**
		 * Send email notification to all members who want notifications
		 *
		 * @param  string $subject The notification subject.
		 * @param  string $message The notification message.
		 * @return bool            Whether the email was sent successfully or not
		 */
		function send_notification( $subject, $message ) {
			$subject = '[Dundee MakerSpace Notification] ' . $subject;
			$emails = array();
			foreach ( static::get_members() as $member ) {
				if ( ! get_user_meta( $member->ID, 'makerspace_no_checkin_notifications', true ) ) {
					$emails[] = $member->email;
				}
			}
			return wp_mail( $emails, $subject, $message );
		}

		/**
		 * Output fields in the user profile
		 *
		 * @param  object $user WP_User object.
		 * @return void         Echos html
		 */
		function profile_fields( $user ) {
			?>
				<h3>MakerSpace Options</h3>

				<table class="form-table">
					<tr>
						<th><label for="makerspace_hide_from_listings">Hide me from public member listings</label></th>
						<td>
							<input type="checkbox" name="makerspace_hide_from_listings" id="makerspace_hide_from_listings" <?php checked( get_user_meta( $user->ID, 'makerspace_hide_from_listings', true ) ); ?>>
						</td>
					</tr>
					<tr>
						<th><label for="makerspace_no_checkin_notifications">Exclude me from notifications</label></th>
						<td>
							<input type="checkbox" name="makerspace_no_checkin_notifications" id="makerspace_no_checkin_notifications" <?php checked( get_user_meta( $user->ID, 'makerspace_no_checkin_notifications', true ) ); ?>>
						</td>
					</tr>
					<tr>
						<th>Your REST Key</th>
						<td>
							<p><?php echo get_user_meta( $user->ID, 'makerspace_key', true ); ?></p>
						</td>
					</tr>
				</table>
			<?php
		}

		/**
		 * Saves the user profile fields
		 *
		 * @param  int $user_id The user id.
		 * @return void         Saves to database
		 */
		function save_profile_fields( $user_id ) {
			if ( current_user_can( 'edit_user', $user_id ) ) {
				if ( isset( $_POST['makerspace_hide_from_listings'] ) && ! empty( $_POST['makerspace_hide_from_listings'] ) ) {
					add_user_meta( $user_id, 'makerspace_hide_from_listings', 1 );
				} else {
					delete_user_meta( $user_id, 'makerspace_hide_from_listings' );
				}
				if ( isset( $_POST['makerspace_no_checkin_notifications'] ) && ! empty( $_POST['makerspace_no_checkin_notifications'] ) ) {
					add_user_meta( $user_id, 'makerspace_no_checkin_notifications', 1 );
				} else {
					delete_user_meta( $user_id, 'makerspace_no_checkin_notifications' );
				}
			}
		}

		/**
		 * Add checkin / checkout button to the user actions menu
		 *
		 * @return void
		 */
		function admin_bar_menu() {
			global $wp_admin_bar;
			if ( $user_id = get_current_user_id() ) {
				$membership = pmpro_getMembershipLevelForUser( $user_id );
				if ( $membership && $membership->id === static::$membership_level_id ) {
					$menu_item = array(
						'parent' => 'user-actions',
						'id' => 'makerspace-checkinout',
						'title' => 'Checkin',
						'href' => '#checkin',
					);
					if ( static::is_member_in( $user_id ) ) {
						$menu_item['title'] = 'Checkout';
						$menu_item['href'] = '#checkout';
					}
					$wp_admin_bar->add_node( $menu_item );
				}
			}
		}

		/**
		 * Setup and enqueue the login script
		 *
		 * @return void
		 */
		function scripts() {
			if ( get_current_user_id() ) {
				wp_register_script( 'makerspace-checkin', plugins_url( '/checkinout.js', __FILE__ ), array( 'jquery' ), false, true );
				wp_localize_script( 'makerspace-checkin', 'MakerspaceCheckinSettings', array(
					'root' => esc_url_raw( rest_url() ),
					'nonce' => wp_create_nonce( 'wp_rest' ),
				) );
				wp_enqueue_script( 'makerspace-checkin' );
			}
		}
	}

	add_action( 'plugins_loaded', function() {
		new MakerspaceOpen;
	});
}

register_activation_hook( __FILE__, array( 'MakerspaceOpen', 'activate' ) );
