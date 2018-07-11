<?php

namespace core {

	/**
	 * Class Librarian, Cannot be a static class because of the SPLobserver
	 *
	 * Route Construction for applications
	 * @Route(
	 *       path         = "/$class/$action/{id}",
	 *       name         = "$class::$action",
	 *       defaults     = { "id" = "1" },
	 *       requirements = { "id" = "\d+" },
	 ***/
	class Librarian extends \core\coreObserver {
		// Storage for class settings
		private $_Settings = [];

		// Private storage for Known Module and Path information
		private $_Paths = [];
		private $_Modules = [];

		/**
		 * A sanitized version of the URL, excluding the domain and base component
		 *
		 * @var string
		 ***/
		protected $path_clean = '';

		/** TODO Returns information about this module
		 *
		 */
		public static $_info = [
			'name' => "Librarian",
			'listen' => 'eventDispatcher'
		];

		/**
		 *
		 * Librarian Constructor
		 * @param array $settings, USed for pushing settings into local
		 *
		 ***/
		public function __construct( $settings  = null )
        {
			$this->_Settings = $settings;

		}

		/**
		 * Process for listener start requests created in responce to changes in core so we can control when core applications trigger start events.
		 * @param $should ? true | false - check to ensure startup request;
		 */
		public function Start($should = false)
        {
			if($should != false) {
				$this->_loadPaths();
				$this->_loadModules();
			}
		}
		/** TODO
		 * Kicks off module installer function
		 ***/
		public function Install() {
			echo "Install";

		}

		/** TODO, Extend to include sub applications
		 *
		 * Inventories applications and gets their Info, this is an expensive process as it needs to open every class and call its static __info__()
		 *
		 * @param bool $purge
		 */
		public function Inventory($purge = true) {


			// if purge is true remove existing paths before inventory
			if ($purge == true) {
				print("<!--Purge: $purge-->");
				$this->_PurgePaths();
				$this->_PurgeModules();
			}

			// scans applications director as defined in settings for applications by name
			$this->_InventoryFolder($this->_Settings['applications']);

		}

		/** Working 7/14/2015
		 * Cycles though each folder looking for applications
		 *
		 * @param      $scanPath
		 * @param bool $recursive - if true look inside additional folders
		 */
		private function _InventoryFolder($scanPath, $recursive = true){
			// Hits each file/folder in DIR as $inc
			foreach (scandir($scanPath) as $fName) {
				//removes hidden and system directories
				if ($fName[0] != '.') {
					// makes local path
					$path = $scanPath . DIRECTORY_SEPARATOR . $fName;

					//creates applications namespace
					if (is_dir($path)) {
						//checks to see if there is a module with matching
						$modulePath = $path . DIRECTORY_SEPARATOR . $fName ;
						if (file_exists($modulePath . ".module")) {
							print("<!-- Module: $modulePath -->\n");
							$this->_InventoryModule($modulePath);
						}

						// Repeats scan looking for sub applications
						if($recursive === true) {
							$this->_InventoryFolder($path);
						}

					}
				}
			}
		}

		/** Working 7/14/2015
		 *  Adds found module to inventory
		 * @param        $path
		 * @param string $extension for use if $extension is changed for future reasons
		 */
		private function _InventoryModule($path, $extension = ".module"){
			//Separates Path by directory DIRECTORY_SEPARATOR
			$m = explode(DIRECTORY_SEPARATOR , $path);
			//Drops last Element in array as $Name
			$name = array_pop($m);
			// Appends $extension onto $path
			$path = $path . $extension;

			// Rebuilds Broken Path as Namespace // TODO replace with namespace tokenizer in core to allow a larger range of paths
			if(is_array($m)) {
				$module = '\\' . implode("\\", $m);
			} else { // Preventive in the event there is only 1 directory in path
				$module = '\\' . $m;
			}

			// verifies that module exists and loads it
			if (file_exists($path)) {
				//print "<pre>\n"; var_dump($name); print "</pre>\n";
				require_once $path;


				// checks for module information
				if (method_exists($module, '__Info__')) {
					$mi = $module::__Info__(); // Grabs Module info for future use
					$mi['basename'] = $name; // Adds path and namespace to info preventing the need to be recreated as at this point we know they are good
					$mi['filepath'] = $path;
					$mi['namespace'] = $module;

					// Checks for Database Schema information
					$this->getDBSchema( $name, $mi['filepath']);

					// calls module information to register information
					if(isset($mi['path'])) {
						// registers module's paths for future call backs

						print ("<!-- Module: " . $mi['filepath'] . " Paths: ". print_r($mi['path'], true) . "-->\n");
						$this->registerPaths($module, $mi['path']);
					}

					$this->registerModule($module, $mi );

				} else { // Skip Inventory no Information found
					//echo "No information for module found!<br>";
				}

			}
		}

