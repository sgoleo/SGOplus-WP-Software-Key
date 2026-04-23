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
		// Enqueueing is now handled manually by the Plugin class or via hooks
	}

	/**
	 * Render Dashboard Container
	 */
	public function render_dashboard() {
		echo '<div id="sgoplus-swk-admin-root"></div>';
	}

	/**
	 * Render Dashboard Container
	 */
	public function render_dashboard() {
		echo '<div id="sgoplus-swk-admin-root"></div>';
	}

	/**
	 * Enqueue Assets
	 */
	public function enqueue_assets( $hook ) {
		if ( 'toplevel_page_sgoplus-swk-dashboard' !== $hook ) {
			return;
		}

		$dist_path = SGOPLUS_SWK_PATH . 'assets/dist/';
		$dist_url  = SGOPLUS_SWK_URL . 'assets/dist/';
		$manifest_file = $dist_path . '.vite/manifest.json';

		// In development mode, you might want to load from Vite dev server
		// For now, we assume a production build exists or we provide a fallback
		if ( file_exists( $manifest_file ) ) {
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
			// Fallback or Dev mode message
			add_action( 'admin_notices', function() {
				echo '<div class="notice notice-warning"><p>SGOplus Software Key: Frontend assets not built. Please run <code>npm run build</code>.</p></div>';
			} );
		}

		// Localize Script for API access
		wp_localize_script( 'sgoplus-swk-admin', 'sgoplusSwkData', array(
			'root'  => esc_url_raw( rest_url( 'sgoplus-swk/v1' ) ),
			'nonce' => wp_create_nonce( 'wp_rest' ),
		) );
	}
}
