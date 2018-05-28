<?php
/**
 * MakerSpace Mailing List Subscribe
 *
 * @package dundee-makerspace
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
?>

			<!-- Begin MailChimp Signup Form -->
			<link href="//cdn-images.mailchimp.com/embedcode/horizontal-slim-10_7.css" rel="stylesheet" type="text/css">
			<style type="text/css">
				#mc_embed_signup{background:#fff; clear:left; font:14px Helvetica,Arial,sans-serif; width:100%;}
				/* Add your own MailChimp form style overrides in your site stylesheet or in this style block.
				   We recommend moving this block and the preceding CSS link to the HEAD of your HTML file. */
			</style>
			<div id="mc_embed_signup">
			<form action="https://dundeemakerspace.us18.list-manage.com/subscribe/post?u=ef82b755e967411750419f6f0&amp;id=f29a1df118" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
			    <div id="mc_embed_signup_scroll">
				<label for="mce-EMAIL">Subscribe to our mailing list</label>
				<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="email address" required>
			    <!-- real people should not fill this in and expect good things - do not remove this or risk form bot signups-->
			    <div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="b_ef82b755e967411750419f6f0_f29a1df118" tabindex="-1" value=""></div>
			    <div class="clear"><input type="submit" value="Subscribe" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
			    </div>
			</form>
			</div>

			<!--End mc_embed_signup-->

<?php
			return ob_get_clean();
		}
	}

	new MakerspaceMailingListSubscribe;
}
