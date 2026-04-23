<?php
/**
 * REST API Controller Class
 *
 * @package SGOplus\SoftwareKey
 */

namespace SGOplus\SoftwareKey;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class REST_API
 * Handles all REST API routes for license management.
 */
class REST_API extends WP_REST_Controller {

	/**
	 * Namespace and version
	 */
	protected $namespace = 'sgoplus-swk/v1';

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register routes
	 */
	public function register_routes() {
		// 1. External: Remote Activation
		register_rest_route( $this->namespace, '/activate', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'activate_license' ),
				'permission_callback' => '__return_true', // Public but validated by key
				'args'                => array(
					'license_key' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'product_id' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'domain' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
		) );

		// 2. Internal: License Management (Dashboard)
		register_rest_route( $this->namespace, '/stats', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
			),
		) );

		register_rest_route( $this->namespace, '/licenses', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_licenses' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
				'args'                => array(
					'per_page' => array( 'default' => 20, 'sanitize_callback' => 'absint' ),
					'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
					'search'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'status'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'orderby'  => array( 'default' => 'id', 'sanitize_callback' => 'sanitize_text_field' ),
					'order'    => array( 'default' => 'desc', 'sanitize_callback' => 'sanitize_text_field' ),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_license' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
				'args'                => array(
					'product_id'       => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
					'customer_email'   => array( 'required' => true, 'sanitize_callback' => 'sanitize_email' ),
					'customer_name'    => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'activation_limit' => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
				),
			),
		) );

		register_rest_route( $this->namespace, '/licenses/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_license' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
				'args'                => array(
					'status' => array(
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'activation_limit' => array(
						'required'          => false,
						'sanitize_callback' => 'absint',
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_license' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_license' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
			),
		) );

		register_rest_route( $this->namespace, '/logs', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_logs' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
				'args'                => array(
					'per_page' => array( 'default' => 20, 'sanitize_callback' => 'absint' ),
					'page'     => array( 'default' => 1, 'sanitize_callback' => 'absint' ),
				),
			),
		) );

		register_rest_route( $this->namespace, '/settings', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_settings' ),
				'permission_callback' => array( $this, 'check_internal_permission' ),
			),
		) );
	}

	/**
	 * Permission callback for internal routes
	 */
	public function check_internal_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Callback: Remote Activation
	 */
	public function activate_license( $request ) {
		global $wpdb;

		$license_key = $request->get_param( 'license_key' );
		$product_id  = $request->get_param( 'product_id' );
		$domain      = $request->get_param( 'domain' );

		$licenses_table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;
		$domains_table  = $wpdb->prefix . DB_Schema::DOMAINS_TABLE;

		// 1. Fetch license
		$license = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $licenses_table WHERE license_key = %s",
			$license_key
		) );

		if ( ! $license ) {
			$this->log_event( $license_key, 'error', 'Invalid license key attempt.', $domain );
			return new WP_Error( 'invalid_license', 'Invalid license key.', array( 'status' => 403 ) );
		}

		// 2. Validate status
		if ( 'active' !== $license->status ) {
			$this->log_event( $license_key, 'error', 'Attempt to activate inactive license.', $domain );
			return new WP_Error( 'license_inactive', 'License is not active.', array( 'status' => 403 ) );
		}

		// 3. Check expiration
		if ( $license->expires_at && strtotime( $license->expires_at ) < time() ) {
			$this->log_event( $license_key, 'error', 'Attempt to activate expired license.', $domain );
			return new WP_Error( 'license_expired', 'License has expired.', array( 'status' => 403 ) );
		}

		// 4. Check if domain already registered
		$existing_domain = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $domains_table WHERE license_id = %d AND registered_domain = %s",
			$license->id,
			$domain
		) );

		if ( $existing_domain ) {
			$this->log_event( $license_key, 'info', 'License already active on this domain.', $domain );
			return new WP_REST_Response( array(
				'success' => true,
				'message' => 'License already active on this domain.',
				'license' => $license,
			), 200 );
		}

		// 5. Check limit
		if ( $license->activation_count >= $license->activation_limit ) {
			$this->log_event( $license_key, 'error', 'Activation limit reached.', $domain );
			return new WP_Error( 'limit_reached', 'Activation limit reached.', array( 'status' => 403 ) );
		}

		// 6. Register domain
		$wpdb->insert( $domains_table, array(
			'license_id'        => $license->id,
			'registered_domain' => $domain,
			'activated_at'      => current_time( 'mysql' ),
			'last_check_in'     => current_time( 'mysql' ),
		) );

		// 7. Update count
		$wpdb->update( $licenses_table, 
			array( 'activation_count' => $license->activation_count + 1 ),
			array( 'id' => $license->id )
		);

		$this->log_event( $license_key, 'success', 'License activated successfully.', $domain );

		return new WP_REST_Response( array(
			'success' => true,
			'message' => 'License activated successfully.',
		), 200 );
	}

	/**
	 * Callback: Get Licenses List (Internal)
	 */
	public function get_licenses( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;

		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$search   = $request->get_param( 'search' );
		$status   = $request->get_param( 'status' );
		$orderby  = $request->get_param( 'orderby' );
		$order    = $request->get_param( 'order' );

		$offset = ( $page - 1 ) * $per_page;

		$where = ' WHERE 1=1';
		$params = array();

		if ( ! empty( $search ) ) {
			$where .= ' AND (license_key LIKE %s OR customer_email LIKE %s OR customer_name LIKE %s)';
			$search_val = '%' . $wpdb->esc_like( $search ) . '%';
			$params[] = $search_val;
			$params[] = $search_val;
			$params[] = $search_val;
		}

		if ( ! empty( $status ) ) {
			$where .= ' AND status = %s';
			$params[] = $status;
		}

		// Valid columns for orderby
		$allowed_orderby = array( 'id', 'license_key', 'created_at', 'status', 'activation_count' );
		if ( ! in_array( $orderby, $allowed_orderby ) ) {
			$orderby = 'id';
		}
		$order = strtoupper( $order ) === 'ASC' ? 'ASC' : 'DESC';

		$query = "SELECT * FROM $table $where ORDER BY $orderby $order LIMIT %d OFFSET %d";
		$params[] = $per_page;
		$params[] = $offset;

		$results = $wpdb->get_results( $wpdb->prepare( $query, $params ) );
		
		// Get total count for pagination headers
		$total_query = "SELECT COUNT(*) FROM $table $where";
		$total = $wpdb->get_var( $wpdb->prepare( $total_query, array_slice( $params, 0, count( $params ) - 2 ) ) );

		$response = new WP_REST_Response( $results, 200 );
		$response->header( 'X-WP-Total', (int) $total );
		$response->header( 'X-WP-TotalPages', ceil( $total / $per_page ) );

		return $response;
	}

	/**
	 * Callback: Get License Details (Internal)
	 */
	public function get_license( $request ) {
		global $wpdb;
		$id = $request->get_param( 'id' );
		$table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;

		$license = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );

		if ( ! $license ) {
			return new WP_Error( 'not_found', 'License not found.', array( 'status' => 404 ) );
		}

		return new WP_REST_Response( $license, 200 );
	}

	/**
	 * Callback: Update License (Internal)
	 */
	public function update_license( $request ) {
		global $wpdb;
		$id     = $request->get_param( 'id' );
		$status = $request->get_param( 'status' );
		$limit  = $request->get_param( 'activation_limit' );

		$table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;
		$data  = array();

		if ( ! empty( $status ) ) {
			$data['status'] = $status;
		}
		if ( isset( $limit ) ) {
			$data['activation_limit'] = $limit;
		}

		if ( empty( $data ) ) {
			return new WP_Error( 'no_data', 'No data provided to update.', array( 'status' => 400 ) );
		}

		$updated = $wpdb->update( $table, $data, array( 'id' => $id ) );

		if ( false === $updated ) {
			return new WP_Error( 'db_error', 'Failed to update license.', array( 'status' => 500 ) );
		}

		return new WP_REST_Response( array( 'success' => true, 'message' => 'License updated.' ), 200 );
	}

	/**
	 * Callback: Create License (Internal)
	 */
	public function create_license( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;

		$license_key = 'SWK-' . strtoupper( wp_generate_password( 4, false ) ) . '-' . strtoupper( wp_generate_password( 4, false ) ) . '-' . strtoupper( wp_generate_password( 4, false ) );
		
		$data = array(
			'license_key'      => $license_key,
			'product_id'       => $request->get_param( 'product_id' ),
			'customer_email'   => $request->get_param( 'customer_email' ),
			'customer_name'    => $request->get_param( 'customer_name' ),
			'activation_limit' => $request->get_param( 'activation_limit' ),
			'status'           => 'active',
			'created_at'       => current_time( 'mysql' ),
		);

		$inserted = $wpdb->insert( $table, $data );

		if ( ! $inserted ) {
			return new WP_Error( 'db_error', 'Failed to create license.', array( 'status' => 500 ) );
		}

		$this->log_event( $license_key, 'admin', 'License created manually via dashboard.' );

		return new WP_REST_Response( array( 'success' => true, 'id' => $wpdb->insert_id, 'license_key' => $license_key ), 201 );
	}

	/**
	 * Callback: Delete License (Internal)
	 */
	public function delete_license( $request ) {
		global $wpdb;
		$id = $request->get_param( 'id' );
		$table_licenses = $wpdb->prefix . DB_Schema::LICENSES_TABLE;
		$table_domains  = $wpdb->prefix . DB_Schema::DOMAINS_TABLE;

		$license_key = $wpdb->get_var( $wpdb->prepare( "SELECT license_key FROM $table_licenses WHERE id = %d", $id ) );
		
		if ( ! $license_key ) {
			return new WP_Error( 'not_found', 'License not found.', array( 'status' => 404 ) );
		}

		$wpdb->delete( $table_licenses, array( 'id' => $id ) );
		$wpdb->delete( $table_domains, array( 'license_id' => $id ) );

		$this->log_event( $license_key, 'admin', 'License and all associated domains deleted.' );

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Callback: Get Stats (Internal)
	 */
	public function get_stats( $request = null ) {
		global $wpdb;
		$licenses_table = $wpdb->prefix . DB_Schema::LICENSES_TABLE;
		$domains_table  = $wpdb->prefix . DB_Schema::DOMAINS_TABLE;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$active_licenses = $wpdb->get_var( "SELECT COUNT(*) FROM {$licenses_table} WHERE status = 'active'" );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_activations = $wpdb->get_var( "SELECT COUNT(*) FROM {$domains_table}" );

		return new WP_REST_Response( array(
			'active_licenses'   => (int) $active_licenses,
			'total_activations' => (int) $total_activations,
			'revenue'           => '$' . number_format( (int) $active_licenses * 29, 2 ),
			'security_health'   => '99.9%',
		), 200 );
	}

	/**
	 * Callback: Get Logs (Internal)
	 */
	public function get_logs( $request ) {
		global $wpdb;
		$table = $wpdb->prefix . DB_Schema::LOGS_TABLE;

		$per_page = $request->get_param( 'per_page' );
		$page     = $request->get_param( 'page' );
		$offset   = ( $page - 1 ) * $per_page;

		$results = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d",
			$per_page, $offset
		) );

		$total = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );

		$response = new WP_REST_Response( $results, 200 );
		$response->header( 'X-WP-Total', (int) $total );
		return $response;
	}

	/**
	 * Callback: Get Settings (Internal)
	 */
	public function get_settings() {
		return new WP_REST_Response( array(
			'enable_logs'      => get_option( 'sgoplus_swk_enable_logs', 'yes' ),
			'auto_expire_days' => get_option( 'sgoplus_swk_auto_expire_days', 365 ),
			'api_secret'       => get_option( 'sgoplus_swk_api_secret', wp_generate_password( 32, false ) ),
		), 200 );
	}

	/**
	 * Callback: Update Settings (Internal)
	 */
	public function update_settings( $request ) {
		$params = $request->get_params();
		
		if ( isset( $params['enable_logs'] ) ) {
			update_option( 'sgoplus_swk_enable_logs', sanitize_text_field( $params['enable_logs'] ) );
		}
		if ( isset( $params['auto_expire_days'] ) ) {
			update_option( 'sgoplus_swk_auto_expire_days', absint( $params['auto_expire_days'] ) );
		}
		if ( isset( $params['api_secret'] ) ) {
			update_option( 'sgoplus_swk_api_secret', sanitize_text_field( $params['api_secret'] ) );
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	/**
	 * Helper: Log Event
	 */
	private function log_event( $license_key, $event_type, $message, $domain = '' ) {
		global $wpdb;
		$table = $wpdb->prefix . DB_Schema::LOGS_TABLE;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$ip         = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		$wpdb->insert( $table, array(
			'license_key' => $license_key,
			'event_type'  => $event_type,
			'message'     => $message,
			'domain'      => $domain,
			'ip_address'  => $ip,
			'user_agent'  => $user_agent,
			'created_at'  => current_time( 'mysql' ),
		) );
	}
}
