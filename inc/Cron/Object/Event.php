<?php
/**
 * Event class
 * Single instance of an event
 */

namespace underDEV\AdvancedCronManager\Cron\Object;

class Event {

	/**
	 * Event hook
	 * @var string
	 */
	private $hook;

	/**
	 * Event's schedule interval
	 * @var int
	 */
	private $interval;

	/**
	 * Event's schedule slug
	 * @var string
	 */
	private $schedule;

	/**
	 * Event's arguments
	 * @var array
	 */
	private $args = array();

	/**
	 * Event's next call timestamp
	 * @var int
	 */
	private $next_call;

	/**
	 * Protected
	 * @var bool
	 */
	private $protected;

	/**
	 * Instantine object
	 * @param boolean $protected if Schedule is protected
	 */
	public function __construct( $hook = null, $schedule = '', $interval = 0, $args = array(), $next_call = 0, $protected = false ) {

		if ( empty( $hook ) ) {
			trigger_error( 'Hook cannot be empty', E_USER_ERROR );
		}

		$this->hook      = $hook;
		$this->schedule  = $schedule;
		$this->interval  = $interval;
		$this->args      = $args;
		$this->next_call = $next_call;
		$this->protected = $protected;

	}

	/**
	 * Magic method
	 * @param  string $property Schedule property
	 * @return mixed            property value
	 */
	public function __get( $property ) {
        return $this->$property;
    }

    /**
     * Gets event's unique hash
     * @return string
     */
    public function get_hash() {
    	return substr( md5( $this->hook . $this->schedule . $this->interval . serialize( $this->args ) ), 0, 8 );
    }

    /**
     * Gets implementation code for event
     * @return string
     */
    public function get_implementation() {

    	$arguments = array();
    	foreach ( $this->args as $n => $arg ) {
    		$arguments[] = '$arg' . ( $n + 1 );
    	}
    	$arguments = empty( $arguments ) ? '' : ' ' . implode( ', ' , $arguments ) . ' ';

    	$function_name = 'cron_' . $this->hook . '_' . $this->get_hash();

    	$imp = '';

    	$imp .= 'function ' . $function_name . '(' . $arguments . ') {<br>';
    	$imp .= '&nbsp;&nbsp;&nbsp;&nbsp;// do stuff<br>';
    	$imp .= '}<br>';
    	$imp .= '<br>';
    	$imp .= "add_action( '" . $this->hook . "',  '" . $function_name . "', 10, " . count( $this->args ) . " );";

    	return $imp;

    }

}