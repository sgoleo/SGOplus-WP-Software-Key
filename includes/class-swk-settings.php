<?php

namespace SGOplus\Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function register_settings() {
		register_setting( 'swk_settings_group', 'swk_secret_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'swk_settings_group', 'swk_default_product_id', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'swk_settings_group', 'swk_license_prefix', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	}

	public function add_settings_page() {
		$parent_slug = 'edit.php?post_type=swk_license';

		add_submenu_page(
			$parent_slug,
			esc_html__( 'Settings', 'sgoplus-software-key' ),
			esc_html__( 'Settings', 'sgoplus-software-key' ),
			'manage_options',
			'swk-settings',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			$parent_slug,
			esc_html__( 'Migration', 'sgoplus-software-key' ),
			esc_html__( 'Migration', 'sgoplus-software-key' ),
			'manage_options',
			'swk-migration',
			array( $this, 'render_migration_page' )
		);

		add_submenu_page(
			$parent_slug,
			esc_html__( 'Guild', 'sgoplus-software-key' ),
			esc_html__( 'Guild', 'sgoplus-software-key' ),
			'manage_options',
			'swk-guild',
			array( $this, 'render_guild_page' )
		);
	}

	public function render_settings_page() {
		?>
		<div class="wrap swk-settings-wrap" style="padding-right: 20px; max-width: none !important; width: auto !important; margin-right: 20px;">
			<h1 style="margin-bottom: 25px; font-weight: 800; color: #1d2327; font-size: 2.2em;"><?php esc_html_e( 'SGOplus Software Key Settings', 'sgoplus-software-key' ); ?></h1>
			
			<div style="display: flex; gap: 24px; align-items: flex-start; width: 100%; box-sizing: border-box;">
				<!-- Main Content (Cards) -->
				<div style="flex: 1; min-width: 0; width: 100%;">
					<!-- System Status -->
					<div class="card swk-main-card" style="margin: 0 0 24px 0; padding: 30px; border-radius: 16px; border: 1px solid #e5e5e5; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.03); width: 100%; box-sizing: border-box;">
						<h2 style="margin-top: 0; font-size: 1.2em; display: flex; align-items: center; gap: 12px; color: #1d2327; font-weight: 800;">
							<span class="dashicons dashicons-database" style="color: #6c5ce7; font-size: 24px; width: 24px; height: 24px;"></span> 
							<?php esc_html_e( 'System Status', 'sgoplus-software-key' ); ?>
						</h2>
						<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 15px; margin-top: 20px; width: 100%;">
							<?php
							global $wpdb;
							$tables = array( $wpdb->prefix . 'swk_licenses', $wpdb->prefix . 'swk_registered_domains' );
							$all_ok = true;
							foreach ( $tables as $table ) {
								$exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) === $table;
								$color = $exists ? '#00a32a' : '#d63638';
								$bg = $exists ? '#f0f9f1' : '#fcf0f1';
								$icon = $exists ? 'yes' : 'no';
								echo '<div style="display: flex; align-items: center; gap: 15px; padding: 18px 25px; border-radius: 12px; background: ' . esc_attr( $bg ) . '; border: 1px solid ' . esc_attr( $exists ? '#e1f0e4' : '#f5e1e2' ) . '; transition: all 0.3s ease;">';
								echo '<span class="dashicons dashicons-' . esc_attr( $icon ) . '" style="color: ' . esc_attr( $color ) . '; font-size: 22px; width: 22px; height: 22px;"></span>';
								echo '<code style="background: transparent; color: #333; font-weight: 700; font-size: 1.1em; word-break: break-all;">' . esc_html( $table ) . '</code>';
								echo '</div>';
								if ( ! $exists ) $all_ok = false;
							}
							?>
						</div>
						<?php if ( ! $all_ok ) : ?>
							<div style="color: #d63638; font-size: 0.95em; margin-top: 25px; background: #fcf0f1; padding: 15px; border-radius: 10px; border-left: 5px solid #d63638;">
								<strong><?php esc_html_e( 'Attention:', 'sgoplus-software-key' ); ?></strong> <?php esc_html_e( 'Critical database tables are missing. Please reactivate the plugin immediately to resolve this.', 'sgoplus-software-key' ); ?>
							</div>
						<?php endif; ?>
					</div>

					<!-- General Configuration -->
					<div class="card swk-main-card" style="margin: 0 0 24px 0; padding: 30px; border-radius: 16px; border: 1px solid #e5e5e5; box-shadow: 0 4px 20px rgba(0,0,0,0.03); background: #fff; width: 100%; box-sizing: border-box;">
						<h2 style="margin-top: 0; font-size: 1.4em; display: flex; align-items: center; gap: 12px; color: #1d2327; font-weight: 800;">
							<span class="dashicons dashicons-admin-settings" style="color: #6c5ce7; font-size: 24px; width: 24px; height: 24px;"></span> 
							<?php esc_html_e( 'General Configuration', 'sgoplus-software-key' ); ?>
						</h2>
						<p style="color: #666; font-size: 1.05em;"><?php esc_html_e( 'Manage your global security and licensing behavior.', 'sgoplus-software-key' ); ?></p>
						<hr style="border: 0; border-top: 1px solid #f0f0f1; margin: 25px 0;">
						
						<form method="post" action="options.php">
							<?php
							settings_fields( 'swk_settings_group' );
							do_settings_sections( 'swk-settings' );
							?>
							<table class="form-table swk-custom-form-table">
								<tr>
									<th scope="row" style="font-weight: 600;"><?php esc_html_e( 'API Secret Key', 'sgoplus-software-key' ); ?></th>
									<td>
										<input type="password" name="swk_secret_key" value="<?php echo esc_attr( get_option( 'swk_secret_key', '' ) ); ?>" class="regular-text" style="border-radius: 8px; padding: 8px 12px; width: 100%; max-width: 500px;">
										<p class="description"><?php esc_html_e( 'Used to authenticate remote API calls.', 'sgoplus-software-key' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row" style="font-weight: 600;"><?php esc_html_e( 'License Key Prefix', 'sgoplus-software-key' ); ?></th>
									<td>
										<input type="text" name="swk_license_prefix" value="<?php echo esc_attr( get_option( 'swk_license_prefix', 'SGO-' ) ); ?>" class="regular-text" style="border-radius: 8px; padding: 8px 12px; width: 100%; max-width: 500px;">
										<p class="description"><?php esc_html_e( 'E.g., PROD-XXXX-XXXX', 'sgoplus-software-key' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row" style="font-weight: 600;"><?php esc_html_e( 'Default Product ID', 'sgoplus-software-key' ); ?></th>
									<td>
										<input type="text" name="swk_default_product_id" value="<?php echo esc_attr( get_option( 'swk_default_product_id', 'SGO_PROD' ) ); ?>" class="regular-text" style="border-radius: 8px; padding: 8px 12px; width: 100%; max-width: 500px;">
									</td>
								</tr>
							</table>
							<div style="margin-top: 30px;">
								<?php submit_button( __( 'Save Configuration', 'sgoplus-software-key' ), 'button-primary', 'submit', true, array( 'style' => 'background: #6c5ce7; border-color: #6c5ce7; padding: 10px 45px; height: auto; font-weight: 700; border-radius: 10px; font-size: 1.1em; box-shadow: 0 4px 12px rgba(108, 92, 231, 0.2);' ) ); ?>
							</div>
						</form>
					</div>

					<!-- API Quick Copy -->
					<div class="card swk-main-card" style="margin: 0 0 24px 0; padding: 30px; border-radius: 16px; border: 1px solid #e5e5e5; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.03); width: 100%; box-sizing: border-box;">
						<h2 style="margin-top: 0; font-size: 1.2em; display: flex; align-items: center; gap: 12px; color: #1d2327; font-weight: 800;">
							<span class="dashicons dashicons-admin-links" style="color: #6c5ce7; font-size: 24px; width: 24px; height: 24px;"></span> 
							<?php esc_html_e( 'API Quick Copy', 'sgoplus-software-key' ); ?>
						</h2>
						<p style="color: #666;"><?php esc_html_e( 'Integrate this endpoint into your software activation logic.', 'sgoplus-software-key' ); ?></p>
						<div style="display: flex; gap: 15px; margin-top: 20px; align-items: center; width: 100%;">
							<input type="text" id="swk-api-endpoint" value="<?php echo esc_url( home_url( '/wp-json/sgoplus-license/v1/verify' ) ); ?>" readonly class="regular-text" style="background: #f8f9fa; border-radius: 8px; flex: 1; padding: 12px; font-family: monospace; font-size: 1em;">
							<button type="button" class="button" onclick="swkCopyEndpoint()" style="border-radius: 8px; padding: 10px 30px; height: auto; font-weight: 700; background: #f0f0f1; border-color: #dcdcde;"><?php esc_html_e( 'Copy URL', 'sgoplus-software-key' ); ?></button>
						</div>
						<script>
							function swkCopyEndpoint() {
								var copyText = document.getElementById("swk-api-endpoint");
								copyText.select();
								copyText.setSelectionRange(0, 99999);
								navigator.clipboard.writeText(copyText.value);
								alert("<?php echo esc_js( __( 'API Endpoint copied to clipboard!', 'sgoplus-software-key' ) ); ?>");
							}
						</script>
					</div>
				</div>

				<!-- Sidebar (Developer Hub) -->
				<div style="width: 360px; flex-shrink: 0;">
					<div class="card" style="padding: 45px 30px; border-radius: 24px; text-align: center; border: 1px solid #e5e5e5; box-shadow: 0 10px 40px rgba(0,0,0,0.06); background: #fff; margin: 0; position: sticky; top: 50px;">
						<h3 style="margin-top: 0; color: #1d2327; font-size: 1.4em; font-weight: 900; letter-spacing: -0.5px;">Developer Hub</h3>
						<div style="margin: 35px 0;">
							<div style="width: 110px; height: 110px; background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 50px; box-shadow: 0 15px 35px rgba(108, 92, 231, 0.3);">
								<span class="dashicons dashicons-admin-network" style="font-size: 50px; width: 50px; height: 50px;"></span>
							</div>
						</div>
						
						<p style="font-weight: 800; font-size: 1.4em; margin: 0 0 10px 0; color: #1d2327;">SGOplus Group</p>
						<p style="font-size: 1em; color: #666; margin: 0 0 35px 0; line-height: 1.6;">Precision Engineered<br>WordPress Architectures</p>
						
						<div style="display: flex; flex-direction: column; gap: 18px; text-align: left; padding: 0;">
							<a href="https://sgoplus.one" target="_blank" style="text-decoration: none; color: #2271b1; display: flex; align-items: center; gap: 18px; font-weight: 700; padding: 18px; border-radius: 14px; background: #f6f7f7; transition: all 0.3s ease;">
								<span class="dashicons dashicons-admin-site" style="font-size: 24px; color: #6c5ce7;"></span> Official Website
							</a>
							<a href="https://discord.gg/WnkEKkZYFY" target="_blank" style="text-decoration: none; color: #fff; display: flex; align-items: center; gap: 18px; font-weight: 700; padding: 18px; border-radius: 14px; background: linear-gradient(90deg, #5865F2, #4752c4); box-shadow: 0 10px 20px rgba(88, 101, 242, 0.3); transition: all 0.3s ease;">
								<span class="dashicons dashicons-format-chat" style="font-size: 24px; color: #fff;"></span> Join Discord
							</a>
						</div>
						
						<hr style="margin: 40px 0; border: 0; border-top: 1px solid #f0f0f1;">
						
						<div style="font-size: 0.95em; color: #ccc;">
							<p style="margin: 0;">SGOplus SWK <strong>v1.0.0</strong></p>
							<p style="margin: 10px 0 0 0;">© 2026 SGOplus</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<style>
			.swk-settings-wrap .card h2 { font-weight: 800; margin-bottom: 20px; }
			.swk-settings-wrap a:hover { transform: translateY(-4px) scale(1.02); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
			.swk-custom-form-table th { width: 250px; }
			.swk-main-card { max-width: none !important; width: 100% !important; }
			@media (max-width: 1100px) {
				.swk-settings-wrap > div { flex-direction: column; }
				.swk-settings-wrap div[style*="width: 360px"] { width: 100% !important; }
				.swk-settings-wrap div[style*="width: 360px"] .card { position: static; }
			}
		</style>
		<?php
	}

	public function render_migration_page() {
		$migrator = new Migrator();
		?>
		<div class="wrap" style="max-width: 1200px;">
			<h1 style="font-weight: 800;"><?php esc_html_e( 'Data Migration', 'sgoplus-software-key' ); ?></h1>
			<?php $migrator->render_migration_ui(); ?>
		</div>
		<?php
	}

	public function render_guild_page() {
		?>
		<div class="wrap swk-guild-wrap" style="max-width: 1200px;">
			<div class="swk-guild-header">
				<h1>
					<span class="dashicons dashicons-welcome-learn-more"></span>
					<?php esc_html_e( 'SGOplus Software Key Guild', 'sgoplus-software-key' ); ?>
				</h1>
				<p class="swk-guild-subtitle"><?php esc_html_e( 'Master your licensing system with professional guidance.', 'sgoplus-software-key' ); ?></p>
				
				<div class="swk-lang-switcher">
					<button class="swk-lang-btn active" data-lang="en"><?php echo esc_html__( 'English', 'sgoplus-software-key' ); ?></button>
					<button class="swk-lang-btn" data-lang="zh"><?php echo esc_html__( '繁體中文', 'sgoplus-software-key' ); ?></button>
				</div>
			</div>

			<div class="swk-guild-content">
				<!-- English Content -->
				<div class="swk-guild-pane active" id="pane-en">
					<div class="swk-intro-card">
						<h2><span class="dashicons dashicons-star-filled"></span> <?php echo esc_html__( 'REST API Usage', 'sgoplus-software-key' ); ?></h2>
						<p><?php echo esc_html__( 'Our API supports 3 main actions: activate, deactivate, and check.', 'sgoplus-software-key' ); ?></p>
						
						<div class="swk-api-docs">
							<p><strong>Endpoint:</strong> <code><?php echo esc_url( home_url( '/wp-json/sgoplus-license/v1/verify' ) ); ?></code></p>
							<p><strong>Method:</strong> <code>POST</code></p>
							<p><strong>Required Parameters:</strong></p>
							<ul style="list-style: disc; margin-left: 20px;">
								<li><code>license_key</code>: Your software license key</li>
								<li><code>user_email</code>: The licensee's email address</li>
								<li><code>secret_key</code>: <?php echo esc_html( get_option( 'swk_secret_key', 'SET IN SETTINGS' ) ); ?></li>
								<li><code>action</code>: <code>activate</code> | <code>deactivate</code> | <code>check</code></li>
								<li><code>domain</code>: The URL of the client site (required for activate/deactivate)</li>
							</ul>
						</div>

						<h3 style="margin-top: 25px;"><?php echo esc_html__( 'PHP Example (Activation)', 'sgoplus-software-key' ); ?></h3>
						<pre class="swk-code-block">
$response = wp_remote_post( '<?php echo esc_url( home_url( '/wp-json/sgoplus-license/v1/verify' ) ); ?>', [
    'body' => [
        'action'      => 'activate',
        'license_key' => 'YOUR-KEY',
        'user_email'  => 'customer@example.com',
        'secret_key'  => '<?php echo esc_html( get_option( 'swk_secret_key', 'YOUR-SECRET' ) ); ?>',
        'domain'      => 'https://client-site.com'
    ]
]);
$data = json_decode( wp_remote_retrieve_body( $response ) );
</pre>
					</div>
				</div>

				<!-- Traditional Chinese Content -->
				<div class="swk-guild-pane" id="pane-zh">
					<div class="swk-intro-card">
						<h2><span class="dashicons dashicons-star-filled"></span> <?php echo esc_html__( 'REST API 使用指南', 'sgoplus-software-key' ); ?></h2>
						<p><?php echo esc_html__( '我們的 API 支援三種主要操作：啟動 (activate)、停用 (deactivate) 和檢查 (check)。', 'sgoplus-software-key' ); ?></p>
						
						<div class="swk-api-docs">
							<p><strong>請求網址 (Endpoint):</strong> <code><?php echo esc_url( home_url( '/wp-json/sgoplus-license/v1/verify' ) ); ?></code></p>
							<p><strong>請求方法 (Method):</strong> <code>POST</code></p>
							<p><strong>必要參數:</strong></p>
							<ul style="list-style: disc; margin-left: 20px;">
								<li><code>license_key</code>: 您的軟體授權密鑰</li>
								<li><code>user_email</code>: 授權持有者的電子郵件</li>
								<li><code>secret_key</code>: <?php echo esc_html( get_option( 'swk_secret_key', '請至設定頁面配置' ) ); ?></li>
								<li><code>action</code>: <code>activate</code> | <code>deactivate</code> | <code>check</code></li>
								<li><code>domain</code>: 客戶端站點網址 (啟動/停用時必填)</li>
							</ul>
						</div>

						<h3 style="margin-top: 25px;"><?php echo esc_html__( 'PHP 範例代碼 (啟動授權)', 'sgoplus-software-key' ); ?></h3>
						<pre class="swk-code-block">
$response = wp_remote_post( '<?php echo esc_url( home_url( '/wp-json/sgoplus-license/v1/verify' ) ); ?>', [
    'body' => [
        'action'      => 'activate',
        'license_key' => '您的密鑰',
        'user_email'  => 'customer@example.com',
        'secret_key'  => '<?php echo esc_html( get_option( 'swk_secret_key', '您的安全密鑰' ) ); ?>',
        'domain'      => 'https://client-site.com'
    ]
]);
$data = json_decode( wp_remote_retrieve_body( $response ) );
</pre>
					</div>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($){
			$('.swk-lang-btn').click(function(){
				var lang = $(this).data('lang');
				$('.swk-lang-btn').removeClass('active');
				$(this).addClass('active');
				$('.swk-guild-pane').removeClass('active');
				$('#pane-' + lang).addClass('active');
			});
		});
		</script>

		<style>
			.swk-guild-header { 
				text-align: center; 
				margin-bottom: 40px; 
				background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); 
				padding: 70px 20px; 
				border-radius: 24px; 
				color: #fff; 
				box-shadow: 0 15px 35px rgba(108, 92, 231, 0.25); 
				position: relative;
				overflow: hidden;
			}
			.swk-guild-header::before {
				content: '';
				position: absolute;
				top: -50%;
				left: -50%;
				width: 200%;
				height: 200%;
				background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
				pointer-events: none;
			}
			.swk-guild-header h1 { 
				color: #fff; 
				font-size: 2.8em; 
				font-weight: 800; 
				margin: 0; 
				display: flex; 
				align-items: center; 
				justify-content: center; 
				gap: 15px; 
				text-shadow: 0 2px 10px rgba(0,0,0,0.1);
			}
			.swk-guild-subtitle { font-size: 1.3em; opacity: 0.95; margin-top: 15px; font-weight: 500; }
			
			.swk-lang-switcher { 
				margin-top: 35px; 
				display: inline-flex; 
				background: rgba(255, 255, 255, 0.15); 
				backdrop-filter: blur(10px); 
				padding: 6px; 
				border-radius: 14px; 
				border: 1px solid rgba(255, 255, 255, 0.2);
				box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
			}
			.swk-lang-btn { 
				background: transparent; 
				border: none; 
				color: #fff; 
				padding: 10px 24px; 
				cursor: pointer; 
				font-weight: 600; 
				border-radius: 10px; 
				transition: all 0.3s ease;
				font-size: 0.95em;
			}
			.swk-lang-btn:hover { background: rgba(255, 255, 255, 0.1); }
			.swk-lang-btn.active { 
				background: #fff; 
				color: #6c5ce7; 
				box-shadow: 0 4px 15px rgba(0,0,0,0.1);
			}

			.swk-guild-pane { display: none; animation: fadeIn 0.4s ease-out; }
			.swk-guild-pane.active { display: block; }
			@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

			.swk-intro-card { 
				background: #fff; 
				padding: 45px; 
				border-radius: 24px; 
				box-shadow: 0 10px 40px rgba(0,0,0,0.04); 
				margin-top: 30px; 
				border-left: 8px solid #6c5ce7; 
			}
			.swk-intro-card h2 { font-size: 1.8em; font-weight: 800; color: #1d2327; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; }
			.swk-api-docs { background: #f8f9fa; padding: 25px; border-radius: 16px; margin-top: 20px; border: 1px solid #e9ecef; }
			.swk-code-block { background: #2d3436; color: #fab1a0; padding: 25px; border-radius: 16px; overflow-x: auto; font-size: 0.95em; line-height: 1.6; border: 1px solid #1d2327; box-shadow: 0 10px 20px rgba(0,0,0,0.2); }
		</style>
		<?php
	}
}
