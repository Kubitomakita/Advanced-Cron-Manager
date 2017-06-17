<?php
/**
 * Events class
 * Used to handle collection of events
 */

namespace underDEV\AdvancedCronManager\Cron;

class Events {

	/**
	 * Registered events
	 * @var array
	 */
	private $events = array();

	/**
	 * Protected events slugs
	 * @var array
	 */
	private $protected_events = array();

	public function __construct() {

		// protected events registered by WordPress' core
		$this->protected_events = array(
			'wp_version_check', 'wp_update_plugins', 'wp_update_themes',
			'wp_scheduled_delete', 'wp_scheduled_auto_draft_delete'
		);

	}

	/**
	 * Gets all registered events
	 * Supports lazy loading
	 * @param  boolean $force if refresh stored events
	 * @return array          registered events
	 */
	public function get_events( $force = false ) {

		if ( empty( $this->events ) || $force ) {

			// wp_schedule_single_event( time() + 360, 'test_event2' );

			$this->events = array();

			foreach ( _get_cron_array() as $timestamp => $events ) {

				foreach ( $events as $event_hook => $event_args ) {

					if ( in_array( $event_hook, $this->protected_events ) ) {
						$protected = true;
					} else {
						$protected = false;
					}

					foreach ( $event_args as $event ) {

						$interval = isset( $event['interval'] ) ? $event['interval'] : 0;

						$this->events[] = new Object\Event( $event_hook, $event['schedule'], $interval, $event['args'], $timestamp, $protected );

					}

				}

			}

			usort( $this->events, array( $this, 'compare_event_next_calls' ) );

		}

		return $this->events;

	}

	/**
	 * Counts the total number of events
	 * @return int
	 */
	public function count() {
		return count( $this->get_events() );
	}

	public function compare_event_next_calls( $e1, $e2 ) {

		if ( $e1->next_call == $e2->next_call ) {
			return 0;
		}

		return ( $e1->next_call < $e2->next_call ) ? -1 : 1;

	}

}