<?php

namespace SGOplus\Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CPT {

	public function register_post_type() {
		$labels = array(
			'name'               => esc_html__( 'Software Licenses', 'sgoplus-software-key' ),
			'singular_name'      => esc_html__( 'Software License', 'sgoplus-software-key' ),
			'menu_name'          => esc_html__( 'Software Key+', 'sgoplus-software-key' ),
			'all_items'          => esc_html__( 'All Licenses', 'sgoplus-software-key' ),
			'add_new'            => esc_html__( 'Add New', 'sgoplus-software-key' ),
			'add_new_item'       => esc_html__( 'Add New License', 'sgoplus-software-key' ),
			'edit_item'          => esc_html__( 'Edit License', 'sgoplus-software-key' ),
			'new_item'           => esc_html__( 'New License', 'sgoplus-software-key' ),
			'view_item'          => esc_html__( 'View License', 'sgoplus-software-key' ),
			'search_items'       => esc_html__( 'Search Licenses', 'sgoplus-software-key' ),
			'not_found'          => esc_html__( 'No licenses found', 'sgoplus-software-key' ),
			'not_found_in_trash' => esc_html__( 'No licenses found in trash', 'sgoplus-software-key' ),
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
			'menu_position'       => 30,
		);

		register_post_type( 'swk_license', $args );

		// Add custom columns
		add_filter( 'manage_swk_license_posts_columns', array( $this, 'set_custom_edit_license_columns' ) );
		add_action( 'manage_swk_license_posts_custom_column', array( $this, 'custom_license_column' ), 10, 2 );

		// Add meta boxes
		add_action( 'add_meta_boxes', array( $this, 'add_license_meta_boxes' ) );
		add_action( 'save_post_swk_license', array( $this, 'save_license_meta' ) );
	}

	public function add_license_meta_boxes() {
		add_meta_box(
			'swk_license_details',
			esc_html__( 'License Details', 'sgoplus-software-key' ),
			array( $this, 'render_license_details_meta_box' ),
			'swk_license',
			'normal',
			'high'
		);
	}

