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
 * Handles the React-based admin interface asset enqueueing.
 */
class Admin_Dashboard {

	/**
	 * Constructor — registers the enqueue hook at the correct time.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Enqueue Assets
	 *
	 * Called by admin_enqueue_scripts with the current page hook suffix.
	 * This is the ONLY correct place to call wp_enqueue_script/style.
	 *
	 * @param string $hook_suffix Current admin page hook suffix provided by WP.
	 */
	public function enqueue_assets( $hook_suffix ) {
		/*
		 * WordPress generates hook suffixes as follows:
		 *   Top-level page  → toplevel_page_{menu-slug}
		 *   Sub-pages       → {sanitized-parent-menu-title}_page_{submenu-slug}
		 *
		 * Menu title "Software Key+" sanitises to "software-key-2" or similar,
		 * so we match by slug pattern instead of hard-coding the parent title.
		 *
		 * Strategy: match any hook that contains our menu slug.
		 */
		if ( strpos( $hook_suffix, 'sgoplus-swk-dashboard' ) === false ) {
			return;
		}

		$dist_path     = SGOPLUS_SWK_PATH . 'assets/dist/';
		$dist_url      = SGOPLUS_SWK_URL . 'assets/dist/';
		$manifest_file = $dist_path . '.vite/manifest.json';

		if ( ! file_exists( $manifest_file ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-warning"><p>' .
						esc_html__( 'SGOplus Software Key: Frontend assets not built. Run ', 'sgoplus-software-key' ) .
						'<code>npm run build</code>.</p></div>';
				}
			);
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$manifest = json_decode( file_get_contents( $manifest_file ), true );

		if ( ! is_array( $manifest ) || ! isset( $manifest['src/main.tsx'] ) ) {
			return;
		}

		$entry = $manifest['src/main.tsx'];

		// Enqueue JS.
		wp_enqueue_script(
			'sgoplus-swk-admin',
			$dist_url . $entry['file'],
			array(),
			SGOPLUS_SWK_VERSION,
			true
		);

		// Vite builds ES modules — add type="module" to the script tag.
		add_filter(
			'script_loader_tag',
			static function ( $tag, $handle ) {
				if ( 'sgoplus-swk-admin' !== $handle ) {
					return $tag;
				}
				// Avoid double-adding the attribute.
				if ( strpos( $tag, 'type="module"' ) !== false ) {
					return $tag;
				}
				return str_replace( '<script ', '<script type="module" ', $tag );
			},
			10,
			2
		);

		// Enqueue CSS.
		if ( ! empty( $entry['css'] ) ) {
			foreach ( $entry['css'] as $css_file ) {
				wp_enqueue_style(
					'sgoplus-swk-admin-' . md5( $css_file ),
					$dist_url . $css_file,
					array(),
					SGOPLUS_SWK_VERSION
				);
			}
		}

		// Provide REST API root + nonce to the React app via inline JS.
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
