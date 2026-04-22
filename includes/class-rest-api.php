<?php

namespace SGOplus\Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API {

	public function register_routes() {
		$method = class_exists( 'WP_REST_Server' ) ? \WP_REST_Server::CREATABLE : 'POST';
		
		register_rest_route( 'sgoplus-license/v1', '/verify', array(
			'methods'             => $method,
			'callback'            => array( $this, 'verify_license' ),
			'permission_callback' => '__return_true', // In production, add secret key validation
		) );
	}

	public function verify_license( $request ) {
		global $wpdb;
		
		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
		$domain      = esc_url_raw( $request->get_param( 'domain' ) );
		$secret_key  = sanitize_text_field( $request->get_param( 'secret_key' ) );
		$user_email  = sanitize_email( $request->get_param( 'user_email' ) );
		$action      = sanitize_text_field( $request->get_param( 'action' ) ); // activate, deactivate, check
		$product_id  = sanitize_text_field( $request->get_param( 'product_id' ) );

		if ( empty( $action ) ) $action = 'check';

		// 0. Secret Key Validation
		$stored_secret = get_option( 'swk_secret_key', '' );
		if ( ! empty( $stored_secret ) && $secret_key !== $stored_secret ) {
			return new \WP_Error( 'unauthorized', esc_html__( 'Invalid Secret Key.', 'sgoplus-software-key' ), array( 'status' => 401 ) );
		}

		if ( empty( $license_key ) || ( $action !== 'check' && empty( $domain ) ) ) {
			return new \WP_Error( 'missing_params', esc_html__( 'Missing parameters.', 'sgoplus-software-key' ), array( 'status' => 400 ) );
		}

		$table_licenses = $wpdb->prefix . 'swk_licenses';
		$table_domains  = $wpdb->prefix . 'swk_registered_domains';

		// 1. Check License Existence
		$where = $wpdb->prepare( "WHERE license_key = %s", $license_key );
		if ( ! empty( $product_id ) ) {
			$where .= $wpdb->prepare( " AND product_id = %s", $product_id );
		}

		$license = $wpdb->get_row( "SELECT * FROM $table_licenses $where" );

		if ( ! $license ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => esc_html__( 'Invalid License Key.', 'sgoplus-software-key' ) ) );
		}

		// 1.1 Email Validation (If stored in DB)
		if ( ! empty( $license->user_email ) && ( empty( $user_email ) || strtolower( $user_email ) !== strtolower( $license->user_email ) ) ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => esc_html__( 'License email mismatch.', 'sgoplus-software-key' ) ) );
		}

		// 2. Handle Actions
		if ( $action === 'deactivate' ) {
			$wpdb->delete( $table_domains, array( 'license_id' => $license->id, 'domain_url' => $domain ), array( '%d', '%s' ) );
			return rest_ensure_response( array( 'result' => 'success', 'message' => esc_html__( 'License deactivated.', 'sgoplus-software-key' ) ) );
		}

		// 3. Check Status & Expiry
		if ( $license->status !== 'active' ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => esc_html__( 'License is not active.', 'sgoplus-software-key' ) ) );
		}

		if ( $license->expiry_date && strtotime( $license->expiry_date ) < time() ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => esc_html__( 'License has expired.', 'sgoplus-software-key' ) ) );
		}

		// 4. Domain Validation / Registration
		$registered_domains = $wpdb->get_col( $wpdb->prepare( "SELECT domain_url FROM $table_domains WHERE license_id = %d", $license->id ) );
		$normalized_domain = trailingslashit( strtolower( $domain ) );
		$is_registered = false;

		foreach ( $registered_domains as $reg_url ) {
			if ( trailingslashit( strtolower( $reg_url ) ) === $normalized_domain ) {
				$is_registered = true;
				break;
			}
		}

		if ( ! $is_registered && $action === 'activate' ) {
			if ( count( $registered_domains ) < $license->max_domains ) {
				$wpdb->insert( $table_domains, array( 'license_id' => $license->id, 'domain_url' => $domain ), array( '%d', '%s' ) );
				$is_registered = true;
			} else {
				return rest_ensure_response( array( 'result' => 'error', 'message' => esc_html__( 'Domain limit reached.', 'sgoplus-software-key' ) ) );
			}
		}

		if ( ! $is_registered ) {
			return rest_ensure_response( array( 'result' => 'error', 'message' => esc_html__( 'Domain not registered.', 'sgoplus-software-key' ) ) );
		}

		return rest_ensure_response( array(
			'result'  => 'success',
			'message' => esc_html__( 'License verified.', 'sgoplus-software-key' ),
			'data'    => array( 'expiry' => $license->expiry_date, 'status' => $license->status, 'product_id' => $license->product_id ),
		) );
	}
}
