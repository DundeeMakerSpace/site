<?php
/**
 * Basic setup for the WordPress Theme
 *
 * @package dundee-makerspace
 */

/**
 * The main terminally pixelated class
 */
class TerminallyPixelatedBase {

	/**
	 * Let's go!
	 */
	function __construct() {
		$this->add_sidebars();
		$this->add_menus();
		add_action( 'after_setup_theme', array( $this, 'add_support' ) );
		add_action( 'init', array( $this, 'remove_crap' ) );
		add_action( 'init', array( $this, 'editor_style' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
		add_action( 'wp_footer', array( $this, 'google_analytics' ), 100 );
		add_action( 'admin_init', array( $this, 'remove_image_links' ) );
		add_filter( 'timber_context', array( $this, 'timber_context' ) );
		add_filter( 'timber_context', array( $this, 'schema' ) );
		add_filter( 'get_twig', array( $this, 'twig_extensions' ) );
		add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );
		add_action( 'tgmpa_register', array( $this, 'require_plugins' ) );
	}

	/**
	 * Adds theme support for stuff
	 */
	public function add_support() {
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'menus' );
		add_theme_support( 'html5' );
		add_theme_support( 'title-tag' );
		$defaults = array(
			'default-preset' => 'default',
			'default-position-x' => 'center',
			'default-position-y' => 'center',
			'default-size' => 'cover',
			'default-repeat' => 'no-repeat',
			'default-attachment' => 'scroll',
			'default-color' => TPHelpers::get_setting( 'colors' )->black,
			'wp-head-callback' => array( $this, 'custom_background' ),
		);
		add_theme_support( 'custom-background', $defaults );
		add_theme_support( 'custom-logo' );
	}

	/**
	 * Add editor style
	 *
	 * @return void
	 */
	public function editor_style() {
		add_editor_style( TPHelpers::get_theme_resource_uri( '/editor-style.css' ) );
	}

	/**
	 * Filter the excerpt ending string
	 *
	 * @param  string $more The default more string.
	 * @return string       Updated more string
	 */
	public function excerpt_more( $more ) {
		return '&hellip;';
	}

