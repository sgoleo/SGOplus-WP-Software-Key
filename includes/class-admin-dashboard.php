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
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add Menu Page
	 */
	public function add_menu_page() {
		// 1. Main Menu (Parent)
		add_menu_page(
			__( 'SGOplus Software Key', 'sgoplus-software-key' ),
			__( 'Software Key+', 'sgoplus-software-key' ),
			'manage_options',
			'sgoplus-swk-dashboard',
			array( $this, 'render_dashboard' ),
			'dashicons-shield-lock',
			26
		);

		// 2. Submenus
		add_submenu_page(
			'sgoplus-swk-dashboard',
			__( 'Licenses - Software Key+', 'sgoplus-software-key' ),
			__( 'Licenses', 'sgoplus-software-key' ),
			'manage_options',
			'sgoplus-swk-dashboard',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'sgoplus-swk-dashboard',
			__( 'Add New - Software Key+', 'sgoplus-software-key' ),
			__( 'Add New', 'sgoplus-software-key' ),
			'manage_options',
			'sgoplus-swk-dashboard&view=add-new',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'sgoplus-swk-dashboard',
			__( 'Logs - Software Key+', 'sgoplus-software-key' ),
			__( 'Activation Logs', 'sgoplus-software-key' ),
			'manage_options',
			'sgoplus-swk-dashboard&view=logs',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'sgoplus-swk-dashboard',
			__( 'Settings - Software Key+', 'sgoplus-software-key' ),
			__( 'Settings', 'sgoplus-software-key' ),
			'manage_options',
			'sgoplus-swk-dashboard&view=settings',
			array( $this, 'render_dashboard' )
		);

		add_submenu_page(
			'sgoplus-swk-dashboard',
			__( 'Guide - Software Key+', 'sgoplus-software-key' ),
			__( 'Guide', 'sgoplus-software-key' ),
			'manage_options',
			'sgoplus-swk-dashboard&view=guide',
			array( $this, 'render_dashboard' )
		);
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
