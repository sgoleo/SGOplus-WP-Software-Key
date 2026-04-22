<?php

namespace SGOplus\Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Migrator {

	public function __construct() {
		add_action( 'wp_ajax_swk_migrate_slm', array( $this, 'ajax_migrate_slm' ) );
	}

	public function ajax_migrate_slm() {
		check_ajax_referer( 'swk_migration_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'sgoplus-software-key' ) ) );
		}

		global $wpdb;
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		$limit  = 50;

		// Standard SLM table names
		$slm_table        = $wpdb->prefix . 'lic_key_tbl';
		$slm_domain_table = $wpdb->prefix . 'lic_reg_domain_tbl';
		
		// Verify SLM table exists
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $slm_table ) ) !== $slm_table ) {
			// Fallback check for common manual naming issues
			$alt_table = $wpdb->prefix . 'wp_lic_key_tbl';
			if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $alt_table ) ) === $alt_table ) {
				$slm_table        = $alt_table;
				$slm_domain_table = $wpdb->prefix . 'wp_lic_reg_domain_tbl';
			} else {
				/* translators: %s: Table name */
				wp_send_json_error( array( 'message' => sprintf( esc_html__( 'Legacy SLM table not found: %s', 'sgoplus-software-key' ), $slm_table ) ) );
			}
		}

		$cache_key_total = 'swk_slm_total_' . md5( $slm_table );
		$total = \wp_cache_get( $cache_key_total, 'swk_migration' );
		if ( false === $total ) {
			$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM %i", $slm_table ) );
			\wp_cache_set( $cache_key_total, $total, 'swk_migration', 60 );
		}

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i ORDER BY id ASC LIMIT %d, %d", $slm_table, $offset, $limit ) );

		if ( ! $items ) {
			wp_send_json_success( array( 'complete' => true ) );
		}

		$table_licenses = $wpdb->prefix . 'swk_licenses';
		$table_domains  = $wpdb->prefix . 'swk_registered_domains';

		foreach ( $items as $item ) {
			// Try to match WP user by email
			$user_id = 0;
			if ( ! empty( $item->email ) ) {
				$user = get_user_by( 'email', $item->email );
				if ( $user ) {
					$user_id = $user->ID;
				}
			}

			// 1. Sync Custom Table
			// We use REPLACE to ensure that if a license key exists, it gets updated with SLM data
			$wpdb->replace( $table_licenses, array(
				'license_key' => $item->license_key,
				'product_id'  => ! empty( $item->product_ref ) ? $item->product_ref : '',
				'user_id'     => $user_id,
				'status'      => strtolower( $item->lic_status ),
				'max_domains' => $item->max_allowed_domains,
				'expiry_date' => $item->date_expiry,
				'created_at'  => $item->date_created,
			) );
			$new_license_id = $wpdb->insert_id;

			// 2. Sync Custom Post Type
			$existing_post_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_swk_license_id' AND meta_value = %d", $new_license_id ) );
			
			$reference_name = trim( $item->first_name . ' ' . $item->last_name );
			if ( empty( $reference_name ) ) {
				$reference_name = ! empty( $item->company_name ) ? $item->company_name : $item->license_key;
			}

			$post_data = array(
				'ID'           => $existing_post_id ? $existing_post_id : 0,
				'post_title'   => $reference_name,
				'post_type'    => 'swk_license',
				'post_status'  => 'publish',
				'post_author'  => $user_id ? $user_id : get_current_user_id(),
			);

			if ( $existing_post_id ) {
				wp_update_post( $post_data );
			} else {
				$post_id = wp_insert_post( $post_data );
				update_post_meta( $post_id, '_swk_license_id', $new_license_id );
			}

			// 3. Sync Domains
			// First, clear old domains for this license to avoid duplicates during re-migration
			$wpdb->delete( $table_domains, array( 'license_id' => $new_license_id ) );

			$domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM %i WHERE lic_key_id = %d", $slm_domain_table, $item->id ) );
			if ( $domains ) {
				foreach ( $domains as $d ) {
					$wpdb->insert( $table_domains, array(
						'license_id' => $new_license_id,
						'domain_url' => $d->registered_domain,
					) );
				}
			}
		}

		wp_send_json_success( array(
			'complete' => ( $offset + $limit >= $total ),
			'total'    => $total,
			'offset'   => $offset + $limit,
			'last_key' => $item->license_key, // For UI feedback
		) );
	}

	public function render_migration_ui() {
		?>
		<div class="swk-migration-card card" style="margin-top: 20px; padding: 25px; border-radius: 12px; border: 1px solid #e5e5e5; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #fff;">
			<h2 style="margin-top: 0; color: #6c5ce7; display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-migrate"></span> 
				<?php esc_html_e( 'Legacy Migration (SLM)', 'sgoplus-software-key' ); ?>
			</h2>
			<p><?php esc_html_e( 'Safely import your license data from the "Software License Manager" plugin. This process is optimized for high performance and won\'t timeout.', 'sgoplus-software-key' ); ?></p>
			
			<div id="swk-migration-progress" style="display: none; margin: 20px 0;">
				<div style="height: 12px; background: #f0f0f1; border-radius: 10px; overflow: hidden; border: 1px solid #eee;">
					<div id="swk-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #6c5ce7, #a29bfe); transition: width 0.3s ease;"></div>
				</div>
				<p id="swk-progress-text" style="margin-top: 10px; font-weight: 600; color: #666;"></p>
			</div>

			<button id="swk-start-migration" class="button button-primary" style="background: #6c5ce7; border-color: #6c5ce7; padding: 5px 25px; height: auto; font-weight: 600; border-radius: 8px;">
				<?php esc_html_e( 'Start Migration', 'sgoplus-software-key' ); ?>
			</button>

			<script>
			jQuery(document).ready(function($){
				$('#swk-start-migration').click(function(e){
					e.preventDefault();
					if(!confirm('<?php echo esc_js( __( 'Are you sure? This will import legacy data into SGOplus format.', 'sgoplus-software-key' ) ); ?>')) return;
					
					var $btn = $(this);
					var $progress = $('#swk-migration-progress');
					var $bar = $('#swk-progress-bar');
					var $text = $('#swk-progress-text');
					
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Processing...', 'sgoplus-software-key' ) ); ?>');
					$progress.show();
					
					function runMigration(offset) {
						$.post(ajaxurl, {
							action: 'swk_migrate_slm',
							offset: offset,
							nonce: '<?php echo esc_attr( wp_create_nonce( "swk_migration_nonce" ) ); ?>'
						}, function(res){
							if(res.success) {
								if(res.data.complete) {
									$bar.css('width', '100%');
									$text.text('<?php echo esc_js( __( 'Migration Complete!', 'sgoplus-software-key' ) ); ?>');
									$btn.text('<?php echo esc_js( __( 'Done', 'sgoplus-software-key' ) ); ?>');
								} else {
									var pct = Math.round((res.data.offset / res.data.total) * 100);
									$bar.css('width', pct + '%');
									var statusText = '<?php echo esc_js( __( 'Migrating:', 'sgoplus-software-key' ) ); ?> ' + res.data.offset + ' / ' + res.data.total;
									if(res.data.last_key) statusText += ' (' + res.data.last_key + ')';
									$text.text(statusText);
									runMigration(res.data.offset);
								}
							} else {
								alert(res.data.message);
								$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Try Again', 'sgoplus-software-key' ) ); ?>');
							}
						});
					}
					
					runMigration(0);
				});
			});
			</script>
		</div>
		<?php
	}
}
