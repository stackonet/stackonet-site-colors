<?php

// If this file is called directly, abort.
defined( 'ABSPATH' ) || die;

class StackonetSiteColorsFrontend {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			add_action( 'wp_head', [ self::$instance, 'customize_colors' ], 5 );
		}

		return self::$instance;
	}

	/**
	 * Stackonet inline color system
	 */
	public function customize_colors() {
		$primary         = get_theme_mod( 'shapla_primary_color', '#00d1b2' );
		$primary_variant = static::adjust_color_brightness( $primary, - 25 );
		$on_primary      = static::find_color_invert( $primary );
		list( $r, $g, $b ) = static::find_rgb_color( $primary );
		$primary_alpha = sprintf( "rgba(%s, %s, %s, 0.25)", $r, $g, $b );

		$secondary         = get_theme_mod( 'shapla_secondary_color', '#9c27b0' );
		$secondary         = ! empty( $secondary ) ? $secondary : $primary;
		$secondary_variant = static::adjust_color_brightness( $secondary, - 25 );
		$on_secondary      = static::find_color_invert( $secondary );
		list( $r, $g, $b ) = static::find_rgb_color( $secondary );
		$secondary_alpha = sprintf( "rgba(%s, %s, %s, 0.25)", $r, $g, $b );

		$success    = get_theme_mod( 'shapla_success_color', '#48c774' );
		$on_success = static::find_color_invert( $success );
		list( $r, $g, $b ) = static::find_rgb_color( $success );
		$success_alpha = sprintf( "rgba(%s, %s, %s, 0.25)", $r, $g, $b );

		$error    = get_theme_mod( 'shapla_error_color', '#f14668' );
		$on_error = static::find_color_invert( $error );
		list( $r, $g, $b ) = static::find_rgb_color( $error );
		$error_alpha = sprintf( "rgba(%s, %s, %s, 0.25)", $r, $g, $b );

		$surface    = get_theme_mod( 'shapla_surface_color', '#ffffff' );
		$on_surface = static::find_color_invert( $surface );
		list( $r, $g, $b ) = static::find_rgb_color( $on_surface );

		$text_primary   = sprintf( "rgba(%s, %s, %s, 0.87)", $r, $g, $b );
		$text_secondary = sprintf( "rgba(%s, %s, %s, 0.54)", $r, $g, $b );
		$text_hint      = sprintf( "rgba(%s, %s, %s, 0.38)", $r, $g, $b );
		$text_disabled  = sprintf( "rgba(%s, %s, %s, 0.38)", $r, $g, $b );
		$text_icon      = sprintf( "rgba(%s, %s, %s, 0.38)", $r, $g, $b );
		?>
        <style type="text/css" id="shapla-colors-system">
            :root {
                --shapla-primary: <?php echo $primary; ?>;
                --shapla-on-primary: <?php echo $on_primary; ?>;
                --shapla-primary-variant: <?php echo $primary_variant; ?>;
                --shapla-primary-alpha: <?php echo $primary_alpha; ?>;
                --shapla-secondary: <?php echo $secondary; ?>;
                --shapla-on-secondary: <?php echo $on_secondary; ?>;
                --shapla-secondary-variant: <?php echo $secondary_variant; ?>;
                --shapla-secondary-alpha: <?php echo $secondary_alpha; ?>;
                --shapla-success: <?php echo $success; ?>;
                --shapla-on-success: <?php echo $on_success; ?>;
                --shapla-success-alpha: <?php echo $success_alpha; ?>;
                --shapla-error: <?php echo $error; ?>;
                --shapla-on-error: <?php echo $on_error; ?>;
                --shapla-error-alpha: <?php echo $error_alpha; ?>;
                --shapla-surface: <?php echo $surface; ?>;
                --shapla-on-surface: <?php echo $on_surface; ?>;
                --shapla-background: <?php echo $surface; ?>;
                --shapla-text-primary: <?php echo $text_primary; ?>;
                --shapla-text-secondary: <?php echo $text_secondary; ?>;
                --shapla-text-hint: <?php echo $text_hint; ?>;
                --shapla-text-disabled: <?php echo $text_disabled; ?>;
                --shapla-text-icon: <?php echo $text_icon; ?>;
            }
        </style>
		<?php
	}

	/**
	 * Find RGB color from a color
	 *
	 * @param string $color
	 *
	 * @return string|array
	 * @since 1.0.0
	 */
	public static function find_rgb_color( $color ) {
		if ( '' === $color ) {
			return '';
		}
		// Trim unneeded whitespace
		$color = str_replace( ' ', '', $color );
		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			// Format the hex color string.
			$hex = str_replace( '#', '', $color );
			if ( 3 == strlen( $hex ) ) {
				$hex = str_repeat( substr( $hex, 0, 1 ), 2 ) .
				       str_repeat( substr( $hex, 1, 1 ), 2 ) .
				       str_repeat( substr( $hex, 2, 1 ), 2 );
			}
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );

			return array( $r, $g, $b, 1 );
		}
		// If this is rgb color
		if ( 'rgb(' === substr( $color, 0, 4 ) ) {
			list( $r, $g, $b ) = sscanf( $color, 'rgb(%d,%d,%d)' );

			return array( $r, $g, $b, 1 );
		}
		// If this is rgba color
		if ( 'rgba(' === substr( $color, 0, 5 ) ) {
			list( $r, $g, $b, $alpha ) = sscanf( $color, 'rgba(%d,%d,%d,%f)' );

			return array( $r, $g, $b, $alpha );
		}

		return '';
	}

	/**
	 * Calculate the luminance for a color.
	 * @link https://www.w3.org/TR/WCAG20-TECHS/G17.html#G17-tests
	 *
	 * @param string $color
	 *
	 * @return float|string
	 * @since 1.0.0
	 */
	public static function calculate_color_luminance( $color ) {
		$rgb_color = static::find_rgb_color( $color );
		if ( ! is_array( $rgb_color ) ) {
			return '';
		}
		$colors = [];
		list( $colors['red'], $colors['green'], $colors['blue'] ) = $rgb_color;
		foreach ( $colors as $name => $value ) {
			$value = $value / 255;
			if ( $value < 0.03928 ) {
				$value = $value / 12.92;
			} else {
				$value = ( $value + .055 ) / 1.055;
				$value = pow( $value, 2 );
			}
			$colors[ $name ] = $value;
		}

		return ( $colors['red'] * .2126 + $colors['green'] * .7152 + $colors['blue'] * .0722 );
	}

	/**
	 * Find light or dark color for given color
	 *
	 * @param $color
	 *
	 * @return string
	 * @since  1.0.0
	 */
	public static function find_color_invert( $color ) {
		$luminance = static::calculate_color_luminance( $color );
		if ( $luminance > 0.55 ) {
			//bright color, use dark font
			return '#000000';
		} else {
			//dark color, use bright font
			return '#ffffff';
		}
	}

	/**
	 * Adjust a hex color brightness
	 * Allows us to create hover styles for custom link colors
	 *
	 * @param string $color color e.g. #111111.
	 * @param integer $steps factor by which to brighten/darken ranging from -255 (darken) to 255 (brighten).
	 *
	 * @return string        brightened/darkened hex color
	 * @since  1.0.0
	 */
	public static function adjust_color_brightness( $color, $steps ) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter.
		$steps     = max( - 255, min( 255, $steps ) );
		$rgb_color = static::find_rgb_color( $color );
		if ( ! is_array( $rgb_color ) ) {
			return '';
		}
		list( $r, $g, $b ) = $rgb_color;
		// Adjust number of steps and keep it inside 0 to 255.
		$r     = max( 0, min( 255, $r + $steps ) );
		$g     = max( 0, min( 255, $g + $steps ) );
		$b     = max( 0, min( 255, $b + $steps ) );
		$r_hex = str_pad( dechex( $r ), 2, '0', STR_PAD_LEFT );
		$g_hex = str_pad( dechex( $g ), 2, '0', STR_PAD_LEFT );
		$b_hex = str_pad( dechex( $b ), 2, '0', STR_PAD_LEFT );

		return '#' . $r_hex . $g_hex . $b_hex;
	}
}