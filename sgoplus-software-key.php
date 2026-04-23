<?php
/**
 * Plugin Name: SGOplus Software Key
 * Description: Modernized software license management system with secure REST API and React-based dashboard.
 * Version: 1.0.0
 * Author: SGOplus
 * Author URI: https://sgoplus.one
 * License: GPLv2 or later
 * Text Domain: sgoplus-software-key
 * Requires at least: 6.5
 * Requires PHP: 7.4
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants
define( 'SGOPLUS_SWK_VERSION', '1.0.1' );
define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Manual Class Loading with Defensive Checks
 */
$sgoplus_swk_files = array(
	'includes/class-db-schema.php',
	'includes/libraries/class-wp-async-request.php',
	'includes/libraries/class-wp-background-process.php',
	'includes/class-migration-worker.php',
	'includes/class-migration-engine.php',
	'includes/class-rest-api.php',
	'includes/class-admin-dashboard.php',
);

foreach ( $sgoplus_swk_files as $file ) {
	$path = SGOPLUS_SWK_PATH . $file;
	if ( file_exists( $path ) ) {
		require_once $path;
	}
}

/**
 * Activation Logic
 */
register_activation_hook( __FILE__, function() {
	if ( class_exists( 'SGOplus\\SoftwareKey\\DB_Schema' ) ) {
		\SGOplus\SoftwareKey\DB_Schema::install();
	}
} );

/**
 * Initialize Plugin
 */
function sgoplus_swk_init_plugin() {
	// Initialize Migration Engine
	if ( is_admin() && class_exists( 'SGOplus\\SoftwareKey\\Migration_Engine' ) ) {
		new \SGOplus\SoftwareKey\Migration_Engine();
	}

	// Initialize REST API
	if ( class_exists( 'SGOplus\\SoftwareKey\\REST_API' ) ) {
		new \SGOplus\SoftwareKey\REST_API();
	}

	// Initialize Admin Dashboard
	if ( class_exists( 'SGOplus\\SoftwareKey\\Admin_Dashboard' ) ) {
		new \SGOplus\SoftwareKey\Admin_Dashboard();
	}
}
add_action( 'plugins_loaded', 'sgoplus_swk_init_plugin' );
