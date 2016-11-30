<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Custom ninjaforms action
 *
 * @package dundee-makerspace
*/
if ( class_exists( 'NF_Abstracts_Controller' ) ) {
	class MakerspaceNinjaformsSlackAction extends NF_Abstracts_Action {
		/**
		 * @var string
		 */
		protected $_name = 'makerspace-slack-notification';

		/**
		 * Get the party started
		 */
		function __construct() {
			parent::__construct();
			$this->_nicename = __( 'Slack Reminder', 'makerspace-contact-form-notifications' );

			$this->_settings['makerspace_contact_webhook_name'] = array(
				'name' => 'name',
				'type' => 'textbox',
				'group' => 'primary',
				'label' => __( 'Name', 'makerspace-contact-form-notifications' ),
				'placeholder' => '',
				'value' => '',
				'width' => 'full',
				'use_merge_tags' => false,
			);

			$this->_settings['makerspace_contact_webhook_url'] = array(
				'name' => 'webhook',
				'type' => 'textbox',
				'group' => 'primary',
				'label' => __( 'Webhook', 'makerspace-contact-form-notifications' ),
				'placeholder' => '',
				'value' => '',
				'width' => 'full',
				'use_merge_tags' => false,
			);

			$this->_settings['makerspace_contact_webhook_text'] = array(
				'name' => 'text',
				'type' => 'textarea',
				'group' => 'primary',
				'label' => __( 'Text', 'makerspace-contact-form-notifications' ),
				'placeholder' => '',
				'value' => '',
				'width' => 'full',
				'use_merge_tags' => true,
			);

			$this->_settings['makerspace_contact_webhook_channel'] = array(
				'name' => 'channel',
				'type' => 'textbox',
				'group' => 'primary',
				'label' => __( 'Channel', 'makerspace-contact-form-notifications' ),
				'placeholder' => '',
				'value' => '',
				'width' => 'full',
				'use_merge_tags' => false,
			);
		}

		/**
		 * Run when action saved
		 *
		 * @param  array $action_settings The action settings.
		 * @return void
		 */
		public function save( $action_settings ) {

		}

		/**
		 * Process the form submission and run the action
		 *
		 * @param  array $action_settings The action settings.
		 * @param  int   $form_id         The form id.
		 * @param  array $data            The form data.
		 * @return array                  Updated data
		 */
		public function process( $action_settings, $form_id, $data ) {
			$message = $action_settings['text'];
			$message .= "\n\n";
			$message .= 'Reply to this message and delete it from the admin at <' . get_admin_url() . 'edit.php?post_status=all&post_type=nf_sub&form_id=' . $form_id . '> to stop receiving reminders.';
			MakerspaceContactFormNotifications::send_slack_notification( $action_settings['name'], $action_settings['text'], $action_settings['webhook'] ) ;
			return $data;
		}
	}
}
