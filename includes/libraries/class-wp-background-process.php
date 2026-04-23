<?php
/**
 * WP_Background_Process
 *
 * @package SGOplus\SoftwareKey\Libraries
 */

namespace SGOplus\SoftwareKey\Libraries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-wp-async-request.php';

/**
 * Abstract WP_Background_Process class.
 *
 * @abstract
 * @extends WP_Async_Request
 */
abstract class WP_Background_Process extends WP_Async_Request {

	/**
	 * Action
	 *
	 * @var string
	 * @access protected
	 */
	protected $action = 'background_process';

	/**
	 * Start time of current process.
	 *
	 * @var int
	 * @access protected
	 */
	protected $start_time = 0;

	/**
	 * Cron_hook_identifier
	 *
	 * @var string
	 * @access protected
	 */
	protected $cron_hook_identifier;

	/**
	 * Cron_interval_identifier
	 *
	 * @var string
	 * @access protected
	 */
	protected $cron_interval_identifier;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->cron_hook_identifier     = $this->identifier . '_cron';
		$this->cron_interval_identifier = $this->identifier . '_cron_interval';

		add_action( $this->cron_hook_identifier, array( $this, 'handle_cron_healthcheck' ) );
		add_filter( 'cron_schedules', array( $this, 'schedule_cron_healthcheck' ) );
	}

	/**
	 * Dispatch background process.
	 *
	 * @return array|\WP_Error
	 */
	public function dispatch() {
		// Create a snapshot of the queue.
		$this->save();

		return parent::dispatch();
	}

	/**
	 * Push to queue.
	 *
	 * @param mixed $data Data.
	 *
	 * @return $this
	 */
	public function push_to_queue( $data ) {
		$this->data[] = $data;

		return $this;
	}

	/**
	 * Save queue.
	 *
	 * @return $this
	 */
	public function save() {
		$key = $this->get_batch_key();

		if ( ! empty( $this->data ) ) {
			update_site_option( $key, $this->data );
		}

		return $this;
	}

	/**
	 * Get batch key.
	 *
	 * @return string
	 */
	protected function get_batch_key() {
		return $this->identifier . '_batch';
	}

	/**
	 * Handle background process.
	 */
	protected function handle() {
		$this->start_time = time();

		$this->data = get_site_option( $this->get_batch_key(), array() );

		foreach ( $this->data as $key => $value ) {
			if ( $this->time_exceeded() || $this->memory_exceeded() ) {
				break;
			}

			$result = $this->task( $value );

			if ( false !== $result ) {
				$this->data[ $key ] = $result;
			} else {
				unset( $this->data[ $key ] );
			}
		}

		$this->save();
		$this->memory_exceeded();

		if ( ! empty( $this->data ) ) {
			$this->dispatch();
		} else {
			$this->complete();
		}
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass or false if completed.
	 *
	 * @param mixed $item Queue item to process.
	 *
	 * @return mixed
	 */
	abstract protected function task( $item );

	/**
	 * Complete
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$this->clear_scheduled_event();
	}

	/**
	 * Memory exceeded
	 *
	 * @return bool
	 */
	protected function memory_exceeded() {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return apply_filters( $this->identifier . '_memory_exceeded', $return );
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	protected function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || -1 === intval( $memory_limit ) ) {
			$memory_limit = '256M';
		}

		return $this->convert_hr_to_bytes( $memory_limit );
	}

	/**
	 * Convert human readable memory to bytes.
	 *
	 * @param string|int $value Value.
	 *
	 * @return int
	 */
	protected function convert_hr_to_bytes( $value ) {
		$value = (string) $value;
		$value = trim( $value );
		if ( empty( $value ) ) {
			return 0;
		}
		
		$last  = strtolower( $value[ strlen( $value ) - 1 ] );
		$value = intval( $value );

		switch ( $last ) {
			case 'g':
				$value *= 1024;
			case 'm':
				$value *= 1024;
			case 'k':
				$value *= 1024;
		}

		return $value;
	}

	/**
	 * Time exceeded.
	 *
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( $this->identifier . '_default_time_limit', 20 ); // 20 seconds
		$return = false;

		if ( time() >= $finish ) {
			$return = true;
		}

		return apply_filters( $this->identifier . '_time_exceeded', $return );
	}

	/**
	 * Schedule cron healthcheck
	 *
	 * @param array $schedules Schedules.
	 *
	 * @return array
	 */
	public function schedule_cron_healthcheck( $schedules ) {
		$interval = apply_filters( $this->cron_interval_identifier, 5 );

		if ( ! isset( $schedules[ $this->cron_interval_identifier ] ) ) {
			$schedules[ $this->cron_interval_identifier ] = array(
				'interval' => $interval * 60,
				'display'  => sprintf( __( 'Every %d Minutes', 'sgoplus-software-key' ), $interval ),
			);
		}

		return $schedules;
	}

	/**
	 * Handle cron healthcheck
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Process is already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// Queue is empty.
			$this->clear_scheduled_event();

			return;
		}

		$this->dispatch();
	}

	/**
	 * Is process running?
	 *
	 * @return bool
	 */
	protected function is_process_running() {
		if ( get_site_option( $this->identifier . '_process_lock' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Is queue empty?
	 *
	 * @return bool
	 */
	protected function is_queue_empty() {
		$data = get_site_option( $this->get_batch_key() );

		return empty( $data );
	}

	/**
	 * Clear scheduled event
	 */
	protected function clear_scheduled_event() {
		wp_clear_scheduled_hook( $this->cron_hook_identifier );
	}

}
