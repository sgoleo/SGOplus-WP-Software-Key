<?php

namespace SGOplus\WP_Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class REST_API {

	public function register_routes() {
		register_rest_route( 'sgoplus-license/v1', '/verify', array(
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'verify_license' ),
			'permission_callback' => '__return_true', // In production, add secret key validation
		) );
	}

	public function verify_license( $request ) {
		global $wpdb;
		$license_key = sanitize_text_field( $request->get_param( 'license_key' ) );
		$domain      = esc_url_raw( $request->get_param( 'domain' ) );

		if ( empty( $license_key ) || empty( $domain ) ) {
			return new \WP_Error( 'missing_params', esc_html__( 'Missing license_key or domain.', 'sgoplus-wp-software-key' ), array( 'status' => 400 ) );
		}

		$table_licenses = $wpdb->prefix . 'swk_licenses';
		$table_domains  = $wpdb->prefix . 'swk_registered_domains';

		// 1. Check License Existence & Status
		$license = $wpdb->get_row( $wpdb->prepare(
			"SELECT * FROM $table_licenses WHERE license_key = %s",
			$license_key
		) );

		if ( ! $license ) {
			return rest_ensure_response( array(
				'result'  => 'error',
				'message' => esc_html__( 'Invalid License Key.', 'sgoplus-wp-software-key' ),
			) );
		}

		if ( $license->status !== 'active' ) {
			return rest_ensure_response( array(
				'result'  => 'error',
				'message' => esc_html__( 'License is not active.', 'sgoplus-wp-software-key' ),
			) );
		}

		// 2. Check Expiry
		if ( $license->expiry_date && strtotime( $license->expiry_date ) < time() ) {
			return rest_ensure_response( array(
				'result'  => 'error',
				'message' => esc_html__( 'License has expired.', 'sgoplus-wp-software-key' ),
			) );
		}

		// 3. Domain Validation / Registration
		$registered_domains = $wpdb->get_col( $wpdb->prepare(
			"SELECT domain_url FROM $table_domains WHERE license_id = %d",
			$license->id
		) );

		$normalized_domain = trailingslashit( strtolower( $domain ) );
		$is_registered = false;

		foreach ( $registered_domains as $reg_url ) {
			if ( trailingslashit( strtolower( $reg_url ) ) === $normalized_domain ) {
				$is_registered = true;
				break;
			}
		}

		if ( ! $is_registered ) {
			if ( count( $registered_domains ) < $license->max_domains ) {
				// Register new domain
				$wpdb->insert( $table_domains, array(
					'license_id' => $license->id,
					'domain_url' => $domain,
				), array( '%d', '%s' ) );
			} else {
				return rest_ensure_response( array(
					'result'  => 'error',
					'message' => esc_html__( 'Domain limit reached for this license.', 'sgoplus-wp-software-key' ),
				) );
			}
		}

		return rest_ensure_response( array(
			'result'  => 'success',
			'message' => esc_html__( 'License verified successfully.', 'sgoplus-wp-software-key' ),
			'data'    => array(
				'expiry' => $license->expiry_date,
				'status' => $license->status,
			),
		) );
	}
}
