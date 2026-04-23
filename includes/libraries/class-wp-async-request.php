<?php
/**
 * WP_Async_Request
 *
 * @package SGOplus\SoftwareKey\Libraries
 */

namespace SGOplus\SoftwareKey\Libraries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract WP_Async_Request class.
 */
abstract class WP_Async_Request {

	/**
	 * Prefix
	 *
	 * @var string
	 * @access protected
	 */
	protected $prefix = 'sgoplus_swk';

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'async_request';

	/**
	 * Identifier
	 *
	 * @var string
	 * @access protected
	 */
	protected $identifier;

	/**
	 * Data
	 *
	 * @var array
	 * @access protected
	 */
	protected $data = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->identifier = $this->prefix . '_' . $this->action;

		add_action( 'wp_ajax_' . $this->identifier, array( $this, 'maybe_handle' ) );
		add_action( 'wp_ajax_nopriv_' . $this->identifier, array( $this, 'maybe_handle' ) );
	}

	/**
	 * Set data used during the request.
	 *
	 * @param array $data Data.
	 *
	 * @return $this
	 */
	public function data( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * Dispatch the async request.
	 *
	 * @return array|\WP_Error
	 */
	public function dispatch() {
		$url  = add_query_arg( $this->get_query_args(), $this->get_query_url() );
		$args = $this->get_post_args();

		return wp_remote_post( esc_url_raw( $url ), $args );
	}

	/**
	 * Get query args.
	 *
	 * @return array
	 */
	protected function get_query_args() {
		if ( property_exists( $this, 'query_args' ) ) {
			return $this->query_args;
		}

		return array(
			'action' => $this->identifier,
			'nonce'  => wp_create_nonce( $this->identifier ),
		);
	}

	/**
	 * Get query URL.
	 *
	 * @return string
	 */
	protected function get_query_url() {
		if ( property_exists( $this, 'query_url' ) ) {
			return $this->query_url;
		}

		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Get post args.
	 *
	 * @return array
	 */
	protected function get_post_args() {
		if ( property_exists( $this, 'post_args' ) ) {
			return $this->post_args;
		}

		return array(
			'timeout'   => 0.01,
			'blocking'  => false,
			'body'      => $this->data,
			'cookies'   => $_COOKIE,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		);
	}

	/**
	 * Maybe handle the async request.
	 */
	public function maybe_handle() {
		// Don't lock up other requests.
		session_write_close();

		check_ajax_referer( $this->identifier, 'nonce' );

		if ( ! $this->is_allowed() ) {
			wp_die();
		}

		$this->handle();

		wp_die();
	}

	/**
	 * Is allowed?
	 *
	 * @return bool
	 */
	protected function is_allowed() {
		return true;
	}

	/**
	 * Handle the async request.
	 */
	abstract protected function handle();

}
