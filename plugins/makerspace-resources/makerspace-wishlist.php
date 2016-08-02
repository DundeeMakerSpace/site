<?php
/**
 * The makerspace wishlist functionality
 *
 * @package  dundee-makerspace
 */

if ( ! class_exists( 'MakerspaceWishlist' ) ) {

	/**
	 * Makerspace wishlist class
	 */
	class MakerspaceWishlist {
		/**
		 * The post type to use for the wishlist
		 *
		 * @var string
		 */
		protected static $post_type = 'resources';

		/**
		 * Add hooks and shortcode
		 */
		function __construct() {
			add_shortcode( 'makerspace_wishlist', array( $this, 'shortcode' ) );
			add_action( 'init', array( $this, 'post_statuses' ) );
			add_action( 'wp', array( $this, 'watch_actions' ) );
			add_action( 'post_submitbox_misc_actions', array( $this, 'add_to_post_status_dropdown' ) );
		}

		/**
		 * Sets up the wanted post status for wishlist items
		 *
		 * @return void
		 */
		function post_statuses() {
			register_post_status( 'wanted', array(
				'label'                     => _x( 'Wanted', 'post' ),
				'public'                    => true,
				'exclude_from_search'       => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'Wanted <span class="count">(%s)</span>', 'Wanted <span class="count">(%s)</span>' ),
			) );
		}

		/**
		 * Add wanted post status to status dropdown
		 */
		function add_to_post_status_dropdown() {
			?>
				<script>
					jQuery(document).ready(function($){
						$("select#post_status").append("<option value=\"wanted\" <?php selected( 'wanted', $post->post_status ); ?>>Wanted</option>");
					});
				</script>
			<?php
		}


		/**
		 * Watches for request variables that signify actions to perform
		 *
		 * @return void
		 */
		function watch_actions() {

			if ( isset( $_REQUEST['wishlist_action'] ) && ! empty( $_REQUEST['wishlist_action'] ) ) {
				$action = $_REQUEST['wishlist_action'];

				switch ( $action ) {
					case 'add_wish' : {

						if ( isset( $_REQUEST['title'], $_REQUEST['description'], $_REQUEST['price'], $_REQUEST['user_name'], $_REQUEST['user_email'] ) ) {
							$item = array(
								'title' => $_REQUEST['title'],
								'description' => $_REQUEST['description'],
								'price' => $_REQUEST['price'],
							);
							if ( isset( $_REQUEST['link'] ) ) {
								$item['link'] = $_REQUEST['link'];
							}
							$user = wp_get_current_user();
							if ( ! $user ) {
								$user = array(
									'name' => $_REQUEST['user_name'],
									'email' => $_REQUEST['user_email'],
								);
							}
							static::add_wish( $item, $user );
							static::redirect_to_wishlist();
						}

						break;

					}

					case 'add_comment' : {

						if ( isset( $_REQUEST['comment_comment'], $_REQUEST['comment_name'], $_REQUEST['item_id'] ) ) {
							static::add_comment( $_REQUEST['comment_comment'], $_REQUEST['comment_name'], $_REQUEST['item_id'] );
							static::redirect_to_wishlist();
						}

						break;

					}

					case 'add_pledge' : {

						if ( isset( $_REQUEST['pledge_amount'], $_REQUEST['pledge_email'], $_REQUEST['pledge_name'], $_REQUEST['item_id'] ) ) {
							static::add_pledge( $_REQUEST['pledge_name'], $_REQUEST['pledge_email'], $_REQUEST['pledge_amount'], $_REQUEST['item_id'] );
							static::redirect_to_wishlist();
						}

						break;

					}

					case 'vote_up' : {

						if ( isset( $_REQUEST['item_id'] ) && ! empty( $_REQUEST['item_id'] ) ) {
							$item_id = $_REQUEST['item_id'];
							if ( static::vote_up( $item_id ) ) {
								static::notification( 'Your vote has been cast!', 'success' );
							} else {
								static::notification( 'There was an error adding your vote', 'error' );
							}
							static::redirect_to_wishlist();
						}
						break;

					}
				}
			}

		}

		/**
		 * Wrapper to output the wishlist html using a shortcode
		 *
		 * @param  array $atts shortcode attributes.
		 * @return void Echos the shortcode html
		 */
		function shortcode( $atts ) {
			echo static::get_wishlist_html();
		}

		/**
		 * Redirects the user to the wishlist page
		 *
		 * @return void Redirects to the wishlist url
		 */
		static function redirect_to_wishlist() {
			$link = site_url( 'wishlist' ); // TODO: get this in a more reusable manner.
			wp_redirect( $link, 302 );
		}

		/**
		 * Generates the html for the wishlist
		 *
		 * @return string the wishlist html
		 */
		static function get_wishlist_html() {
			$content = static::get_wishlist();
			$user = wp_get_current_user();
			ob_start();
			foreach ( $content as $wish ) :
			?>
				<div class="wishlist__item block">
					<div class="layout-wrapper">
						<h3 class="wishlist__item__title"><?php echo $wish['title']; ?></h3>
						<div class="wishlist__item__costs">
							<span class="wishlist__item__costs__bar" style="width: <?php echo $wish['percentage']; ?>%"></span>
							<span class="wishlist__item__costs__text">&pound;<?php echo $wish['pledged']; ?> / &pound;<?php echo $wish['price']; ?></span>
						</div>
						<p class="wishlist__item__description"><?php echo $wish['description']; ?></p>
						<div class="wishlist__item__actions">
							<?php /*<form action="./submit/" method="post"  class="wishlist__item__upvote-form wishlist__item__actions__action">
								<input type="hidden" name="item_id" <?php echo $wish['id']; ?>>
								<button class="wishlist__item__upvote button--boring" name="wishlist_action" value="vote_up">+1</button>
							</form> */ ?>
							<?php if ( $wish['link'] ) : ?>
								<div class="wishlist__item__actions__action">
									<a target="_blank" href="<?php echo $wish['link']; ?>" class="button button--dark">View Item &raquo;</a>
								</div>
							<?php endif; ?>
							<div class="wishlist__item__actions__action">
								<a href="#pledge-<?php echo $wish['id']; ?>" class="button button--boring">Pledge</a>
							</div>
							<div class="wishlist__item__actions__action">
								<a href="#comment-<?php echo $wish['id']; ?>" class="button button--boring">Comment</a>
							</div>
							<?php if ( $wish['comments'] ): ?>
								<div class="wishlist__item__actions__action">
									<a href="#comments-<?php echo $wish['id']; ?>" class="button button--boring">View Comments (<?php echo count( $wish['comments'] ) ;?>)</a>
								</div>
							<?php endif; ?>
						</div>
						<form action="./submit/" id="pledge-<?php echo $wish['id']; ?>" method="post" class="wishlist__item__pledge-form">
							<h4>Pledge money to this item</h4>
							<small>This info is not shown publicly but is used to contact you when the item has been funded</small>
							<input type="hidden" name="item_id" value="<?php echo $wish['id']; ?>">
							<label class="wishlist__label" for="pledge_amount">Pledge Amount</label>
							<input class="wishlist__input" type="number" name="pledge_amount" id="pledge_amount" required>
							<label class="wishlist__label" for="pledge_email">Your Email</label>
							<input class="wishlist__input" type="email" name="pledge_email" id="pledge_email" required <?php if ( $user ) { echo 'value="' . $user->user_email . '"'; } ?>>
							<label class="wishlist__label" for="pledge_name">Your Name</label>
							<input class="wishlist__input" type="text" name="pledge_name" id="pledge_name" required <?php if ( $user ) { echo 'value="' . $user->display_name . '"'; } ?>>
							<button class="wishlist__item__upvote" name="wishlist_action" value="add_pledge">Submit Pledge</button>
						</form>
						<form action="./submit/" method="post" id="comment-<?php echo $wish['id']; ?>" class="wishlist__item__comment-form">
							<h4>Add Comment</h4>
							<input type="hidden" name="item_id" value="<?php echo $wish['id']; ?>">
							<label class="wishlist__label" for="comment_name">Your Name</label>
							<input class="wishlist__input" type="name" name="comment_name" id="comment_name" required <?php if ( $user ) { echo 'value="' . $user->display_name . '"'; } ?>>
							<label class="wishlist__label" for="comment_comment">Your Comment</label>
							<textarea class="wishlist__input" name="comment_comment" id="comment_comment"></textarea>
							<button class="wishlist__item__upvote" name="wishlist_action" value="add_comment">Add Comment</button>
						</form>
						<?php if ( $wish['comments'] ): ?>
							<div class="wishlist__item__comments" id="comments-<?php echo $wish['id']; ?>">
								<h4>Comments</h4>
								<ol class="wishlist__item__comments__list">
									<?php foreach ( $wish['comments'] as $comment ): ?>
										<li class="wishlist__item__comments__comment">
											<h5><?php echo $comment->comment_author; ?></h5>
											<p><?php echo $comment->comment_content; ?></p>
										</li>
									<?php endforeach; ?>
								</ol>
							</div>
						<?php endif; ?>
					</div>
				</div>
			<?php
			endforeach;
			echo static::get_add_wish_form();
			return ob_get_clean();
		}

		/**
		 * Gets a single wish item
		 *
		 * @param  int/object $item The item ID or post object.
		 * @return array       The item
		 */
		static function get_wish( $item ) {
			if ( is_int( $item ) ) {
				$item = get_post( $item );
			}
			if ( $item ) {
				$id = $item->ID;
				$pledged = static::get_pledged_amount( $id );
				$meta = get_post_meta( $id, '' );
				if ( $pledged ) {
					$percentage = $pledged / $meta['price'][0] * 100;
				} else {
					$percentage = 0;
				}
				$pledges = 0;
				if ( isset( $meta['pledges'] ) ) {
					$pledges = $meta['pledges'];
				}
				$wish = array(
					'id' => $id,
					'title' => $item->post_title,
					'description' => $item->post_content,
					'link' => $meta['link'][0],
					'price' => number_format( $meta['price'][0], 2, '.', ',' ),
					'upvotes' => $meta['upvotes'][0],
					'pledges' => $pledges,
					'pledged' => number_format( $pledged, 2, '.', ',' ),
					'comments' => get_comments( array(
						'status' => 'approve',
						'post_id' => $id,
						'order' => 'ASC',
					) ),
					'percentage' => $percentage,
				);
				return $wish;
			}
			return false;
		}

		/**
		 * Gets all wanted items sorted by percentage funded
		 *
		 * @return array Array of single wish items
		 */
		static function get_wishlist() {

			$results = new WP_Query( array(
				'post_type' => static::$post_type,
				'post_status' => 'wanted',
				'nopaging' => true,
				'order' => 'ASC',
				'orderby' => 'title',
			) );

			if ( ! $results ) {
				return false;
			}

			if ( $results = $results->posts ) {
				$items = array();

				foreach ( $results as $item ) {
					$items[] = static::get_wish( $item );
				}

				usort( $items, function( $a, $b ) {
					return ceil( $b['percentage'] - $a['percentage'] );
				} );

				return $items;
			}

			return false;
		}

		/**
		 * Adds a wanted item
		 *
		 * @param array        $item The item to add.
		 * @param array/object $user Array of user details or user object.
		 */
		static function add_wish( $item, $user ) {

			$previous_test = get_page_by_title( $item['title'], 'OBJECT', static::$post_type );

			if ( ! $previous_test ) {

				$post_id = wp_insert_post( array(
					'post_content'   => $item['description'],
					'post_title'     => $item['title'],
					'post_status'    => 'wanted',
					'post_type'      => static::$post_type,
				) );

				$user_email = '';
				$user_name = '';

				if ( is_array( $user ) ) {
					$user_email = $user['email'];
					$user_name = $user['name'];
				} elseif ( is_object( $user ) ) {
					$user_email = $user->data->user_email;
					$user_name = $user->data->display_name;
				}

				add_post_meta( $post_id, 'price', $item['price'], true );
				add_post_meta( $post_id, 'upvotes', array( $user_email ), true );
				add_post_meta( $post_id, 'added_by', $user_name, true );
				if ( isset( $item['link'] ) ) {
					add_post_meta( $post_id, 'link', $item['link'], true );
				}
			}

		}

		/**
		 * Adds a vote for an item
		 *
		 * @param  int $item_id The item ID.
		 * @return bool          The result
		 */
		static function vote_up( $item_id ) {
			$current_votes = get_post_meta( $item_id, 'votes', true );
			$new_votes = $current_votes++;
			return update_post_meta( $item_id, 'votes', $new_votes, $current_votes );
		}

		/**
		 * Adds a monetary pledge to an item
		 *
		 * @param string $name    The name of the item.
		 * @param string $email   The email address of the pledger.
		 * @param float  $amount  The amount they are donating.
		 * @param int    $item_id The ID of the item.
		 * @return bool The result
		 */
		static function add_pledge( $name, $email, $amount, $item_id ) {
			if ( is_numeric( $amount ) ) {
				$pledge = array(
					'name' => $name,
					'email' => $email,
					'amount' => $amount,
				);
				$result = add_post_meta( $item_id, 'pledges', $pledge, false );
				$item = static::get_wish( $item_id );
				if ( $item['percentage'] >= 100 ) {
					// Woo hoo! We can buy this.
					static::send_success_emails( $item );
				}
				return $result;
			}
			return false;
		}

		/**
		 * Calculates the total amount pledged to an item
		 *
		 * @param  int $item_id Item ID.
		 * @return float          The total pledge amount
		 */
		static function get_pledged_amount( $item_id ) {
			$pledges = get_post_meta( $item_id, 'pledges' );
			$amount = 0;
			foreach ( $pledges as $pledge ) {
				$amount = $amount + $pledge['amount'];
			}
			return $amount;
		}

		/**
		 * Adds a comment to an item
		 *
		 * @param string $comment The comment content.
		 * @param string $name    The commenter name.
		 * @param int    $item_id The item ID.
		 * @return boolean Result of the wp_insert_comment function
		 */
		static function add_comment( $comment, $name, $item_id ) {

			$time = current_time( 'mysql' );

			return wp_insert_comment( array(
				'comment_post_ID' => $item_id,
				'comment_author' => $name,
				'comment_content' => $comment,
				'comment_date' => $time,
			) );
		}

		/**
		 * Gets the html for the add wish form
		 *
		 * @return string form html
		 */
		static function get_add_wish_form() {
			ob_start(); ?>

			<form class="block block--banner block--last" action="./submit/" method="post">
				<div class="layout-wrapper">
					<h3>Add an item to the wishlist</h3>
					<!-- <input type="hidden" name="wishlist_action" value="add_wish"> -->

					<fieldset>
						<!-- <legend>Item info</legend> -->
						<label class="wishlist__label" for="title">Title</label>
						<input class="wishlist__input" type="text" name="title" required>

						<label class="wishlist__label" for="price">Price</label>
						<input class="wishlist__input" type="number" name="price" required>

						<label class="wishlist__label" for="link">Link</label>
						<input class="wishlist__input" type="url" name="link">

						<label class="wishlist__label" for="description">Description</label>
						<textarea class="wishlist__input" name="description" required></textarea>
					</fieldset>
					<?php if ( $user = wp_get_current_user() ) : ?>
						<input type="hidden" name="user_name" value="<?php echo $user->data->display_name; ?>">
						<input type="hidden" name="user_email" value="<?php echo $user->data->user_email; ?>">
					<?php else : ?>
						<fieldset>
							<legend>Your info</legend>
							<label class="wishlist__label" for="user_name">Name (this is public)</label>
							<input class="wishlist__input" type="text" name="user_name" required>

							<label class="wishlist__label" for="user_email">Email (this is private)</label>
							<input class="wishlist__input" type="email" name="user_email" required>
						</fieldset>
					<?php endif; ?>
					<button type="submit" name="wishlist_action" value="add_wish">Add Item</button>
				</div>
			</form>

			<?php return ob_get_clean();
		}

		/**
		 * This should send notifications but does nothing for now
		 *
		 * @param  string $message The message text.
		 * @param  string $type    The message type.
		 * @return void
		 */
		static function notification( $message, $type = 'success' ) {

		}

		/**
		 * Wrapper to send success emails
		 *
		 * @param  object/int $item ID or post object of the item.
		 * @return bool       Success at sending to admin
		 */
		static function send_success_emails( $item ) {
			$pledge_emails = static::send_success_email_pledges( $item );
			return static::send_success_email_admin( $item, $pledge_emails );
		}

		/**
		 * Sends an email to the admin email with details of pledgers and email sending results
		 *
		 * @param  int/object  $item                 ID or post object of the item.
		 * @param  bool/string $pledge_emails_result The status of emails sent to pledgers.
		 * @return bool                       Email sent or not
		 */
		static function send_success_email_admin( $item, $pledge_emails_result ) {
			if ( $item = static::get_wish( $item ) ) {
				$email = get_bloginfo( 'admin_email' );
				$subject = '[' . get_bloginfo( 'name' ) . ' Wishlist] ' . $item['title'] . ' Has Been Funded!';
				$content = 'The item: ' . $item['title'] . ' has been funded by the following people:';
				foreach ( $item['pledges'] as $pledge ) {
					$content .= "\n\r" . $pledge['name'] . ' - ' . $pledge['email'] . ' - Amount: ' . $pledge['amount'];
				}
				if ( true === $pledge_emails_result ) {
					$content .= "\n\r\n\rAll emails to pledgers were sent successfully";
				} else {
					$content .= "\n\r\n\r" . $pledge_emails_result;
				}
				return wp_mail( $email, $subject, $content );
			}
			return false;
		}

		/**
		 * Sends emails to everyone who pledged to the item with payment details
		 *
		 * @param  id/object $item Item ID or post object.
		 * @return bool/string       true if all successfull, message string if there are errors
		 */
		static function send_success_email_pledges( $item ) {
			if ( $item = static::get_wish( $item ) ) {
				$result = true;
				$subject = '[' . get_bloginfo( 'name' ) . ' Wishlist] ' . $item['title'] . ' Has Been Funded!';

				foreach ( $item['pledges'] as $pledge ) {
					$email = $pledge['email'];
					$content = 'Hello ' . $pledge['name'] . ',';
					$content .= "\n\rThanks to your very generous pledge we have now reached the amount needed to purchase the item: " . $item['title'];
					$content .= "\n\rIf you are still able to donate the money you promised please send it to the following bank details:";
					$content .= "\n\r\n\rDundee MakerSpace\n\rSort Code: \n\rAccount Number:";
					$content .= "\n\r\n\rOnce you have done this or if there is a problem please email info@dundeemakerspace.co.uk to let us know you have donated your pledged amount.";
					$content .= "\n\rAs soon as all pledges have been collected we will purchase the item.";
					$content .= "\n\r\n\rThanks, \n\rDundee MakerSpace";
					if ( ! wp_mail( $email, $subject, $content ) ) {
						if ( true === $result ) {
							$result = '';
						}
						$result .= 'Error sending email to ' . $email . '. ';
					}
				}

				return $result;
			}
			return false;
		}
	}

	new MakerspaceWishlist;

}
