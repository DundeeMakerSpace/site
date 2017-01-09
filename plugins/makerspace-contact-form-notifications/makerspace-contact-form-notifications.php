<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Plugin Name: MakerSpace Contact Form Notifications
 * Description: Sends notifications to the slack group on new form entries and reminders about unreplied messages
 * Version: 1.0.0
 * Author: Grant Richmond
 * Author URI: https://grant.codes
 *
 * @package dundee-makerspace
 */
class MakerspaceContactFormNotifications {
	/**
	 * The current instance of this class
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * The time after the initial contact to send notifications in seconds
	 *
	 * @var int
	 */
	protected static $reminder_delay = 60 * 60 * 24 * 4; // 4 days.

	/**
	 * Get the party started
	 */
	function __construct() {
		add_filter( 'ninja_forms_register_actions', array( $this, 'register_actions' ) );
		add_action( 'makerspace_contact_form_reminder', array( $this, 'send_reminders' ) );
		add_filter( 'ninja_forms_menu_ninja-forms_capability', array( $this, 'menu_capability' ) );
		add_filter( 'ninja_forms_admin_submissions_capabilities', array( $this, 'menu_capability' ) );
		if ( ! wp_next_scheduled( 'makerspace_contact_form_reminder' ) ) {
			wp_schedule_event( time(), 'daily', 'makerspace_contact_form_reminder' );
		}
	}

	/**
	 * Register the ninja form actions
	 *
	 * @param  array $actions Original actions.
	 * @return array          Updated actions
	 */
	function register_actions( $actions ) {
		require_once( dirname( __FILE__ ) . '/lib/ninjaforms-slack-action.php' );
		$actions['makerspace-slack-notification'] = new MakerspaceNinjaformsSlackAction();
		return $actions;
	}

	/**
	 * Update the capability required to see the forms menu.
	 *
	 * @param  string $cap The original cap.
	 * @return string      The updated cap
	 */
	function menu_capability( $cap ) {
		return 'delete_posts';
	}

	/**
	 * Sends a message to slack via webhook
	 *
	 * @param  string $name    The bot username.
	 * @param  string $message The message to send.
	 * @param  string $webhook The url of the webhook.
	 * @param  string $channel The channel name to send to.
	 * @return void
	 */
	public static function send_slack_notification( $name, $message, $webhook, $channel = null ) {
		$data = array(
			'username' => $name,
			'text' => $message,
		);
		if ( $channel ) {
			$data['channel'] = $channel;
		}
		wp_remote_post( $webhook, array(
			'body' => array( 'payload' => json_encode( $data ) ),
		) );
	}

	/**
	 * Send reminders for all undeleted submissions.
	 *
	 * @return void
	 */
	function send_reminders() {
		$forms = Ninja_Forms()->form()->get_forms();
		foreach ( $forms as $form ) {
			$form_id = $form->get_id();
			$form = Ninja_Forms()->form( $form_id );
			$actions = $form->get_actions();
			foreach ( $actions as $action ) {
				$action_settings = $action->get_settings();
				$type = $action_settings['type'];
				if ( 'makerspace-slack-notification' === $type ) {
					$submissions = $form->get_subs();
					foreach ( $submissions as $submission ) {
						$post = get_post( $submission->get_id() );
						// TODO: Generate the text of the message again.
						if ( (strtotime( $post->post_date ) + self::$reminder_delay ) < date( 'U' ) ) {
							// Send reminder notification about this message.
							$message = 'There are old messages needing to be delt with:';
							$message .= "\n\n";
							$message .= 'Reply to messages and delete them from the admin at <' . get_admin_url() . 'edit.php?post_status=all&post_type=nf_sub&form_id=' . $form_id . '> to stop receiving reminders.';
							MakerspaceContactFormNotifications::send_slack_notification( $action_settings['name'], $action_settings['text'], $action_settings['webhook'] );
						}
					}
				}
			}
		}
		exit();
	}

	/**
	 * Get an instance of this class
	 *
	 * @return object The class object
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
		apply_filters( $tag, $value );
	}
}

add_action( 'plugins_loaded', array( 'MakerspaceContactFormNotifications', 'get_instance' ) );
