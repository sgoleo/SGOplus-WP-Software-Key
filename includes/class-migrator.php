<?php

namespace SGOplus\WP_Software_Key;

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
			wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'sgoplus-wp-software-key' ) ) );
		}

		global $wpdb;
		$offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
		$limit  = 50; // Process 50 at a time

		$slm_table = $wpdb->prefix . 'wp_lic_key_tbl';
		
		// Check if SLM table exists
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $slm_table ) ) !== $slm_table ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Legacy SLM table not found.', 'sgoplus-wp-software-key' ) ) );
		}

		$total = $wpdb->get_var( "SELECT COUNT(id) FROM $slm_table" );
		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $slm_table ORDER BY id ASC LIMIT %d, %d", $offset, $limit ) );

		if ( ! $items ) {
			wp_send_json_success( array( 'complete' => true ) );
		}

		$table_licenses = $wpdb->prefix . 'swk_licenses';
		$table_domains  = $wpdb->prefix . 'swk_registered_domains';
		$slm_domain_table = $wpdb->prefix . 'wp_lic_reg_domain_tbl';

		foreach ( $items as $item ) {
			// Insert into new table
			$wpdb->replace( $table_licenses, array(
				'license_key' => $item->license_key,
				'user_id'     => 0, // SLM uses different user logic
				'status'      => strtolower( $item->lic_status ),
				'max_domains' => $item->max_allowed_domains,
				'expiry_date' => $item->date_expiry,
				'created_at'  => $item->date_created,
			) );

			$new_id = $wpdb->insert_id;

			// Migrate domains for this key
			$domains = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $slm_domain_table WHERE lic_key_id = %d", $item->id ) );
			foreach ( $domains as $d ) {
				$wpdb->insert( $table_domains, array(
					'license_id' => $new_id,
					'domain_url' => $d->registered_domain,
				) );
			}
		}

		wp_send_json_success( array(
			'complete' => ( $offset + $limit >= $total ),
			'total'    => $total,
			'offset'   => $offset + $limit,
		) );
	}

	public function render_migration_ui() {
		?>
		<div class="swk-migration-card card" style="margin-top: 20px; padding: 25px; border-radius: 12px; border: 1px solid #e5e5e5; box-shadow: 0 4px 12px rgba(0,0,0,0.05); background: #fff;">
			<h2 style="margin-top: 0; color: #6c5ce7; display: flex; align-items: center; gap: 10px;">
				<span class="dashicons dashicons-migrate"></span> 
				<?php esc_html_e( 'Legacy Migration (SLM)', 'sgoplus-wp-software-key' ); ?>
			</h2>
			<p><?php esc_html_e( 'Safely import your license data from the "Software License Manager" plugin. This process is optimized for high performance and won\'t timeout.', 'sgoplus-wp-software-key' ); ?></p>
			
			<div id="swk-migration-progress" style="display: none; margin: 20px 0;">
				<div style="height: 12px; background: #f0f0f1; border-radius: 10px; overflow: hidden; border: 1px solid #eee;">
					<div id="swk-progress-bar" style="width: 0%; height: 100%; background: linear-gradient(90deg, #6c5ce7, #a29bfe); transition: width 0.3s ease;"></div>
				</div>
				<p id="swk-progress-text" style="margin-top: 10px; font-weight: 600; color: #666;"></p>
			</div>

			<button id="swk-start-migration" class="button button-primary" style="background: #6c5ce7; border-color: #6c5ce7; padding: 5px 25px; height: auto; font-weight: 600; border-radius: 8px;">
				<?php esc_html_e( 'Start Migration', 'sgoplus-wp-software-key' ); ?>
			</button>

			<script>
			jQuery(document).ready(function($){
				$('#swk-start-migration').click(function(e){
					e.preventDefault();
					if(!confirm('<?php echo esc_js( __( 'Are you sure? This will import legacy data into SGOplus format.', 'sgoplus-wp-software-key' ) ); ?>')) return;
					
					var $btn = $(this);
					var $progress = $('#swk-migration-progress');
					var $bar = $('#swk-progress-bar');
					var $text = $('#swk-progress-text');
					
					$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Processing...', 'sgoplus-wp-software-key' ) ); ?>');
					$progress.show();
					
					function runMigration(offset) {
						$.post(ajaxurl, {
							action: 'swk_migrate_slm',
							offset: offset,
							nonce: '<?php echo wp_create_nonce("swk_migration_nonce"); ?>'
						}, function(res){
							if(res.success) {
								if(res.data.complete) {
									$bar.css('width', '100%');
									$text.text('<?php echo esc_js( __( 'Migration Complete!', 'sgoplus-wp-software-key' ) ); ?>');
									$btn.text('<?php echo esc_js( __( 'Done', 'sgoplus-wp-software-key' ) ); ?>');
								} else {
									var pct = Math.round((res.data.offset / res.data.total) * 100);
									$bar.css('width', pct + '%');
									$text.text('<?php echo esc_js( __( 'Migrating:', 'sgoplus-wp-software-key' ) ); ?> ' + res.data.offset + ' / ' + res.data.total);
									runMigration(res.data.offset);
								}
							} else {
								alert(res.data.message);
								$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Try Again', 'sgoplus-wp-software-key' ) ); ?>');
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
