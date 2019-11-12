<?php
/**
 * Plugin Name:     Stackonet Site Colors
 * Description:     A WordPress plugin to change stackonet plugins color system according to site colors.
 * Version:         1.0.0
 * Author:          Stackonet Services (Pvt.) Ltd.
 * Author URI:      http://www.stackonet.com
 * Text Domain:     stackonet-site-colors
 * Domain Path:     /languages
 */

// If this file is called directly, abort.
defined( 'ABSPATH' ) || die;

final class StackonetSiteColors {

	/**
	 * The instance of the class
	 *
	 * @var self
	 */
	private static $instance;

	/**
	 * Plugin name slug
	 *
	 * @var string
	 */
	private $plugin_name = 'stackonet-site-colors';

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Holds various class instances
	 *
	 * @var array
	 */
	private $container = array();

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private $min_php = '5.3';

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();

			// define constants
			self::$instance->define_constants();

			// Check if PHP version is supported for our plugin
			if ( ! self::$instance->is_supported_php() ) {
				register_activation_hook( __FILE__, array( self::$instance, 'auto_deactivate' ) );
				add_action( 'admin_notices', array( self::$instance, 'php_version_notice' ) );

				return self::$instance;
			}

			// initialize the classes
			add_action( 'plugins_loaded', array( self::$instance, 'init_classes' ) );

			// Load plugin textdomain
			add_action( 'plugins_loaded', array( self::$instance, 'load_plugin_textdomain' ) );

			// Register plugin activation activity
			register_activation_hook( __FILE__, array( self::$instance, 'activation' ) );
		}

		return self::$instance;
	}

	/**
	 * Define plugin constants
	 */
	private function define_constants() {
		define( 'STACKONET_SITE_COLORS', $this->plugin_name );
		define( 'STACKONET_SITE_COLORS_VERSION', $this->version );
		define( 'STACKONET_SITE_COLORS_FILE', __FILE__ );
		define( 'STACKONET_SITE_COLORS_PATH', dirname( STACKONET_SITE_COLORS_FILE ) );
		define( 'STACKONET_SITE_COLORS_INCLUDES', STACKONET_SITE_COLORS_PATH . '/includes' );
		define( 'STACKONET_SITE_COLORS_URL', plugins_url( '', STACKONET_SITE_COLORS_FILE ) );
		define( 'STACKONET_SITE_COLORS_ASSETS', STACKONET_SITE_COLORS_URL . '/assets' );
	}

	/**
	 * Instantiate the required classes
	 *
	 * @return void
	 */
	public function init_classes() {
		include_once STACKONET_SITE_COLORS_INCLUDES . '/StackonetSiteColorsCustomizer.php';
		StackonetSiteColorsCustomizer::init();

		if ( $this->is_request( 'frontend' ) ) {
			include_once STACKONET_SITE_COLORS_INCLUDES . '/StackonetSiteColorsFrontend.php';
			StackonetSiteColorsFrontend::init();
		}
	}

	/**
	 * Run on plugin activation
	 */
	public function activation() {
		do_action( 'stackonet_site_colors/activation' );
	}

	/**
	 * Load plugin textdomain
	 */
	public function load_plugin_textdomain() {
		// Traditional WordPress plugin locale filter
		$locale = apply_filters( 'plugin_locale', get_locale(), $this->plugin_name );
		$mofile = sprintf( '%1$s-%2$s.mo', $this->plugin_name, $locale );
		// Setup paths to current locale file
		$mofile_global = WP_LANG_DIR . '/' . $this->plugin_name . '/' . $mofile;
		// Look in global /wp-content/languages/dialog-contact-form folder
		if ( file_exists( $mofile_global ) ) {
			load_textdomain( $this->plugin_name, $mofile_global );
		}
	}

	/**
	 * Show notice about PHP version
	 *
	 * @return void
	 */
	public function php_version_notice() {
		if ( $this->is_supported_php() || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$error = __( 'Your installed PHP Version is: ', 'stackonet-site-colors' ) . PHP_VERSION . '. ';
		$error .= sprintf( __( 'The Stackonet Site Colors plugin requires PHP version %s or greater.',
			'stackonet-site-colors' ), $this->min_php );
		?>
        <div class="error">
            <p><?php printf( $error ); ?></p>
        </div>
		<?php
	}

	/**
	 * Bail out if the php version is lower than
	 *
	 * @return void
	 */
	public function auto_deactivate() {
		if ( $this->is_supported_php() ) {
			return;
		}
		deactivate_plugins( plugin_basename( __FILE__ ) );
		$error = '<h1>' . __( 'An Error Occurred', 'stackonet-site-colors' ) . '</h1>';
		$error .= '<h2>' . __( 'Your installed PHP Version is: ', 'stackonet-site-colors' ) . PHP_VERSION . '</h2>';
		$error .= '<p>' . sprintf( __( 'The Stackonet Site Colors plugin requires PHP version %s or greater',
				'stackonet-site-colors' ), $this->min_php ) . '</p>';
		$error .= '<p>' . sprintf( __( 'The version of your PHP is %s unsupported and old %s. ',
				'stackonet-site-colors' ),
				'<a href="http://php.net/supported-versions.php" target="_blank"><strong>',
				'</strong></a>'
			);
		$error .= __( 'You should update your PHP software or contact your host regarding this matter.',
				'stackonet-site-colors' ) . '</p>';
		wp_die( $error, __( 'Plugin Activation Error', 'stackonet-site-colors' ), array( 'back_link' => true ) );
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type admin, ajax, rest, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'rest' :
				return defined( 'REST_REQUEST' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}

		return false;
	}

	/**
	 * Check if the PHP version is supported
	 *
	 * @param null $min_php
	 *
	 * @return bool
	 */
	private function is_supported_php( $min_php = null ) {
		$min_php = $min_php ? $min_php : $this->min_php;
		if ( version_compare( PHP_VERSION, $min_php, '<=' ) ) {
			return false;
		}

		return true;
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function stackonet_site_colors() {
	return StackonetSiteColors::init();
}

stackonet_site_colors();
