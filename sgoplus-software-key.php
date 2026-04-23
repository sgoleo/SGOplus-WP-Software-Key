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
define( 'SGOPLUS_SWK_VERSION', '1.0.0' );
define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader (Simple PSR-4 style for internal use)
 */
spl_autoload_register( function ( $class ) {
	$prefix = 'SGOplus\\SoftwareKey\\';
	$base_dir = SGOPLUS_SWK_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	
	// Convert namespace to file path
	$parts = explode( '\\', $relative_class );
	$class_name = array_pop( $parts );
	$path = strtolower( implode( '/', $parts ) );
	
	$file = $base_dir . ( $path ? $path . '/' : '' ) . 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Activation Logic
 */
register_activation_hook( __FILE__, array( 'SGOplus\\SoftwareKey\\DB_Schema', 'install' ) );

/**
 * Initialize Plugin
 */
function sgoplus_swk_init_plugin() {
	// Initialize Migration Engine
	if ( is_admin() ) {
		new SGOplus\SoftwareKey\Migration_Engine();
	}

	// Initialize REST API
	new SGOplus\SoftwareKey\REST_API();

	// Initialize Admin Dashboard
	if ( is_admin() ) {
		new SGOplus\SoftwareKey\Admin_Dashboard();
	}

	// Initialize core components here in future steps
}
add_action( 'plugins_loaded', 'sgoplus_swk_init_plugin' );
