<?php

namespace SGOplus\WP_Software_Key;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
	}

	public function add_settings_page() {
		add_menu_page(
			esc_html__( 'Software Key+', 'sgoplus-wp-software-key' ),
			esc_html__( 'Software Key+', 'sgoplus-wp-software-key' ),
			'manage_options',
			'swk-settings',
			array( $this, 'render_settings_page' ),
			'dashicons-admin-network',
			30
		);

		add_submenu_page(
			'swk-settings',
			esc_html__( 'Migration', 'sgoplus-wp-software-key' ),
			esc_html__( 'Migration', 'sgoplus-wp-software-key' ),
			'manage_options',
			'swk-migration',
			array( $this, 'render_migration_page' )
		);

		add_submenu_page(
			'swk-settings',
			esc_html__( 'Guild', 'sgoplus-wp-software-key' ),
			esc_html__( 'Guild', 'sgoplus-wp-software-key' ),
			'manage_options',
			'swk-guild',
			array( $this, 'render_guild_page' )
		);
	}

	public function render_settings_page() {
		?>
		<div class="wrap swk-settings-wrap" style="max-width: 1200px;">
			<h1 style="margin-bottom: 20px;"><?php esc_html_e( 'SGOplus Software Key Settings', 'sgoplus-wp-software-key' ); ?></h1>
			
			<div style="display: flex; gap: 20px; align-items: flex-start;">
				<!-- Main Content -->
				<div style="flex: 1; min-width: 0;">
					<div class="card" style="margin: 0 0 20px 0; padding: 25px; border-radius: 12px; border: 1px solid #e5e5e5; box-shadow: 0 2px 4px rgba(0,0,0,0.02); background: #fff;">
						<h2 style="margin-top: 0; font-size: 1.3em; display: flex; align-items: center; gap: 10px;">
							<span class="dashicons dashicons-admin-settings" style="color: #6c5ce7;"></span> 
							<?php esc_html_e( 'General Configuration', 'sgoplus-wp-software-key' ); ?>
						</h2>
						<p><?php esc_html_e( 'Configure your global license management settings here.', 'sgoplus-wp-software-key' ); ?></p>
						<hr>
						<p style="text-align: center; color: #999; padding: 20px;"><?php esc_html_e( 'Setting options will be added in the next update.', 'sgoplus-wp-software-key' ); ?></p>
					</div>
				</div>

				<!-- Sidebar -->
				<div style="width: 320px; flex-shrink: 0;">
					<div class="card" style="padding: 30px 20px; border-radius: 15px; text-align: center; border: 1px solid #e5e5e5; box-shadow: 0 4px 20px rgba(0,0,0,0.04); background: #fff; margin: 0; position: sticky; top: 50px;">
						<h3 style="margin-top: 0; color: #1d2327; font-size: 1.2em;">Developer Hub</h3>
						<div style="margin: 20px 0;">
							<div style="width: 90px; height: 90px; background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 40px; box-shadow: 0 5px 15px rgba(108, 92, 231, 0.2);">
								<span class="dashicons dashicons-admin-network" style="font-size: 40px; width: 40px; height: 40px;"></span>
							</div>
						</div>
						
						<p style="font-weight: 800; font-size: 1.2em; margin: 0 0 5px 0; color: #1d2327;">SGOplus Group</p>
						<p style="font-size: 0.9em; color: #666; margin: 0 0 25px 0; line-height: 1.4;">Premium WordPress Solutions<br>Crafted with Excellence</p>
						
						<div style="display: flex; flex-direction: column; gap: 12px; text-align: left; padding: 0 10px;">
							<a href="https://sgoplus.one" target="_blank" style="text-decoration: none; color: #2271b1; display: flex; align-items: center; gap: 12px; font-weight: 500; padding: 8px; border-radius: 8px; background: #f6f7f7;">
								<span class="dashicons dashicons-admin-site" style="font-size: 20px; color: #6c5ce7;"></span> Official Website
							</a>
							<a href="https://discord.gg/WnkEKkZYFY" target="_blank" style="text-decoration: none; color: #fff; display: flex; align-items: center; gap: 12px; font-weight: 600; padding: 10px; border-radius: 8px; background: #5865F2; box-shadow: 0 4px 10px rgba(88, 101, 242, 0.2);">
								<span class="dashicons dashicons-format-chat" style="font-size: 20px; color: #fff;"></span> Join Discord
							</a>
						</div>
						
						<hr style="margin: 25px 0; border: 0; border-top: 1px solid #f0f0f1;">
						
						<div style="font-size: 0.85em; color: #999;">
							<p style="margin: 0;">SGOplus SWK <strong>v1.0.0</strong></p>
							<p style="margin: 5px 0 0 0;">© 2026 SGOplus</p>
						</div>
					</div>
				</div>
			</div>
		</div>
		<style>
			.swk-settings-wrap .card h2 { font-weight: 700; margin-bottom: 15px; }
			@media (max-width: 900px) {
				.swk-settings-wrap > div { flex-direction: column; }
				.swk-settings-wrap div[style*="width: 320px"] { width: 100% !important; }
			}
		</style>
		<?php
	}

	public function render_migration_page() {
		$migrator = new Migrator();
		?>
		<div class="wrap" style="max-width: 1200px;">
			<h1><?php esc_html_e( 'Data Migration', 'sgoplus-wp-software-key' ); ?></h1>
			<?php $migrator->render_migration_ui(); ?>
		</div>
		<?php
	}

	public function render_guild_page() {
		// Inherit the exact premium Guild design from WP Share Service
		?>
		<div class="wrap swk-guild-wrap" style="max-width: 1200px;">
			<div class="swk-guild-header">
				<h1>
					<span class="dashicons dashicons-welcome-learn-more"></span>
					<?php esc_html_e( 'SGOplus Software Key Guild', 'sgoplus-wp-software-key' ); ?>
				</h1>
				<p class="swk-guild-subtitle"><?php esc_html_e( 'Master your licensing system with professional guidance.', 'sgoplus-wp-software-key' ); ?></p>
				
				<div class="swk-lang-switcher">
					<button class="swk-lang-btn active" data-lang="en"><?php echo esc_html__( 'English', 'sgoplus-wp-software-key' ); ?></button>
					<button class="swk-lang-btn" data-lang="zh"><?php echo esc_html__( '繁體中文', 'sgoplus-wp-software-key' ); ?></button>
				</div>
			</div>

			<div class="swk-guild-content">
				<div class="swk-guild-pane active" id="pane-en">
					<div class="swk-intro-card">
						<h2><span class="dashicons dashicons-star-filled"></span> <?php echo esc_html__( 'REST API Usage', 'sgoplus-wp-software-key' ); ?></h2>
						<p><?php echo esc_html__( 'Verify licenses from any application using our secure REST API endpoint:', 'sgoplus-wp-software-key' ); ?></p>
						<code style="display: block; background: #f0f0f1; padding: 15px; margin-top: 15px; border-radius: 8px; border-left: 4px solid #6c5ce7;">POST /wp-json/sgoplus-license/v1/verify</code>
					</div>
				</div>
			</div>
		</div>
		<style>
			.swk-guild-header { text-align: center; margin-bottom: 40px; background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%); padding: 60px 20px; border-radius: 20px; color: #fff; box-shadow: 0 10px 30px rgba(108, 92, 231, 0.2); }
			.swk-guild-header h1 { color: #fff; font-size: 2.5em; font-weight: 800; margin: 0; display: flex; align-items: center; justify-content: center; gap: 15px; }
			.swk-guild-subtitle { font-size: 1.2em; opacity: 0.9; margin-top: 10px; }
			.swk-intro-card { background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.03); margin-top: 30px; border-left: 6px solid #6c5ce7; }
		</style>
		<?php
	}
}
