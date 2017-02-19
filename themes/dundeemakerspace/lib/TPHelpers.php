<?php
/**
 * Some useful helper functions
 *
 * @package dundee-makerspace
 */

/**
 * The helper class
 */
class TPHelpers {

	/**
	 * Cache of the json settings file
	 *
	 * @var null/array
	 */
	static $json_settings = null;

	/**
	 * Returns the uri of a theme resource
	 *
	 * @param  string $resource relative resource uri with or without a starting /.
	 * @return string           full resource uri
	 */
	public static function get_theme_resource_uri( $resource ) {
		if ( '/' !== substr( $resource, 0, 1 ) ) {
			$resource = '/' . $resource;
		}
		return get_stylesheet_directory_uri() . $resource;
	}

	/**
	 * A wrapper function to more easily enqueue js and css
	 *
	 * @param  string $resource the location of the file.
	 * @param  array  $deps     the dependencies.
	 * @return void
	 */
	public static function enqueue( $resource, $deps = false ) {
		$uri = static::get_theme_resource_uri( $resource );
		if ( '/' === substr( $resource, 0, 1 ) ) {
			$resource = substr( $resource, 1 );
		}
		if ( strpos( $resource, '.js', strlen( $resource ) - 3 ) !== false ) {
			wp_enqueue_script( $resource, $uri, $deps, false, true );
		} elseif ( strpos( $resource, '.css', strlen( $resource ) - 4 ) !== false ) {
			wp_enqueue_style( $resource, $uri, $deps, false, 'all' );
		}
	}

	/**
	 * A wrapper function to more easily register js and css
	 *
	 * @param  string $resource the location of the file.
	 * @param  array  $deps     the dependencies.
	 * @return void
	 */
	public static function register( $resource, $deps = false ) {
		$uri = static::get_theme_resource_uri( $resource );
		if ( '/' === substr( $resource, 0, 1 ) ) {
			$resource = substr( $resource, 1 );
		}
		if ( strpos( $resource, '.js', strlen( $resource ) - 3 ) !== false ) {
			wp_register_script( $resource, $uri, $deps, false, true );
		} elseif ( strpos( $resource, '.css', strlen( $resource ) - 4 ) !== false ) {
			wp_register_style( $resource, $uri, $deps, false, false );
		}
	}

	/**
	 * Gets a variable from the config.json file
	 *
	 * @param  string $key the variable to retrieve.
	 * @return mixed  the value of the variable
	 */
	public static function get_setting( $key = false ) {
		if ( ! self::$json_settings ) {
			$file = file_get_contents( get_stylesheet_directory() . '/config.json' );
			self::$json_settings = (array) json_decode( $file );
		}
		if ( false === $key ) {
			return self::$json_settings;
		} elseif ( isset( self::$json_settings[ $key ] ) ) {
			return self::$json_settings[ $key ];
		}
		return false;
	}

	/**
	 * Get svg markup for an icon
	 *
	 * @param  string $icon The icon to use.
	 * @param  string $svg  The url of the svg file.
	 * @return string       The svg markup
	 */
	public static function icon( $icon, $svg = false ) {
	    if ( ! $svg ) {
	        $svg = TPHelpers::get_theme_resource_uri( 'img/symbol/svg/sprite.symbol.svg' );
	    }
	    return '<svg class="tp-icon tp-icon--' . $icon . '"><use xlink:href="' . $svg . '#' . $icon . '"></use></svg>';
	}

	/**
	 * Lighten or darkens a given hex color
	 * Thanks to https://gist.github.com/stephenharris/5532899
	 *
	 * @param  string $hex     The hex color (with or without #).
	 * @param  float  $percent The amount to lighten / darken the color. 0.2 = 20% brighter. -0.2 = 20% darker.
	 * @return string          The updated color (with leading #)
	 */
	public static function color_luminance( $hex, $percent ) {
		// Validate hex string.
		$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$new_hex = '#';
		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}
		// Convert to decimal and change luminosity.
		for ( $i = 0; $i < 3; $i++ ) {
			$dec = hexdec( substr( $hex, $i * 2, 2 ) );
			$dec = min( max( 0, $dec + $dec * $percent ), 255 );
			$new_hex .= str_pad( dechex( $dec ) , 2, 0, STR_PAD_LEFT );
		}
		return $new_hex;
	}
}
