<?php
/**
 * Migration Engine Class
 *
 * @package SGOplus\SoftwareKey
 */

namespace SGOplus\SoftwareKey;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Migration_Engine
 * Orchestrates the migration process.
 */
class Migration_Engine {

	/**
	 * Worker instance
	 *
	 * @var Migration_Worker
	 */
	private $worker;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->worker = new Migration_Worker();

		// Hook into admin init to check if migration is needed.
		add_action( 'admin_init', array( $this, 'maybe_start_migration' ) );
	}

	/**
	 * Check and start migration if requested, with nonce verification.
	 */
	public function maybe_start_migration() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $_GET['sgoplus_start_migration'] ) || '1' !== $_GET['sgoplus_start_migration'] ) {
			return;
		}

		// Nonce must be present and valid.
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'sgoplus_start_migration' ) ) {
			wp_die( esc_html__( 'Security check failed.', 'sgoplus-software-key' ) );
		}

		$this->start_migration();

		// Redirect back to avoid multiple triggers.
		wp_safe_redirect( remove_query_arg( array( 'sgoplus_start_migration', '_wpnonce' ) ) );
		exit;
	}

	/**
	 * Start the migration process.
	 */
	public function start_migration() {
		global $wpdb;

		$legacy_table = $wpdb->prefix . 'lic_key_tbl';

		// Check if legacy table exists.
		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $legacy_table ) ) !== $legacy_table ) {
			return;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$ids = $wpdb->get_col( "SELECT id FROM {$legacy_table} ORDER BY id ASC" );

		if ( empty( $ids ) ) {
			update_option( 'sgoplus_swk_migration_completed', time() );
			return;
		}

		// Push to worker queue.
		foreach ( $ids as $id ) {
			$this->worker->push_to_queue( $id );
		}

		// Dispatch the worker.
		$this->worker->save()->dispatch();
	}

	/**
	 * Get migration status
	 *
	 * @return array
	 */
	public function get_status() {
		$is_completed = get_option( 'sgoplus_swk_migration_completed' );

		return array(
			'completed' => (bool) $is_completed,
			'timestamp' => $is_completed,
		);
	}
}
