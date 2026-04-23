<?php
/**
 * Admin Dashboard Class
 *
 * @package SGOplus\SoftwareKey
 */

namespace SGOplus\SoftwareKey;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Dashboard
 * Handles the React-based admin interface.
 */
class Admin_Dashboard {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Enqueueing is now handled manually by the Plugin class via render_dashboard().
	}

	/**
	 * Enqueue Assets
	 *
	 * @param string $hook Current admin page hook suffix.
	 */
	public function enqueue_assets( $hook ) {
		// Allow all sub-pages of the sgoplus-swk-dashboard slug to load assets.
		$allowed_hooks = array(
			'toplevel_page_sgoplus-swk-dashboard',
			'software-key_page_sgoplus-swk-dashboard-add',
			'software-key_page_sgoplus-swk-dashboard-logs',
			'software-key_page_sgoplus-swk-dashboard-settings',
			'software-key_page_sgoplus-swk-dashboard-guide',
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		$dist_path     = SGOPLUS_SWK_PATH . 'assets/dist/';
		$dist_url      = SGOPLUS_SWK_URL . 'assets/dist/';
		$manifest_file = $dist_path . '.vite/manifest.json';

		if ( file_exists( $manifest_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$manifest = json_decode( file_get_contents( $manifest_file ), true );

			if ( isset( $manifest['src/main.tsx'] ) ) {
				$entry = $manifest['src/main.tsx'];

				// Enqueue JS
				wp_enqueue_script(
					'sgoplus-swk-admin',
					$dist_url . $entry['file'],
					array(),
					SGOPLUS_SWK_VERSION,
					true
				);

				// Enqueue CSS
				if ( isset( $entry['css'] ) ) {
					foreach ( $entry['css'] as $css_file ) {
						wp_enqueue_style(
							'sgoplus-swk-admin-' . md5( $css_file ),
							$dist_url . $css_file,
							array(),
							SGOPLUS_SWK_VERSION
						);
					}
				}
			}
		} else {
			// Fallback notice when the frontend hasn't been built yet.
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-warning"><p>' .
						esc_html__( 'SGOplus Software Key: Frontend assets not built. Please run ', 'sgoplus-software-key' ) .
						'<code>npm run build</code>.</p></div>';
				}
			);
		}

		// Localize Script for API access
		wp_localize_script(
			'sgoplus-swk-admin',
			'sgoplusSwkData',
			array(
				'root'  => esc_url_raw( rest_url( 'sgoplus-swk/v1' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
