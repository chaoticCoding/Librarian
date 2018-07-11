<?php


namespace core;

/** Working /7/16/2015
 * Class eventDispatcher
 *
 * Event Dispatched for Spl Observers, Spl Observer prevents use of static class
 * @package core
 ***/
class eventDispatcher extends \core\coreInc implements \SplSubject {
	private $observers = [];
	private $event;
	private $message;

	/** TODO Returns information about this module
	 *
	 */
	public static $_info = [
		'name' => "eventDispatcher",
		'dispatcher' => true,
	];

	/** Working /7/16/2015
	 * Attach SplObserver class to SplSubject
	 *
	 * @param \SplObserver $observer
	 */
	public function attach(\SplObserver $observer) {

		$this->observers[] = $observer;
	}

	/** Working /7/16/2015
	 * remove SplObserver class from SplSubject
	 *
	 * @param \SplObserver $observer
	 */
	public function detach(\SplObserver $observer) {

		$key = array_search($observer, $this->observers, true);

		if($key){
			unset($this->observers[$key]);
		}
	}
	/** Working /7/16/2015
	 * Handel new inputs by setting $content and then triggering notify function
	 *
	 * @param $event
	 * @param $message
	 */
	public function NewEvent($event, $message) {
		$this->event = strtolower($event);
		$this->message = $message;

		$this->notify();
	}

	/** Working /7/16/2015
	 * Gets Current Content values, Used by Observers
	 * @return array( $event, $message 0
	 */
	public function getEvent() {
		return [$this->event => $this->message];
	}

	/** Working /7/16/2015
	 * notify observers of new inputs
	 ***/
	public function notify() {
		// Loops though all observers and sends updates one at a time.
		foreach ($this->observers as $value) {

			$value->update($this);
		}
	}
}