<?php

namespace SGOplus\Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Database_Schema {

	public static function init() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$table_licenses = $wpdb->prefix . 'swk_licenses';
		$table_domains  = $wpdb->prefix . 'swk_registered_domains';

		$sql = array();

		// Licenses Table
		$sql[] = "CREATE TABLE $table_licenses (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			license_key varchar(100) NOT NULL,
			product_id varchar(50) DEFAULT '',
			user_id bigint(20) DEFAULT 0,
			user_email varchar(100) DEFAULT '',
			status varchar(20) DEFAULT 'active',
			max_domains int(11) DEFAULT 1,
			expiry_date datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY license_key (license_key)
		) $charset_collate;";

		// Registered Domains Table
		$sql[] = "CREATE TABLE $table_domains (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			license_id bigint(20) NOT NULL,
			domain_url varchar(255) NOT NULL,
			registered_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY license_id (license_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		if ( function_exists( 'dbDelta' ) ) {
			foreach ( $sql as $query ) {
				dbDelta( $query );
			}
		}
	}
}
