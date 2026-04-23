<?php
/**
 * Database Schema Class
 *
 * @package SGOplus\SoftwareKey
 */

namespace SGOplus\SoftwareKey;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class DB_Schema
 * Handles table creation and schema updates.
 */
class DB_Schema {

	/**
	 * Table names
	 */
	const LICENSES_TABLE = 'sgoplus_swk_licenses';
	const DOMAINS_TABLE  = 'sgoplus_swk_domains';
	const LOGS_TABLE     = 'sgoplus_swk_logs';

	/**
	 * Create/Update database tables.
	 */
	public static function install() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$licenses_table  = $wpdb->prefix . self::LICENSES_TABLE;
		$domains_table   = $wpdb->prefix . self::DOMAINS_TABLE;
		$logs_table      = $wpdb->prefix . self::LOGS_TABLE;

		// 1. Primary License Table
		// Designed to accommodate legacy lic_key_tbl data
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

		// 2. Domain/Activation Table
		// Designed to accommodate legacy lic_reg_domain_tbl data
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

		// 3. Activity Logs Table
		$sql_logs = "CREATE TABLE $logs_table (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			license_key varchar(100) NOT NULL,
			event_type varchar(50) NOT NULL,
			message text DEFAULT '' NOT NULL,
			domain varchar(255) DEFAULT '' NOT NULL,
			ip_address varchar(45) DEFAULT '' NOT NULL,
			user_agent text DEFAULT '' NOT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			PRIMARY KEY  (id),
			KEY license_key (license_key),
			KEY event_type (event_type)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_licenses );
		dbDelta( $sql_domains );
		dbDelta( $sql_logs );

		// Update version in options
		update_option( 'sgoplus_swk_db_version', '1.0.0' );
	}
}
