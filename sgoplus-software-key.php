<?php
/**
 * Plugin Name: SGOplus Software Key
 * Description: Modernized software license management system with secure REST API and React-based dashboard.
 * Version: 1.0.7
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
define( 'SGOPLUS_SWK_VERSION', '1.0.7' );
define( 'SGOPLUS_SWK_PATH', plugin_dir_path( __FILE__ ) );
define( 'SGOPLUS_SWK_URL', plugin_dir_url( __FILE__ ) );

namespace SGOplus\SoftwareKey;

/**
 * Database Schema Class (Moved here for absolute stability)
 */
class DB_Schema {
	const LICENSES_TABLE = 'sgoplus_swk_licenses';
	const DOMAINS_TABLE  = 'sgoplus_swk_domains';

	public static function install() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$licenses_table  = $wpdb->prefix . self::LICENSES_TABLE;
		$domains_table   = $wpdb->prefix . self::DOMAINS_TABLE;

		$sql_licenses = "CREATE TABLE $licenses_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			license_key varchar(100) NOT NULL,
			product_id varchar(50) DEFAULT '' NOT NULL,
			customer_email varchar(100) DEFAULT '' NOT NULL,
			customer_name varchar(255) DEFAULT '' NOT NULL,
			status varchar(20) DEFAULT 'active' NOT NULL,
			activation_limit int(11) DEFAULT 1 NOT NULL,
			activation_count int(11) DEFAULT 0 NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			expires_at datetime DEFAULT NULL,
			last_modified datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			meta longtext DEFAULT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY license_key (license_key),
			KEY product_id (product_id),
			KEY customer_email (customer_email)
		) $charset_collate;";

		$sql_domains = "CREATE TABLE $domains_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			license_id bigint(20) NOT NULL,
			registered_domain varchar(255) NOT NULL,
			instance_id varchar(100) DEFAULT '' NOT NULL,
			activated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			last_check_in datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			KEY license_id (license_id),
			KEY registered_domain (registered_domain(191))
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_licenses );
		dbDelta( $sql_domains );
		update_option( 'sgoplus_swk_db_version', '1.0.7' );
	}
}

/**
 * Activation Logic
 */
register_activation_hook( __FILE__, array( 'SGOplus\\SoftwareKey\\DB_Schema', 'install' ) );

/**
 * Core Classes Loading
 */
require_once SGOPLUS_SWK_PATH . 'includes/libraries/class-wp-async-request.php';
require_once SGOPLUS_SWK_PATH . 'includes/libraries/class-wp-background-process.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-migration-worker.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-migration-engine.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-rest-api.php';
require_once SGOPLUS_SWK_PATH . 'includes/class-admin-dashboard.php';

/**
 * Initialize Components
 */
add_action( 'plugins_loaded', function() {
	// Initialize Migration Engine
	if ( class_exists( 'SGOplus\\SoftwareKey\\Migration_Engine' ) ) {
		new \SGOplus\SoftwareKey\Migration_Engine();
	}

	// Initialize REST API
	if ( class_exists( 'SGOplus\\SoftwareKey\\REST_API' ) ) {
		new \SGOplus\SoftwareKey\REST_API();
	}
} );

/**
 * Admin Menu - Procedural approach for maximum compatibility with WP 6.9
 */
add_action( 'admin_menu', function() {
	$capability = 'manage_options';
	$slug = 'sgoplus-swk-dashboard';

	// 1. Main Menu
	add_menu_page(
		'SGOplus Software Key',
		'Software Key+',
		$capability,
		$slug,
		'SGOplus\\SoftwareKey\\sgoplus_swk_render_dashboard',
		'dashicons-shield-lock',
		26
	);

	// 2. Submenus
	add_submenu_page( $slug, 'Licenses', 'Licenses', $capability, $slug, 'SGOplus\\SoftwareKey\\sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, 'Add New', 'Add New', $capability, $slug . '-add', 'SGOplus\\SoftwareKey\\sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, 'Logs', 'Activation Logs', $capability, $slug . '-logs', 'SGOplus\\SoftwareKey\\sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, 'Settings', 'Settings', $capability, $slug . '-settings', 'SGOplus\\SoftwareKey\\sgoplus_swk_render_dashboard' );
	add_submenu_page( $slug, 'Guide', 'Guide', $capability, $slug . '-guide', 'SGOplus\\SoftwareKey\\sgoplus_swk_render_dashboard' );
}, 99 );

/**
 * Render Dashboard
 */
function sgoplus_swk_render_dashboard() {
	echo '<div id="sgoplus-swk-admin-root"></div>';
	
	// Enqueue assets manually here to be safe
	$dashboard = new Admin_Dashboard();
	$dashboard->enqueue_assets( 'toplevel_page_sgoplus-swk-dashboard' );
}

/**
 * Ensure assets are enqueued
 */
add_action( 'admin_enqueue_scripts', function( $hook ) {
	if ( strpos( $hook, 'sgoplus-swk-dashboard' ) !== false ) {
		$dashboard = new Admin_Dashboard();
		$dashboard->enqueue_assets( $hook );
	}
}, 99 );