	/**
	 * Removes useless junk
	 *
	 * @return void
	 */
	public function remove_crap() {
		if ( ! is_admin() ) {
			remove_action( 'wp_head', 'feed_links_extra' ); // Display the links to the extra feeds such as category feeds
			remove_action( 'wp_head', 'feed_links' ); // Display the links to the general feeds: Post and Comment Feed
			remove_action( 'wp_head', 'rsd_link' ); // Display the link to the Really Simple Discovery service endpoint, EditURI link
			remove_action( 'wp_head', 'wlwmanifest_link' ); // Display the link to the Windows Live Writer manifest file.
			remove_action( 'wp_head', 'index_rel_link' ); // index link
			remove_action( 'wp_head', 'parent_post_rel_link', 10 ); // prev link
			remove_action( 'wp_head', 'start_post_rel_link', 10 ); // start link
			remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 ); // Display relational links for the posts adjacent to the current post.
			remove_action( 'wp_head', 'wp_generator' ); // Display the XHTML generator that is generated on the wp_head hook, WP version.
		}
	}

	/**
	 * Prevent images linking to themselves by default
	 *
	 * @return void
	 */
	public function remove_image_links() {
		$image_set = get_option( 'image_default_link_type' );

		if ( 'none' !== $image_set ) {
			update_option( 'image_default_link_type', 'none' );
		}
	}

	/**
	 * Enqueue / register styles
	 */
	public function add_styles() {
		TPHelpers::enqueue( 'style.css' );
	}

	/**
	 * Register / enqueue scripts
	 */
	public function add_scripts() {
		TPHelpers::register( 'js/app.js' );
		wp_localize_script( 'js/app.js', 'TerminallyPixelated', TPHelpers::get_setting() );
		wp_enqueue_script( 'js/app.js' );
	}

	/**
	 * Register sidebars
	 */
	public function add_sidebars() {
		// register_sidebar( array(
		// 	'name'          => __( 'Main Sidebar', 'terminally_pixelated' ),
		// 	'id'            => 'main-sidebar',
		// 	'class'         => 'main-sidebar',
		// 	'description'   => 'The main sidbar',
		// 	'before_widget' => '<div id="%1$s" class="widget %2$s">',
		// 	'after_widget'  => '</div>',
		// 	'before_title'  => '<h1 class="widgettitle">',
		// 	'after_title'   => '</h1>',
		// ) );

		$footer_widget_count = TPHelpers::get_setting( 'footer-widgets' );
		for ( $i = 1; $i <= $footer_widget_count; $i++ ) {
			register_sidebar( array(
				'name'          => __( 'Footer Widgets ' . $i, 'terminally_pixelated' ),
				'id'            => 'footer-widgets-' . $i,
				'class'         => 'footer-widgets-' . $i,
				'description'   => 'Footer widgets set ' . $i,
				'before_widget' => '<div id="%1$s" class="footer-widget widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h1 class="widgettitle footer-widgettitle">',
				'after_title'   => '</h1>',
			) );
		}
	}

	/**
	 * Register menus
	 */
	private function add_menus() {
		register_nav_menu( 'main', 'The main site navigation' );
	}

	/**
	 * Add some data to the timber context
	 *
	 * @param  array $context The original timber context.
	 * @return array          The updated timber context
	 */
	public function timber_context( $context ) {

		// Add logo.
		$context['logo'] = get_custom_logo();

		// Add menu.
		$context['main_menu'] = new TimberMenu( 'main' );

		// Add sidebar.
		// $context['main_sidebar'] = Timber::get_widgets( 'main-sidebar' );

		// Add footer widgets.
		if ( $count = TPHelpers::get_setting( 'footer-widgets' ) ) {
			for ( $i = 1; $i <= $count; $i++ ) {
				$context['footer_widgets'][ $i + 1 ] = Timber::get_widgets( 'footer-widgets-' . $i );
			}
		}

		// Add title.
		if ( is_archive() ) {
			$context['title'] = get_the_archive_title();
		} else if ( is_home() ) {
			$context['title'] = get_the_title( get_option( 'page_for_posts', true ) );
		} else if ( is_search() ) {
			$context['title'] = 'Search Results for: ' . get_search_query();
		} else if ( is_singular() ) {
			$context['title'] = get_the_title();
		}

		// Add breadcrumbs.
		if ( function_exists( 'yoast_breadcrumb' ) ) {
			$context['breadcrumbs'] = yoast_breadcrumb( '<nav id="breadcrumbs" class="main-breadcrumbs">','</nav>', false );
		}

		// Add json settings.
		$context['terminally_pixelated'] = TPHelpers::get_setting();

		// Add icon location.
		$context['svg_sprite'] = TPHelpers::get_theme_resource_uri( 'img/symbol/svg/sprite.symbol.svg' );

		// Footer text.
		$footer_text = get_option( 'terminally_pixelated_footer_text' );
		if ( ! $footer_text ) {
			$footer_text = 'Copyright  ' . date( 'Y' ) . ' ' . get_bloginfo( 'name' ) . ' | All Rights Reserved.';
		}
		$context['footer_text'] = $footer_text;

		return $context;
	}

	/**
	 * Add schema.org data to the timber context
	 *
	 * @param  array $context The timber context.
	 * @return array          The timber context with schema data
	 */
	public function schema( $context ) {
		$context['schema'] = array();

		if ( is_single() ) {
			$type = 'Article';
		} else if ( is_author() ) {
			$type = 'ProfilePage';
		} else if ( is_search() ) {
			$type = 'SearchResultsPage';
		} else if ( is_archive() ) {
			$type = 'Blog';
		} else {
			$type = 'WebPage';
		}

		$context['schema']['type'] = $type;

		return $context;
	}

	/**
	 * Adds custom extensions to twig
	 *
	 * @param  object $twig The original twig object.
	 * @return object       Updated twig object
	 */
	function twig_extensions( $twig ) {
		$icon_function = new Twig_SimpleFunction('icon', function ( $context, $icon ) {
			return TPHelpers::icon( $icon, $context['svg_sprite'] );
		}, array( 'needs_context' => true ) );
		$twig->addFunction( $icon_function );
		return $twig;
	}

	function custom_background() {
		$background = set_url_scheme( get_background_image() );
		$color = get_background_color();
		if ( get_theme_support( 'custom-background', 'default-color' ) === $color ) {
			$color = false;
		}

		if ( ! $background && ! $color ) {
			if ( is_customize_preview() ) {
				echo '<style type="text/css" id="custom-background-css"></style>';
			}
			return;
		}

		$button_color = get_option( 'terminally_pixelated_button_color' );

		$color_style = $color ? "background-color: #$color;" : '';
		$image_style = '';

		if ( $background ) {
			$image = ' background-image: url(' . wp_json_encode( $background ) . ');';

			// Background Position.
			$position_x = get_theme_mod( 'background_position_x', get_theme_support( 'custom-background', 'default-position-x' ) );
			$position_y = get_theme_mod( 'background_position_y', get_theme_support( 'custom-background', 'default-position-y' ) );
			if ( ! in_array( $position_x, array( 'left', 'center', 'right' ), true ) ) {
				$position_x = 'center';
			}
			if ( ! in_array( $position_y, array( 'top', 'center', 'bottom' ), true ) ) {
				$position_y = 'center';
			}

			$position = " background-position: $position_x $position_y;";

			// Background Size.
			$size = get_theme_mod( 'background_size', get_theme_support( 'custom-background', 'default-size' ) );
			if ( ! in_array( $size, array( 'auto', 'contain', 'cover' ), true ) ) {
				$size = 'cover';
			}
			$size = " background-size: $size;";

			// Background Repeat.
			$repeat = get_theme_mod( 'background_repeat', get_theme_support( 'custom-background', 'default-repeat' ) );
			if ( ! in_array( $repeat, array( 'repeat-x', 'repeat-y', 'repeat', 'no-repeat' ), true ) ) {
				$repeat = 'no-repeat';
			}
			$repeat = " background-repeat: $repeat;";

			// Background Scroll.
			$attachment = get_theme_mod( 'background_attachment', get_theme_support( 'custom-background', 'default-attachment' ) );
			if ( 'fixed' !== $attachment ) {
				$attachment = 'scroll';
			}
			$attachment = " background-attachment: $attachment;";

			$image_style = $image . $position . $size . $repeat . $attachment;
		}
		$combined_style = $color_style . $image_style;
		?>
			<style type="text/css" id="custom-background-css">
				.main-header-nav-wrap { <?php echo trim( $combined_style ); ?> }
				<?php if ( $color ) : ?>
					.block--banner,
					.footer-map__marker,
					.wishlist__item__costs__bar,
					#tribe-events-bar,
					input[type="radio"]:checked:after,
					.pagination__link:hover,
					.pagination__link:focus,
					.pagination__link:active {
						background-color: #<?php echo $color; ?>;
					}
					.home-cta-button {
						background-color: #<?php echo $color; ?>;
						border-color: #<?php echo $color; ?>;
					}
					a,
					.footer-widgets a:hover,
					.footer-widgets a:focus,
					.footer-widgets a:active {
						color: #<?php echo $color; ?>;
					}
					a:hover,
					a:focus,
					a:active {
						color: <?php echo TPHelpers::color_luminance( $color, 0.7 ); ?>;
					}
				<?php endif; ?>
				<?php if ( $button_color ) : ?>
					button,
					.button,
					input[type=submit],
					input[type=button] {
						background-color: <?php echo $button_color; ?>;
						border-color: <?php echo $button_color; ?>;
					}
					button:hover,
					button:focus,
					button:active,
					.button:hover,
					.button:focus,
					.button:active,
					input[type=submit]:hover,
					input[type=submit]:focus,
					input[type=submit]:active,
					input[type=button]:hover,
					input[type=button]:focus,
					input[type=button]:active {
						background-color: <?php echo TPHelpers::color_luminance( $button_color, -0.3 ); ?>;
						border-color: <?php echo TPHelpers::color_luminance( $button_color, -0.3 ); ?>;
					}
				<?php endif; ?>
			</style>
		<?php
	}

	/**
	 * Output google analytics script tag
	 *
	 * @return void
	 */
	public function google_analytics() {
		if ( $ga_id = get_option( 'terminally_pixelated_googleanalytics' ) ) : ?>
			<script>
				(function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
				function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
				e=o.createElement(i);r=o.getElementsByTagName(i)[0];
				e.src='//www.google-analytics.com/analytics.js';
				r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
				ga('create','<?php echo $ga_id; ?>');ga('send','pageview');
			</script>
		<?php endif;
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
		);
		tgmpa( $plugins );
	}
}
