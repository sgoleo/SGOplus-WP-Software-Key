<?php
/**
 * Plugin Name: SGOplus Software Key
 * Description: Modernized software license management system with secure REST API and React-based dashboard.
 * Version:     1.2.0
 * Author:      SGOplus
 * Author URI:  https://sgoplus.one
 * License:     GPLv2 or later
 * Text Domain: sgoplus-software-key
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP:      7.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Constants ────────────────────────────────────────────────────────────────

define( 'SGOPLUS_SWK_VERSION',  '1.2.0' );
define( 'SGOPLUS_SWK_PATH',     plugin_dir_path( __FILE__ ) );
define( 'SGOPLUS_SWK_URL',      plugin_dir_url( __FILE__ ) );
define( 'SGOPLUS_SWK_BASENAME', plugin_basename( __FILE__ ) );

// ─── Load all class files immediately (global scope, before any hook fires) ───

require_once SGOPLUS_SWK_PATH . 'includes/class-db-schema.php';
require_once SGOPLUS_SWK_PATH . 'includes/libraries/class-wp-async-request.php';
require_once SGOPLUS_SWK_PATH . 'includes/libraries/class-wp-background-process.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-migration-worker.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-migration-engine.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-rest-api.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-admin-dashboard.php';

// ─── Activation / Deactivation Hooks (MUST be at global scope) ────────────────

/**
 * Plugin activation callback.
 * Called by WordPress when the admin clicks "Activate".
 */
function sgoplus_swk_activate() {
	SGOplus\SoftwareKey\DB_Schema::install();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'sgoplus_swk_activate' );

/**
 * Plugin deactivation callback.
 */
function sgoplus_swk_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'sgoplus_swk_deactivate' );

// ─── Bootstrap the plugin after all plugins are loaded ────────────────────────

add_action( 'plugins_loaded', 'sgoplus_swk_init', 10 );

/**
 * Main bootstrap function — initialises all plugin components.
 */
function sgoplus_swk_init() {
	// Load translations.
	load_plugin_textdomain(
		'sgoplus-software-key',
		false,
		dirname( SGOPLUS_SWK_BASENAME ) . '/languages'
	);

	// Instantiate background-migration engine (registers its own hooks).
	new SGOplus\SoftwareKey\Migration_Engine();

	// Instantiate REST API controller (registers routes on rest_api_init).
	new SGOplus\SoftwareKey\REST_API();
}

// ─── Admin Menu (must be on admin_menu, not plugins_loaded) ──────────────────

add_action( 'admin_menu', 'sgoplus_swk_register_menu', 99 );

/**
 * Register the admin menu pages.
 */
function sgoplus_swk_register_menu() {
	$slug = 'sgoplus-swk-dashboard';
	$cap  = 'manage_options';

	add_menu_page(
		__( 'SGOplus Software Key', 'sgoplus-software-key' ),
		__( 'Software Key+', 'sgoplus-software-key' ),
		$cap,
		$slug,
		'sgoplus_swk_render_dashboard',
		'dashicons-shield-lock',
		26
	);

	add_submenu_page( $slug, __( 'Licenses',        'sgoplus-software-key' ), __( 'Licenses',        'sgoplus-software-key' ), $cap, $slug,                  'sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, __( 'Add New',         'sgoplus-software-key' ), __( 'Add New',         'sgoplus-software-key' ), $cap, $slug . '-add',         'sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, __( 'Activation Logs', 'sgoplus-software-key' ), __( 'Activation Logs', 'sgoplus-software-key' ), $cap, $slug . '-logs',        'sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, __( 'Settings',        'sgoplus-software-key' ), __( 'Settings',        'sgoplus-software-key' ), $cap, $slug . '-settings',    'sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, __( 'Guide',           'sgoplus-software-key' ), __( 'Guide',           'sgoplus-software-key' ), $cap, $slug . '-guide',       'sgoplus_swk_render_dashboard' );
}

/**
 * Render the React SPA mount point and enqueue assets.
 */
function sgoplus_swk_render_dashboard() {
	echo '<div id="sgoplus-swk-admin-root"></div>';

	$screen    = get_current_screen();
	$hook      = $screen ? $screen->id : 'toplevel_page_sgoplus-swk-dashboard';
	$dashboard = new SGOplus\SoftwareKey\Admin_Dashboard();
	$dashboard->enqueue_assets( $hook );
}
