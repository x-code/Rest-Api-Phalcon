<?php

namespace Events\Api;

use Interfaces\IEvent as IEvent;

class SampleAuthenticate extends \Phalcon\Events\Manager implements IEvent {

	/**
	 * Constructor
	 *
	 * @param object
	 * @param string
	 */
	public function __construct() {

		// Add Event to validate message
		$this->handleEvent();
	}

	/**
	 * Setup an Event
	 *
	 * Phalcon event to make sure client sends a valid message
	 * @return FALSE|void
	 */
	public function handleEvent() {

		$this->attach('micro', function ($event, $app) {
			if ($event->getType() == 'beforeExecuteRoute') {
				error_log("before armn oke" . $app->request->getRawBody());
				// error_log($app->request->getHeader('x-token'));
			}

			if ($event->getType() == 'afterExecuteRoute') {
				error_log("after armn oke");

			}
		});
	}
}