	public function render_license_details_meta_box( $post ) {
		global $wpdb;
		$table = $wpdb->prefix . 'swk_licenses';
		$license_id = get_post_meta( $post->ID, '_swk_license_id', true );
		
		$license = null;
		if ( $license_id ) {
			$license = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $license_id ) );
		}

		// Defaults
		$key         = $license ? $license->license_key : '';
		$product_id  = $license ? $license->product_id : get_option( 'swk_default_product_id', 'SGO_PROD' );
		$user_email  = $license ? $license->user_email : '';
		$status      = $license ? $license->status : 'active';
		$max_domains = $license ? $license->max_domains : 1;
		$expiry      = $license ? $license->expiry_date : '';

		wp_nonce_field( 'swk_save_license', 'swk_license_nonce' );
		?>
		<div class="swk-meta-box">
			<table class="form-table">
				<tr>
					<th><label for="swk_license_key"><?php esc_html_e( 'License Key', 'sgoplus-software-key' ); ?></label></th>
					<td>
						<div style="display: flex; gap: 10px;">
							<input type="text" name="swk_license_key" id="swk_license_key" value="<?php echo esc_attr( $key ); ?>" class="regular-text" style="font-family: monospace;">
							<button type="button" class="button" id="swk-gen-key"><?php esc_html_e( 'Generate', 'sgoplus-software-key' ); ?></button>
						</div>
					</td>
				</tr>
				<tr>
					<th><label for="swk_user_email"><?php esc_html_e( 'User Email', 'sgoplus-software-key' ); ?></label></th>
					<td>
						<input type="email" name="swk_user_email" id="swk_user_email" value="<?php echo esc_attr( $user_email ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="swk_product_id"><?php esc_html_e( 'Product ID', 'sgoplus-software-key' ); ?></label></th>
					<td>
						<input type="text" name="swk_product_id" id="swk_product_id" value="<?php echo esc_attr( $product_id ); ?>" class="regular-text">
					</td>
				</tr>
				<tr>
					<th><label for="swk_status"><?php esc_html_e( 'Status', 'sgoplus-software-key' ); ?></label></th>
					<td>
						<select name="swk_status" id="swk_status">
							<option value="active" <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'sgoplus-software-key' ); ?></option>
							<option value="pending" <?php selected( $status, 'pending' ); ?>><?php esc_html_e( 'Pending', 'sgoplus-software-key' ); ?></option>
							<option value="blocked" <?php selected( $status, 'blocked' ); ?>><?php esc_html_e( 'Blocked', 'sgoplus-software-key' ); ?></option>
							<option value="expired" <?php selected( $status, 'expired' ); ?>><?php esc_html_e( 'Expired', 'sgoplus-software-key' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label for="swk_max_domains"><?php esc_html_e( 'Max Domains', 'sgoplus-software-key' ); ?></label></th>
					<td>
						<input type="number" name="swk_max_domains" id="swk_max_domains" value="<?php echo esc_attr( $max_domains ); ?>" class="small-text">
					</td>
				</tr>
				<tr>
					<th><label for="swk_expiry_date"><?php esc_html_e( 'Expiry Date', 'sgoplus-software-key' ); ?></label></th>
					<td>
						<input type="date" name="swk_expiry_date" id="swk_expiry_date" value="<?php echo esc_attr( $expiry ? date( 'Y-m-d', strtotime( $expiry ) ) : '' ); ?>">
						<p class="description"><?php esc_html_e( 'Leave empty for lifetime license.', 'sgoplus-software-key' ); ?></p>
					</td>
				</tr>
			</table>
			<script>
				jQuery(document).ready(function($){
					$('#swk-gen-key').click(function(){
						var prefix = '<?php echo esc_js( get_option( 'swk_license_prefix', 'SGO-' ) ); ?>';
						var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
						var res = prefix;
						for (var i = 0; i < 16; i++) {
							res += chars.charAt(Math.floor(Math.random() * chars.length));
							if ((i+1) % 4 === 0 && i < 15) res += '-';
						}
						$('#swk_license_key').val(res);
					});
				});
			</script>
		</div>
		<?php
	}

	public function save_license_meta( $post_id ) {
		if ( ! isset( $_POST['swk_license_nonce'] ) || ! wp_verify_nonce( $_POST['swk_license_nonce'], 'swk_save_license' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'swk_licenses';
		$license_id = get_post_meta( $post_id, '_swk_license_id', true );

		$data = array(
			'license_key' => sanitize_text_field( $_POST['swk_license_key'] ),
			'product_id'  => sanitize_text_field( $_POST['swk_product_id'] ),
			'user_email'  => sanitize_email( $_POST['swk_user_email'] ),
			'status'      => sanitize_text_field( $_POST['swk_status'] ),
			'max_domains' => intval( $_POST['swk_max_domains'] ),
			'expiry_date' => ! empty( $_POST['swk_expiry_date'] ) ? sanitize_text_field( $_POST['swk_expiry_date'] ) : null,
		);

		if ( $license_id ) {
			$wpdb->update( $table, $data, array( 'id' => $license_id ) );
		} else {
			$wpdb->insert( $table, $data );
			update_post_meta( $post_id, '_swk_license_id', $wpdb->insert_id );
		}
	}

	public function set_custom_edit_license_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $value ) {
			if ( $key === 'title' ) {
				$new_columns['swk_key']    = esc_html__( 'License Key', 'sgoplus-software-key' );
				$new_columns['swk_user']   = esc_html__( 'User', 'sgoplus-software-key' );
				$new_columns['swk_product'] = esc_html__( 'Product ID', 'sgoplus-software-key' );
				$new_columns['swk_status'] = esc_html__( 'Status', 'sgoplus-software-key' );
			}
			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}

	public function custom_license_column( $column, $post_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'swk_licenses';
		
		switch ( $column ) {
			case 'swk_key':
				$key = $wpdb->get_var( $wpdb->prepare( "SELECT license_key FROM $table WHERE id = (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_swk_license_id')", $post_id ) );
				// Fallback to title if not found via meta
				if ( ! $key ) $key = get_the_title( $post_id );
				echo '<code>' . esc_html( $key ) . '</code>';
				break;
			case 'swk_user':
				$email = $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM $table WHERE id = (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_swk_license_id')", $post_id ) );
				echo esc_html( $email ? $email : '-' );
				break;
			case 'swk_product':
				$pid = $wpdb->get_var( $wpdb->prepare( "SELECT product_id FROM $table WHERE id = (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_swk_license_id')", $post_id ) );
				echo esc_html( $pid ? $pid : '-' );
				break;
			case 'swk_status':
				$status = $wpdb->get_var( $wpdb->prepare( "SELECT status FROM $table WHERE id = (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_swk_license_id')", $post_id ) );
				$color = ( $status === 'active' ) ? '#00a32a' : '#d63638';
				echo '<span style="color:' . esc_attr( $color ) . '; font-weight:bold;">' . esc_html( strtoupper( $status ) ) . '</span>';
				break;
		}
	}
}
