<?php

namespace SGOplus\WP_Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT {

	public function register_post_type() {
		$labels = array(
			'name'               => esc_html__( 'Software Licenses', 'sgoplus-wp-software-key' ),
			'singular_name'      => esc_html__( 'Software License', 'sgoplus-wp-software-key' ),
			'menu_name'          => esc_html__( 'Software Key+', 'sgoplus-wp-software-key' ),
			'all_items'          => esc_html__( 'All Licenses', 'sgoplus-wp-software-key' ),
			'add_new'            => esc_html__( 'Add New', 'sgoplus-wp-software-key' ),
			'add_new_item'       => esc_html__( 'Add New License', 'sgoplus-wp-software-key' ),
			'edit_item'          => esc_html__( 'Edit License', 'sgoplus-wp-software-key' ),
			'new_item'           => esc_html__( 'New License', 'sgoplus-wp-software-key' ),
			'view_item'          => esc_html__( 'View License', 'sgoplus-wp-software-key' ),
			'search_items'       => esc_html__( 'Search Licenses', 'sgoplus-wp-software-key' ),
			'not_found'          => esc_html__( 'No licenses found', 'sgoplus-wp-software-key' ),
			'not_found_in_trash' => esc_html__( 'No licenses found in trash', 'sgoplus-wp-software-key' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'capability_type'     => 'post',
			'hierarchical'        => false,
			'supports'            => array( 'title' ),
			'menu_icon'           => 'dashicons-admin-network',
		);

		register_post_type( 'swk_license', $args );

		// Add custom columns
		add_filter( 'manage_swk_license_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_swk_license_posts_custom_column', array( $this, 'display_custom_columns' ), 10, 2 );
	}

	public function add_custom_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( $key === 'title' ) {
				$new_columns['swk_key']    = esc_html__( 'License Key', 'sgoplus-wp-software-key' );
				$new_columns['swk_status'] = esc_html__( 'Status', 'sgoplus-wp-software-key' );
			}
			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}

	public function display_custom_columns( $column, $post_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'swk_licenses';
		
		// Note: Title is used for User/Reference, License Key is in custom table
		$license_key = $wpdb->get_var( $wpdb->prepare( "SELECT license_key FROM $table WHERE license_key = %s", get_the_title( $post_id ) ) );

		if ( $column === 'swk_key' ) {
			echo '<code>' . esc_html( get_the_title( $post_id ) ) . '</code>';
		}
		if ( $column === 'swk_status' ) {
			echo '<span style="background: #e7f7ff; color: #0073aa; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 600;">ACTIVE</span>';
		}
	}
}
