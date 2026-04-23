<?php
/**
 * Plugin Name: SGOplus Software Key
 * Description: Modernized software license management system with secure REST API and React-based dashboard.
 * Version: 1.0.9
 * Author: SGOplus
 * Author URI: https://sgoplus.one
 * License: GPLv2 or later
 * Text Domain: sgoplus-software-key
 * Requires at least: 6.5
 * Requires PHP: 7.4
 */

namespace SGOplus\SoftwareKey;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
final class Plugin {

	/**
	 * Instance
	 * @var Plugin
	 */
	private static $instance;

	/**
	 * Get Instance
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
	 * Define Constants
	 */
	private function define_constants() {
		define( 'SGOPLUS_SWK_VERSION', '1.0.9' );
		define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
		define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Include Files
	 */
	private function includes() {
		require_once SGOPLUS_SWK_PATH . 'includes/class-db-schema.php';
		require_once SGOPLUS_SWK_PATH . 'includes/libraries/class-wp-async-request.php';
		require_once SGOPLUS_SWK_PATH . 'includes/libraries/class-wp-background-process.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-migration-worker.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-migration-engine.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-rest-api.php';
		require_once SGOPLUS_SWK_PATH . 'includes/class-admin-dashboard.php';
	}

	/**
	 * Initialize Hooks
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\DB_Schema', 'install' ) );
		
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ), 99 );
	}

	/**
	 * Init Plugin Components
	 */
	public function init_plugin() {
		new Migration_Engine();
		new REST_API();
	}

	/**
	 * Register Admin Menu
	 */
	public function register_admin_menu() {
		$slug = 'sgoplus-swk-dashboard';
		$cap  = 'manage_options';

		add_menu_page(
			'SGOplus Software Key',
			'Software Key+',
			$cap,
			$slug,
			array( $this, 'render_dashboard' ),
			'dashicons-shield-lock',
			26
		);

		add_submenu_page( $slug, 'Licenses', 'Licenses', $cap, $slug, array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, 'Add New', 'Add New', $cap, $slug . '-add', array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, 'Logs', 'Activation Logs', $cap, $slug . '-logs', array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, 'Settings', 'Settings', $cap, $slug . '-settings', array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, 'Guide', 'Guide', $cap, $slug . '-guide', array( $this, 'render_dashboard' ) );
	}

	/**
	 * Render Dashboard
	 */
	public function render_dashboard() {
		echo '<div id="sgoplus-swk-admin-root"></div>';
		
		$dashboard = new Admin_Dashboard();
		$screen = get_current_screen();
		$dashboard->enqueue_assets( $screen ? $screen->id : 'toplevel_page_sgoplus-swk-dashboard' );
	}
}

/**
 * Initialize the Plugin
 */
Plugin::get_instance();
