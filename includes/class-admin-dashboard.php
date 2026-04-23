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
 */
class Admin_Dashboard {

	/**
	 * Our menu page slugs.
	 */
	private static $slugs = array(
		'sgoplus-swk-dashboard',
		'sgoplus-swk-dashboard-add',
		'sgoplus-swk-dashboard-logs',
		'sgoplus-swk-dashboard-settings',
		'sgoplus-swk-dashboard-guide',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Check if the current page is one of ours.
	 * Reads the 'page' query parameter directly — the most reliable method,
	 * independent of WordPress hook-suffix generation quirks.
	 *
	 * @return bool
	 */
	private function is_our_page() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		return in_array( $page, self::$slugs, true );
	}

	/**
	 * Enqueue Assets
	 *
	 * @param string $hook_suffix Current admin page hook suffix (unused — we use is_our_page()).
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! $this->is_our_page() ) {
			return;
		}

		$dist_path     = SGOPLUS_SWK_PATH . 'assets/dist/';
		$dist_url      = SGOPLUS_SWK_URL . 'assets/dist/';
		$manifest_file = $dist_path . '.vite/manifest.json';

		if ( ! file_exists( $manifest_file ) ) {
			add_action(
				'admin_notices',
				static function () {
					echo '<div class="notice notice-error"><p><strong>SGOplus Software Key:</strong> ' .
						esc_html__( 'Frontend assets not found. Please reinstall the plugin.', 'sgoplus-software-key' ) .
						'</p></div>';
				}
			);
			return;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$raw      = file_get_contents( $manifest_file );
		$manifest = json_decode( $raw, true );

		if ( ! is_array( $manifest ) ) {
			return;
		}

		// ── Locate JS entry ───────────────────────────────────────────────────
		$js_file = null;
		foreach ( $manifest as $item ) {
			if ( is_array( $item ) && ! empty( $item['isEntry'] ) && ! empty( $item['file'] ) ) {
				$js_file = $item['file'];
				break;
			}
		}

		// Fallback: look for main.js directly.
		if ( null === $js_file && file_exists( $dist_path . 'assets/main.js' ) ) {
			$js_file = 'assets/main.js';
		}

		if ( null === $js_file ) {
			return;
		}

		// ── Enqueue JS ───────────────────────────────────────────────────────
		wp_enqueue_script(
			'sgoplus-swk-admin',
			$dist_url . $js_file,
			array(),
			SGOPLUS_SWK_VERSION,
			true
		);

		// ── Locate and enqueue CSS ────────────────────────────────────────────
		$css_enqueued = false;

		// Strategy 1: entry-level css array (ESM builds).
		foreach ( $manifest as $item ) {
			if ( is_array( $item ) && ! empty( $item['isEntry'] ) && ! empty( $item['css'] ) ) {
				foreach ( $item['css'] as $i => $css_file ) {
					wp_enqueue_style(
						'sgoplus-swk-admin-css-' . $i,
						$dist_url . $css_file,
						array(),
						SGOPLUS_SWK_VERSION
					);
					$css_enqueued = true;
				}
			}
		}

		// Strategy 2: top-level manifest keys ending in .css (IIFE builds).
		if ( ! $css_enqueued ) {
			foreach ( $manifest as $item ) {
				if ( is_array( $item ) && ! empty( $item['file'] ) && substr( $item['file'], -4 ) === '.css' ) {
					wp_enqueue_style(
						'sgoplus-swk-admin-css',
						$dist_url . $item['file'],
						array(),
						SGOPLUS_SWK_VERSION
					);
					$css_enqueued = true;
				}
			}
		}

		// Strategy 3: direct file fallback.
		if ( ! $css_enqueued && file_exists( $dist_path . 'assets/style.css' ) ) {
			wp_enqueue_style(
				'sgoplus-swk-admin-css',
				$dist_url . 'assets/style.css',
				array(),
				SGOPLUS_SWK_VERSION
			);
		}

		// ── Provide data to React app ─────────────────────────────────────────
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