		/** TODO
		 * Check for Database Schema information to be loaded with applications.
		 *
		 * @param $modulePath
		 * @param $moduleName
		 *
		 * @return mixed
		 */
		public function getDBSchema($moduleName, $modulePath){
			global $schema;

			$schemaPath = dirname($modulePath) . "/" . $moduleName . ".schema";

			if (file_exists($schemaPath)) {
				require_once $schemaPath; // TODO replace with bootstrap include

			}

		}

		/** Working 7/14/2015
		 * TODO - exten to support namespaces outside of core\
		 * Loads module from a given path
		 * @param $path
		 */
		public function loadModulesFromPath($path) {
			// Returns Registered module from path;
			$modulePath = $this->Path($path);

			if (isset($modulePath) && array_filter($modulePath)) {
				//print("<!-- Module path found: $modulePath -->\n");

				$module = $this->getModule($modulePath['class']);
				$requiredModules = $this->getRequired($module['require']);

				/**
				 * Load requirements before loading main
				 */
				if (isset($requiredModules)) {
					foreach ($requiredModules as $required) {
						require_once($required['filepath']); //TODO replace with bootstrap include

						$attachedRequire = \core\bootstrap::attachModule($required['basename'], new $required['namespace']());
					}
				}

				require_once($module['filepath']); // TODO replace with bootstrap include

				$attachedModule = \core\bootstrap::attachModule($module['basename'], new $module['namespace']());

				//$this->$module['basename'] = new $module['namespace']();
				$attachedModule->$modulePath['entry']($modulePath['args']);
			}else{
				print("<!-- no module found for path: $path -->");
			}
		}

		/** Working 7/14/2015
		 * @param $module
		 *
		 * @return null
		 */
		public function getModule($module) {
			if( isset( $this->_Modules[$module] ) ) {
				return $this->_Modules[$module];
			}

			return null;
		}

		/** TODO - Recheck Required Modules for their Decencies
		 *
		 * Returns module Details from __INFO__ for all required applications, expects array of module names
		 *
		 * @param $requiredModules
		 *
		 * @return array|null
		 */
		public function getRequired($requiredModules){
			$required = [];
			$missingRequred = false;

			if(isset($requiredModules)) {
				// scans applications director as defined in settings for applications by name
				foreach ($requiredModules as $r) {
					$module = "\\core\\applications\\$r"; // TODO regenerate form information rather then path rebuild
					if (isset($this->_Modules[$module])) {
						$required[$r] = $this->_Modules[$module];
					}

				}
			}

			if($missingRequred == true){
				return null;
			}
			return $required;
		}

		/** Working 7/14/2015
		 * @param $module
		 *
		 * @return null
		 */
		public function getRequirements($module) {
			if( isset( $this->_Modules[$module] ) ) {
				if( isset( $this->_Modules[$module]['require'] ) ) {
					return $this->_Modules[$module]['require'];
				}
			}

			return null;
		}

		/** Working 7/14/2015
		 * function for returning known Modules
		 * @return array
		 */
		public function knownModules(){
			if(isset($this->_Modules)) {
				return $this->_Modules;
			}

			return null;
		}

		/** Working 7/14/2015
		 * Returns Module information givens its name
		 * @param $moduleName
		 *
		 * @return mixed
		 ***/
		public function Module($moduleName){
			$m = $this->_Modules[$moduleName];
			return $m;
		}

		/** Working 7/14/2015
		 * Registers Module and its information for retrieval
		 *
		 * @param $module
		 * @param $moduleInfo
		 ***/
		private function registerModule($module, $moduleInfo){
			$this->_Modules[$module] = $moduleInfo;

			$this->_storeModules();
		}

		/** Working 7/14/2015
		 *
		 ***/
		private function _storeModules() {
			cache::Store('ModuleInfo', $this->_Modules, 0);
		}

		/** Working 7/14/2015
		 * @return mixed
		 ***/
		private function _loadModules() {
			return $this->_Modules = cache::Get('ModuleInfo');
		}

		/** Working 7/14/2015
		 *
		 ***/
		private function _PurgeModules() {
			cache::Store('ModuleInfo', "", 0);
			cache::Delete('ModuleInfo');

			$this->_Modules = array();
		}

		/** Working 7/14/2015
		 * function for returning known Paths
		 *
		 * @return array
		 */
		public function knownPaths(){
			if(isset($this->_Paths)) {
				return $this->_Paths;
			}

			return null;
		}

