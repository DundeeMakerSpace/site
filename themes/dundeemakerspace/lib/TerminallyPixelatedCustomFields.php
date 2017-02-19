<?php
/**
 * Set up custom fields for the theme using carbon fields
 *
 * @package dundee-makerspace
 */
use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Creates the custom fields
 */
class TerminallyPixelatedCustomFields {

	/**
	 * Let's go!
	 */
	function __construct() {
		add_action( 'carbon_register_fields', array( $this, 'fields' ) );
	}

	/**
	 * Checks to see if the current post is using the provided template
	 *
	 * @param  string $template The template we are looking for.
	 * @return boolean          True if using given template
	 */
	function uses_template( $template ) {
		$post_id = 0;
		if ( ! empty( $_GET['post'] ) ) {
			$post_id = (int) $_GET['post'];
		} elseif ( ! empty( $_POST['post_ID'] ) ) {
			$post_id = (int) $_POST['post_ID'];
		} else {
			return false;
		}
		if ( get_post_meta( $post_id, '_wp_page_template', true ) === $template ) {
			return true;
		}
		return false;
	}

	/**
	 * Create the carbon fields
	 *
	 * @return void
	 */
	function fields() {
		// Page builder fields.
		$template_path = 'template-builder.php';
		if ( $this->uses_template( $template_path ) ) {
			add_action( 'admin_head', function() {
				remove_post_type_support( 'page', 'editor' );
			} );
		}
		Container::make( 'post_meta', 'Page Builder' )
			->show_on_post_type( 'page' )
			->show_on_template( 'template-builder.php' )
			->add_fields( array(
				Field::make( 'checkbox', 'crb_hide_title' ),
				Field::make( 'complex', 'crb_page_builder_blocks' )
					->set_min( 1 )
					// ->set_layout( 'tabbed-vertical' )
					->setup_labels(array(
						'plural_name' => 'Blocks',
						'singular_name' => 'Block',
					) )
					->add_fields( 'highlighted_content', array(
						Field::make( 'rich_text', 'content' )->set_required( true ),
					) )
					->add_fields( 'content', array(
						Field::make( 'rich_text', 'content' )->set_required( true ),
					) )
					->add_fields( 'banner', array(
						Field::make( 'text', 'title' ),
						Field::make( 'rich_text', 'content' )->set_required( true ),
					) )
					->add_fields( '2_columns', array(
						Field::make( 'complex', 'columns' )
							->set_required( true )
							->set_min( 2 )
							->set_max( 2 )
							->set_layout( 'tabbed-horizontal' )
							->add_fields( 'column', array(
								Field::make( 'rich_text', 'content' )->set_required( true ),
							) )
					) )
					->add_fields( '3_columns', array(
						Field::make( 'complex', 'columns' )
							->set_required( true )
							->set_min( 3 )
							->set_max( 3 )
							->set_layout( 'tabbed-horizontal' )
							->add_fields( 'column', array(
								Field::make( 'rich_text', 'content' )->set_required( true ),
							) )
					) )
					->add_fields( 'seperator', array() ),
			) );

		// Homepage fields.
		Container::make( 'post_meta', 'Home Content' )
			->show_on_post_type( 'page' )
			->show_on_page( (int) get_option( 'page_on_front' ) )
			->add_fields( array(
				Field::make( 'text', 'home_banner_title' ),
				Field::make( 'textarea', 'home_banner_content' ),
				Field::make( 'relationship', 'home_membership_page' )
					->set_post_type( 'page' )
					->set_max( 1 ),
			) );
	}
}
