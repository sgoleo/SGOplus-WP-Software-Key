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

namespace SGOplus\SoftwareKey;

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
	$prefix = __NAMESPACE__ . '\\';
	$base_dir = SGOPLUS_SWK_PATH . 'includes/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	
	// Convert namespace to file path
	// Example: SGOplus\SoftwareKey\Libraries\WP_Async_Request -> libraries/class-wp-async-request.php
	$parts = explode( '\\', $relative_class );
	$class_name = array_pop( $parts );
	$path = strtolower( implode( DIRECTORY_SEPARATOR, $parts ) );
	
	$file = $base_dir . ( $path ? $path . DIRECTORY_SEPARATOR : '' ) . 'class-' . strtolower( str_replace( '_', '-', $class_name ) ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
} );

/**
 * Activation Logic
 */
register_activation_hook( __FILE__, array( __NAMESPACE__ . '\\DB_Schema', 'install' ) );

/**
 * Initialize Plugin
 */
function init_plugin() {
	// Initialize Migration Engine
	if ( is_admin() ) {
		new Migration_Engine();
	}

	// Initialize core components here in future steps
}
add_action( 'plugins_loaded', __NAMESPACE__ . '\\init_plugin' );