		/** Working 7/14/2015
		 * Registers new path for later use
		 *
		 * @param      $module
		 * @param null $modulePaths
		 ***/
		private function registerPaths($module, $modulePaths = null) {

			// if path is present, prevents applications with out paths from being added to registry
			if (isset($modulePaths)) {
				// if modulePath is not array convert to numbered array
				if(!is_array($modulePaths)) {
					$modulePaths = [$modulePaths];
				}

				// loops though the array of paths
				foreach ($modulePaths as $action => $path) {
					//if key is numeric action should be default
					if(is_numeric($action)) {
						$action = '_Default';
					}

					// Registers new path
					$this->_Paths[$this->pathMarkup($path)] = [$module => $action];

				}
			}
			$this->_storePaths();
		}

		/** Working 7/14/2015
		 * Takes a marked up path and preps it for Regex Named Captures
		 *
		 * http://www.regular-expressions.info/named.html
		 * http://stackoverflow.com/questions/4777635/how-to-match-numbers-in-an-array-in-php?rq=1
		 *
		 * users/<id:(\d+)>/ = users/1/ || users/42 != users/bob
		 * $reg = "user\/(?P<id>\d+)\/";
		 *
		 * @param $path
		 *
		 * @return mixed
		 ***/
		private function pathMarkup($path) {
			// encoded backslashes
			$pattern = '/\//';
			$replacement = '\\\/';
			$path = preg_replace($pattern, $replacement, $path);

			// converts markup format and creates names Regex match
			$pattern = '/\<([a-zA-Z0-9]*)\:\((.*)\)\>/';
			$replacement = "(?P<$1>$2)";
			$path = preg_replace($pattern, $replacement, $path);

			$path = includeTrailingCharacter($path,'\/');

			// should return a valid Regex named pattern from the markup structure
			return $path;
		}

		/** Working 7/14/2015
		 * Validates Path and returns appropriate module information
		 *
		 * @param $action
		 *
		 * @return array
		 ***/
		public function Path($action){
			$module = [];

			$action = includeTrailingCharacter($action, '/');

			//TODO only works for first path all others will get forgotten about
			//var_dump($this->_Paths);

			foreach ($this->_Paths as $mPath => $mAction) {
				print("<!-- $action : $mPath -->\n");

				if($args = $this->testPath($action, $mPath)){
					$module['class'] = key($mAction);
					$module['entry'] = current($mAction);
					$module['args'] = $args;

					return $module;
				}
			}


		}

		/** Working 7/14/2015
		 * test a URI path against a given path Regex
		 *
		 * http://www.regular-expressions.info/named.html
		 *
		 * $str = 'user/1/';
		 * $reg = "user\/(?P<ID>\d+)\/";
		 *
		 * var_dump(testPath($str, $reg));
		 *
		 * $str = 'user/bob/smith/';
		 * $reg = "user\/(?P<fname>[a-zA-Z]+)\/(?P<lname>[a-zA-Z]+)\/";
		 *
		 * @param $url
		 * @param $pathReg
		 *
		 * @return null
		 ***/
		private function testPath($url, $pathReg) {

			if(preg_match("/^$pathReg\\/?$/", $url, $matches)) {
				return $matches;

			} else {
				return null;
			}
		}

		/** Working 7/14/2015
		 *
		 ***/
		private function _storePaths() {
			cache::Store('ModulePaths', $this->_Paths, 0); // TODO replace with new cache system
		}

		/** Working 7/14/2015
		 * @return mixed
		 ***/
		private function _loadPaths() {
			return $this->_Paths = cache::Get('ModulePaths'); // TODO replace with new cache system
		}

		/** Working 7/14/2015
		 *
		 ***/
		private function _PurgePaths() {
			cache::Store('ModulePaths', "", 0); // TODO replace with new cache system

			cache::Delete('ModulePaths');

			$this->_Paths = array();
		}

		/** TODO create lookup requests for parent actions; to limit the need for update actions for each module,
		 *
		 * Takes observed inputs
		 ***/
		public function update(\SplSubject $inputs) {
			$event = $inputs->getEvent();


			foreach($event as $e => $message){
				print("<!-- $e : heard by Librarian -->\n");

				switch ($e) {
					case "start":
						print "<!-- Starting Librarian -->\n";

						$this->Start($message);

						break;

					case "inventory":
						print "<!-- Inventoring Modules -->\n";

						$this->Inventory($message);

						break;

					case "action":
						print "<!-- Routing Action: '$message' to applications -->\n";

						$this->loadModulesFromPath($message);

						break;
				}
			}
		}
	}
}