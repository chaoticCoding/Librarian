<?php

namespace core {

	/** TODO - Inprogress
	 *
	 * Class error
	 * Error Processor
	 * @package core
	 ***/
	class errors extends \core\coreObserver
	{

		private $_Settings;

		private $_Errors = [];

		/** TODO Returns information about this module
		 *
		 */
		public static $_info = [
			'name' => "Error",
			'listen' => 'eventDispatcher'
		];


		public function __construct($settings = null) {
			parent::__construct($settings);
			
			$this->_Settings = $settings;
		}


		/** TODO
		 *
		 * take error and handel's it somehow!
		 *
		 * @param $error [id]
		 * @param $error [name]
		 * @param $error [message]
		 */
		public function create($error, $type = 'Error')
		{
			print("<pre>\n");

			var_dump($error);

			print("</pre>\n");

		}

		public function renderErrors()
		{

		}

		/**
		 * Logs Error into Database
		 *
		 * @param $error
		 */
		public function LogError($error) {

		}

		public function start($event){
			print("<!-- starting Errors Services -->");

		}

		public function error($event){
			print("<!-- new error from event -->");

		}

		public function render(){

		}

		/** TODO - Remove being replaced by unified update in core.abstract\coreObserver
		 * Takes observed inputs
		 * */
		/*public function update(\SplSubject $event)
		{
			$event = $event->getEvent();

			foreach ($event as $e => $message) {
				switch ($e) {
					case "error":
						$this->Error($message);
						break;

					case "render":
						$this->renderErrors();
						break;


				}
			}

		}*/

	}

	/**
	 * Class newError - Stores new error messages on create returns the timestamp of the created messages
	 * @package core
	 */
	class newError{
		private $_timestamp;
		private $_type;
		private $_Message;

		/**
		 *
		 * @param $Message
		 * @param string $type
		 */
		public function __construct($Message, $type = "Error"){
			$this->_timestamp = time();
			$this->_Message = $Message;
			$this->_type = $type;

			return $this->_timestamp;
		}

		/**
		 * function for rendering error to client
		 */
		public function render(){

		}


	}
}