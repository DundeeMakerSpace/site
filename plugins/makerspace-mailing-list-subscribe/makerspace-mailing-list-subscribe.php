<?php
/**
 * Plugin Name: MakerSpace Mailing List Subscribe
 * Description: Adds stuff for subscribing to the mailing list
 * Version: 1.0.0
 * Author: Grant Richmond
 * Author URI: https://grant.codes
 *
 * @package  dundee-makerspace
 */

if ( ! class_exists( 'MakerspaceMailingListSubscribe' ) ) {

	/**
	 * The mailing list class. Provides forms and sends some emails
	 */
	class MakerspaceMailingListSubscribe {

		/**
		 * The mailing list email address
		 *
		 * @var string
		 */
		public static $join_email = 'dundee-makerspace+subscribe@googlegroups.com';

		/**
		 * Add hooks for shortcode
		 */
		function __construct() {
		  	add_shortcode( 'mailing_list_subscribe', array( $this, 'shortcode' ) );
		}

		/**
		 * Shortcode to output the form and handle sending emails
		 *
		 * @param  array $atts Shortcode attibutes. Not used at the moment.
		 * @return string      The form html
		 */
		public function shortcode( $atts ) {
			ob_start();
			$form_submitted = ( isset( $_POST['mailing_list_subscribe'] ) && ! empty( $_POST['mailing_list_subscribe'] ) );
			$error_message = false;

			if ( $form_submitted ) {
				$email = trim( $_POST['mailing_list_subscribe'] );
				if ( is_email( $email ) ) {
					// Valid email address so try sending a message to the mailing list.
					$subscribed = wp_mail( static::$join_email, 'MakerSpace Mailing List', 'Beam me up Scotty!', 'From: ' . $email );
					// Let the admin know about it.
					$admin_email = get_option( 'admin_email' );
					wp_mail( $admin_email, 'New Mailinglist Member', $email . ' signed up for the mailing list' );
					if ( $subscribed ) {
						// Victory!
						?>
						<div class="mailing-list-subscribe">
							<div class="mailing-list-subscribe__message--success">
								<h2>Thank You</h2>
								<p>You will be added to the mailing list shortly.</p>
							</div>
						</div>
						<?php

					} else {
						// Fail!
						$error_message = 'Something went wrong. Please try again later.';
					}
				} else {
					$error_message = 'You need to enter a valid email address';
				}
			}
			if ( ! $form_submitted || $error_message ) {
				?>
					<form action="" method="post" class="mailing-list-subscribe">
						<?php if ( $error_message ) : ?>
							<div class="mailing-list-subscribe__message--error">
								<p><?php echo $error_message; ?></p>
							</div>
						<?php endif; ?>
						<label for="mailing_list_subscribe" class="mailing-list-subscribe__label">Email</label>
						<input type="email" placeholder="maker@example.com" name="mailing_list_subscribe" id="mailing_list_subscribe" class="mailing-list-subscribe__input">
						<button type="submit" class="mailing-list-subscribe__button">Subscribe</button>
					</form>
				<?php
			}

			return ob_get_clean();
		}
	}

	new MakerspaceMailingListSubscribe;
}
