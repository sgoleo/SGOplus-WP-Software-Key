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
			return new WP_Error( 'invalid_license', 'Invalid license key.', array( 'status' => 403 ) );
		}

		// 2. Validate status
		if ( 'active' !== $license->status ) {
			return new WP_Error( 'license_inactive', 'License is not active.', array( 'status' => 403 ) );
		}

		// 3. Check expiration
		if ( $license->expires_at && strtotime( $license->expires_at ) < time() ) {
			return new WP_Error( 'license_expired', 'License has expired.', array( 'status' => 403 ) );
		}

		// 4. Check if domain already registered
		$existing_domain = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $domains_table WHERE license_id = %d AND registered_domain = %s",
			$license->id,
			$domain
		) );

		if ( $existing_domain ) {
			return new WP_REST_Response( array(
				'success' => true,
				'message' => 'License already active on this domain.',
				'license' => $license,
			), 200 );
		}

		// 5. Check limit
		if ( $license->activation_count >= $license->activation_limit ) {
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

		return new WP_REST_Response( array(
			'success' => true,
			'message' => 'License activated successfully.',
		), 200 );
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
}
