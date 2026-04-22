<?php
/**
 * Plugin Name: SGOplus WP Software Key
 * Description: A modern and secure Software License Manager for WordPress, designed with premium visual aesthetics.
 * Version: 1.0.0
 * Author: SGOplus
 * Author URI: https://sgoplus.one
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.5
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * Stable tag: 1.0.0
 * Text Domain: sgoplus-wp-software-key
 */

namespace SGOplus\WP_Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define constants
define( 'SGOPLUS_SWK_VERSION', '1.0.0' );
define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );

// Include required classes
require_once SGOPLUS_SWK_PATH . 'includes/class-db-schema.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-rest-api.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-swk-cpt.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-swk-settings.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-migrator.php';

/**
 * Activation Logic
 */
function activate() {
	Database_Schema::init();
}
register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );

/**
 * Initialization
 */
function init() {
	// Initialize CPT
	$cpt = new CPT();
	$cpt->register_post_type();
	
	// Initialize REST API
	$api = new REST_API();
	add_action( 'rest_api_init', array( $api, 'register_routes' ) );

	// Initialize Settings
	new Settings();

	// Initialize Migrator
	new Migrator();
}
add_action( 'init', __NAMESPACE__ . '\\init' );
