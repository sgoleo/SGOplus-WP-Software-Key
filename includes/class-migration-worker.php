<?php
/**
 * Migration Worker Class
 *
 * @package SGOplus\SoftwareKey
 */

namespace SGOplus\SoftwareKey;

use SGOplus\SoftwareKey\Libraries\WP_Background_Process;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_Worker
 * Handles the background migration of legacy license data.
 */
class Migration_Worker extends WP_Background_Process {

	/**
	 * Action identifier
	 *
	 * @var string
	 */
	protected $action = 'migration_worker';

	/**
	 * Task
	 *
	 * Process a single license record migration.
	 *
	 * @param mixed $item The legacy license ID or record.
	 *
	 * @return mixed false on success (removes from queue), or the item to retry.
	 */
	protected function task( $item ) {
		global $wpdb;

		$license_id = (int) $item;
		if ( ! $license_id ) {
			return false;
		}

		// 1. Fetch legacy record
		$legacy_table = $wpdb->prefix . 'lic_key_tbl';
		$legacy_record = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $legacy_table WHERE id = %d", $license_id ) );

		if ( ! $legacy_record ) {
			return false; // Record not found, skip.
		}

		// 2. Prepare data for new schema
		$new_licenses_table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;
		
		// Map fields (Assumption based on common SLM schema)
		$license_data = array(
			'license_key'      => $legacy_record->license_key,
			'product_id'       => isset( $legacy_record->product_id ) ? $legacy_record->product_id : '',
			'customer_email'   => isset( $legacy_record->email ) ? $legacy_record->email : ( isset( $legacy_record->customer_email ) ? $legacy_record->customer_email : '' ),
			'customer_name'    => isset( $legacy_record->name ) ? $legacy_record->name : ( isset( $legacy_record->customer_name ) ? $legacy_record->customer_name : '' ),
			'status'           => isset( $legacy_record->lic_status ) ? $legacy_record->lic_status : ( isset( $legacy_record->status ) ? $legacy_record->status : 'active' ),
			'activation_limit' => isset( $legacy_record->max_allowed_domains ) ? (int) $legacy_record->max_allowed_domains : 1,
			'created_at'       => isset( $legacy_record->date_created ) ? $legacy_record->date_created : current_time( 'mysql' ),
			'expires_at'       => ( isset( $legacy_record->date_expiry ) && '0000-00-00' !== $legacy_record->date_expiry ) ? $legacy_record->date_expiry : null,
		);

		// 3. Insert into new table
		$wpdb->insert( $new_licenses_table, $license_data );
		$new_license_id = $wpdb->insert_id;

		if ( ! $new_license_id ) {
			// Check if it's a duplicate key (already migrated)
			if ( ! empty( $wpdb->last_error ) && strpos( $wpdb->last_error, 'Duplicate' ) !== false ) {
				// Already migrated, let's try to find the new ID to migrate domains anyway
				$new_license_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $new_licenses_table WHERE license_key = %s", $legacy_record->license_key ) );
			} else {
				return $item; // Retry later
			}
		}

		// 4. Migrate associated domains (lic_reg_domain_tbl)
		$legacy_domain_table = $wpdb->prefix . 'lic_reg_domain_tbl';
		$new_domains_table  = $wpdb->prefix . DB_Schema::DOMAINS_TABLE;

		// Note: SLM uses lic_key_id or license_key to link. We'll check for license_key link.
		$legacy_domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $legacy_domain_table WHERE lic_key_id = %d", $license_id ) );

		if ( $legacy_domains ) {
			$activation_count = 0;
			foreach ( $legacy_domains as $domain ) {
				$domain_data = array(
					'license_id'        => $new_license_id,
					'registered_domain' => $domain->registered_domain,
					'activated_at'      => isset( $domain->date_registered ) ? $domain->date_registered : current_time( 'mysql' ),
				);
				$wpdb->insert( $new_domains_table, $domain_data );
				$activation_count++;
			}

			// Update activation count in license table
			$wpdb->update(
				$new_licenses_table,
				array( 'activation_count' => $activation_count ),
				array( 'id' => $new_license_id )
			);
		}

		return false; // Success
	}

	/**
	 * Complete
	 *
	 * Log completion or update migration status.
	 */
	protected function complete() {
		parent::complete();
		error_log( 'SGOplus Software Key: Background migration completed.' );
		update_option( 'sgoplus_swk_migration_completed', time() );
	}
}
