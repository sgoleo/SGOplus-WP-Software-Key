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
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get Instance (singleton)
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor — load constants and files immediately; hooks on plugins_loaded.
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
		if ( ! defined( 'SGOPLUS_SWK_VERSION' ) ) {
			define( 'SGOPLUS_SWK_VERSION', '1.0.9' );
		}
		if ( ! defined( 'SGOPLUS_SWK_PATH' ) ) {
			define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'SGOPLUS_SWK_URL' ) ) {
			define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Include Files
	 *
	 * All class files are loaded here so that the activation hook
	 * (which runs after includes()) can safely reference DB_Schema::install.
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
		// Activation hook — DB_Schema is already loaded via includes().
		register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\DB_Schema', 'install' ) );

		// Defer component initialisation until all plugins are loaded.
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );

		// Admin menu registration must happen on admin_menu, not plugins_loaded.
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
			__( 'SGOplus Software Key', 'sgoplus-software-key' ),
			__( 'Software Key+', 'sgoplus-software-key' ),
			$cap,
			$slug,
			array( $this, 'render_dashboard' ),
			'dashicons-shield-lock',
			26
		);

		add_submenu_page( $slug, __( 'Licenses', 'sgoplus-software-key' ),         __( 'Licenses', 'sgoplus-software-key' ),         $cap, $slug,                    array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, __( 'Add New', 'sgoplus-software-key' ),          __( 'Add New', 'sgoplus-software-key' ),          $cap, $slug . '-add',            array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, __( 'Activation Logs', 'sgoplus-software-key' ),  __( 'Activation Logs', 'sgoplus-software-key' ),  $cap, $slug . '-logs',           array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, __( 'Settings', 'sgoplus-software-key' ),         __( 'Settings', 'sgoplus-software-key' ),         $cap, $slug . '-settings',       array( $this, 'render_dashboard' ) );
		add_submenu_page( $slug, __( 'Guide', 'sgoplus-software-key' ),            __( 'Guide', 'sgoplus-software-key' ),            $cap, $slug . '-guide',          array( $this, 'render_dashboard' ) );
	}

	/**
	 * Render Dashboard
	 *
	 * Outputs the React mount point and enqueues frontend assets.
	 */
	public function render_dashboard() {
		echo '<div id="sgoplus-swk-admin-root"></div>';

		$dashboard = new Admin_Dashboard();
		$screen    = \get_current_screen();
		$dashboard->enqueue_assets( $screen ? $screen->id : 'toplevel_page_sgoplus-swk-dashboard' );
	}
}

/**
 * Initialize the Plugin on plugins_loaded to guarantee WordPress is fully
 * bootstrapped before any class is instantiated.
 */
add_action(
	'plugins_loaded',
	static function () {
		Plugin::get_instance();
	},
	1 // Priority 1 — run before other plugins_loaded callbacks.
);
