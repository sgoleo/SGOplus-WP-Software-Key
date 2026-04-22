<?php
/**
 * Plugin Name: SGOplus Software Key
 * Description: A modern and secure Software License Manager for WordPress, designed with premium visual aesthetics.
 * Version: 1.1.0
 * Author: SGOplus
 * Author URI: https://sgoplus.one
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.5
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Stable tag: 1.0.0
 * Text Domain: sgoplus-software-key
 */

namespace SGOplus\Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Plugin Class
 */
final class Bootstrap {

	/**
	 * Instance of this class
	 * @var Bootstrap
	 */
	private static $instance;

	/**
	 * Get instance of this class
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Define constants
	 */
	private function define_constants() {
		define( 'SGOPLUS_SWK_VERSION', '1.1.0' );
		define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
		define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Include required files
	 */
	private function includes() {
		require_once SGOPLUS_SWK_PATH . 'includes/class-db-schema.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-rest-api.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-swk-cpt.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-swk-settings.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-migrator.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		\register_activation_hook( __FILE__, array( $this, 'activate' ) );
		\add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Activation logic
	 */
	public function activate() {
		Database_Schema::init();
	}

	/**
	 * Initialize the plugin features
	 */
	public function init() {
		// Initialize CPT
		$cpt = new CPT();
		$cpt->register_post_type();
		
		// Initialize REST API
		$api = new REST_API();
		\add_action( 'rest_api_init', array( $api, 'register_routes' ) );

		// Initialize Settings
		new Settings();

		// Initialize Migrator
		new Migrator();
	}
}

// Initialize the plugin
\SGOplus\Software_Key\Bootstrap::get_instance();
