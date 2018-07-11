<?php

namespace core {

    // hard coded core needs
    include_once 'settings.php';

    include_once 'includes/core.interface';
    include_once 'includes/core.abstract';
    include_once 'includes/cache/cache.stub';

    /**
     * Class bootstrap
     * @package core
     */
    class bootstrap {
        static $instance;
        // Singleton Container
        private $_coreSettings;

        private $_coreCache;

        private $_listeners = array();
        private $_dispatchers = array();

        /** TODO
         * class constructor
         * @param  $suppress_events ? true | false prevents event calls after loading core
         *
         * http://php.net/manual/en/language.oop5.decon.php
         */
        public function __construct($suppress_events = false)
        {
            //prevents 2nd creation by returning the singleton instance if present
            self::getInstance($this);

            //attaches core settings
            global $settings;

            // gets its own settings
            $this->_coreSettings = $settings['core'];

            //gets local working directory, and prevents path from being changed
            if (!array_key_exists('local', $this->_coreSettings)) {
                $this->_coreSettings['local'] = [
                    'path' => getcwd(),
                ];
            }

            $this->_coreCache = new cacheStub([
                'engine' => 'File',
                'name' => 'core',
                'path' => $this->_coreSettings['path']['temp'],
                'cache_life' => '86400' //caching time, in seconds 84,400 24h * 60min * 60sec
            ]);

            // attached the global $schema; maybe unneeded?
            global $schema;

            // Core Loaders
            $this->_coreDefinitions();

            // Triggers request to load core
            $this->_indexCore();

            // Once core has started add listeners in.
            $this->attachlisteners();

            // registers eventdispatcher with events for callbacks - Still a little messy but better then before
            //events::registerDispatcher($this->eventDispatcher);


            // TODO Remove - Static test of Template Render functions
            //\core\Renderer\Template::render(['header'=>'<head><title>openDungeon</title></head>'], "core/templates/page.tpl");

            // Trigger Function for starting events after all the entire core has been loaded
            //if($suppress_events != true) {
            //    events::triggerEvents();
            //}
        }

        /** TODO - Working 7/14/2016 - Needs further testing.
         * TODO move part of this into pre-init caching so that listener requests can be caches
         * function for preparing modules for attachments to listeners
         * @param $lisenerRequest
         */
        private function addtoListeners($lisenerRequest){
            if(get_class($lisenerRequest) == "core\\listener") {
	            print("<!-- - listener: request added -->\n");
                array_push($this->_listeners, $lisenerRequest);
            }

        }

        /** TODO - Working 7/14/2016 - Needs further testing.
         *
         * function for attaching prepared modules to listeners
         *
         */
        private function attachListeners() {
            print("<!-- attaching listeners -->\n");
            // Loops through known Listener needs and attempt to add
            foreach($this->_listeners as $lis) {
                $listener = $lis->getClass();
                $listenTo = $lis->listenTo();

                $this->registerWithDispatcher($listenTo, $listener);
            }
        }

        /**
         * @param $name
         * @param $dispatcher
         */
        private function registerDispatcher($name, $dispatcher) {
            print("<!-- attaching dispatcher $name -->\n");

            $this->_dispatchers[$name] = $dispatcher;
        }

        /**
         * @param $dispatcherName
         * @param $listener
         */
        private function registerWithDispatcher($dispatcherName, $listener) {
            print("<!-- - Attaching Listener '" . get_class($listener) . "''to '" . $dispatcherName . "' -->\n");
            if(isset($this->_dispatchers[$dispatcherName])) {
                print("<!-- - Dispatcher Found! -->\n");
                if(method_exists($this->_dispatchers[$dispatcherName], 'attach')) {
	                print("<!-- -- Attached to $dispatcherName -->\n");
                    $this->_dispatchers[$dispatcherName]->attach($listener);
                }else {
	                print("<!-- -- Cannot attached to $dispatcherName -->\n");
                }
            }else {
                print("<!-- - Dispatcher $dispatcherName is not Found! -->\n");
            }

        }

        /** Working 7/13/2016
         * Returns singleton instance
         * @param null $newInstance
         *
         * @return bootstrap
         */
        public static function getInstance($newInstance = null) {
            if (isset(self::$instance)) {
                return self::$instance;
            }

            if ($newInstance != null) {
                self::$instance = $newInstance;
            }

            return self::$instance;
        }

        /** Working 7/13/2013
         * TODO - add cache controls to system to increase performance in new system. may require pre static subsystem?
         * TODO cannot cache system using APC or any module driven code so create a temp file and test against that.
         * Tells the core to load the abstracts Definitions
         */
        private function _coreDefinitions() {
            $this->_defineCore("trait", "trait", true);
            $this->_defineCore("abstract", "abstract", false);
            $this->_defineCore("interface", "interface", false);
            $this->_defineCore("schema", "schema", false);
            $this->_defineCore("static", "static", true);
            $this->_defineCore("include", "inc", false, true);

        }

        /** Working 7/13/2013
         * define type of files to load
         *
         * @param $coreType
         * @param $fileExten
         * @param bool?false|true $canBeInit
         * @param bool?false|true $canBeCalledAsNew
         * @param bool?false|true $canStartSubclasses
         * @param bool?false|true $canRequestNew
         ***/
        private function _defineCore($coreType, $fileExten, $canBeInit = false, $canBeCalledAsNew = false, $canStartSubclasses = false, $canRequestNew = false)
        {
            print("<!-- Adding '" . $fileExten . "' to _coreSettings -->\n");
            // Adds to core settings with config
            $this->_coreSettings['includes'][$fileExten] = new coreSetting($coreType, $fileExten, $canBeInit, $canBeCalledAsNew, $canStartSubclasses, $canRequestNew);
        }

        /** TODO - Test recursive functions 7/13/2013
         * TODO - add cache controls to system to increase performance in new system. may require pre static subsystem?
         *
         * Index core directory and requests found files be loaded based on their _defineCore
         * searches core dir for possible paths to load then requests their load based on defined coreSettings
         *
         * @param null $pathOverride ? null | (string) path to index for use in core
         ***/
        private function _indexCore($pathOverride = null) {
            // checks for path override used for recursion or could be used with an alt loader
            // keep private to avoid security issues

            if (is_null($pathOverride)) {
                $pathOverride = $this->_coreSettings['path']['include'];
            }

            // Hits each file/folder in $pathOverride as $fName
            foreach (scandir($pathOverride) as $fName) {

                // skips hidden and system directories
                if ($fName[0] != '.') {
                    // makes local path
                    $path = $pathOverride . DIRECTORY_SEPARATOR . $fName;

                    if (is_dir($path)) { // if directory dig further
                        $this->_indexCore($path);

                    } else { // if not directory assume file
                        $this->_loadCore($path);
                    }
                }
            }
        }

        /** TODO Working ( 7/14/2016 ) but needs cleanup
         * TODO create logic to allow module load settings to be overridden in __info__;
         *
         * Loads core files sent over from _indexCore based on settings from _defineCore
         *
         * @param $path
         */
        private function _loadCore($path){
            global $settings;

            $pathData = pathinfo($path);

            //Checks to see if definition of core exists and is OK to use
            if (array_key_exists($pathData["extension"], $this->_coreSettings['includes'])) {
                // creates local copy of $core settings for faster access

                $incSettings = $this->_coreSettings['includes'][$pathData["extension"]];

                // Attaches core file to be used, returned references is stored in $f
                if ($f = $this->__includeFile($pathData, "include")) {

                    // loops though each loaded namespace and
                    foreach($f as $NS) {

                        foreach($NS['classes'] as $class) {
	                        $name = $NS['namespace'] . '\\' . $class['name'];

	                        /**
	                         *     public $_coreType; // type of core often going to be fileType
	                         *     public $_fileExten; // file extension to use be allowed
	                         *     public $_canBeInit = false; // can this core call __Init__()
	                         *     public $_canBeCalledAsNew = false; // can this core be call with new
	                         *     public $_canStartSubclasses = false; // can this core request its subclasses be loaded
	                         *     public $_canRequestNew = false; // can this core request new files be loaded
	                         ***/
	                        $settingsName = strtolower($class['name']);

	                        // Checks for module information
	                        /**
	                         * Core Modules are required to have public static __info__function
	                         * so this prevent fake modules and bad calls
	                         *
	                         * if you cannot be called as new nor inited skip, no reason to continue
	                         */
	                        if  ((($incSettings->_canBeInit === true) || ($incSettings->_canBeCalledAsNew === true)) && (method_exists($name, '__info__'))) {
		                        // bootSettings possible
		                        /* $info['bootSettings']['coreType'];
								   $info['bootSettings']['fileExten'];
								   $info['bootSettings']['callInit'];
								   $info['bootSettings']['callAsNew'];
								   $info['bootSettings']['StartSubclasses'];
								   $info['bootSettings']['skip'];
								 }*/

		                        $info = $name::__info__();

		                        /** //TODO-Remove | Disabled as testing is not needed at the moment
		                         * print("<!-- Module Info -->\n");
		                         * var_dump($info);
		                         * print("\n<!-- /Module -->\n");
                                */

		                        /**
		                         * if the class should be INITed as a static
		                         */

                                print("<!-- - Loading core: " . $class['name'] . " -->\n");

		                        if (isset($info['bootSettings'])) {
                                    print("<!-- -- " . $class['name'] . ": has boot settings -->\n");

                                    /// TODO Broken, here prevents call to listeners
			                        if ($info['bootSettings']['skip'] != true) {
                                        print("<!-- -- " . $class['name'] . ": has not been skipped -->\n");

				                        /** TODO
				                         *  Starts to load extensions after main class has been opened and __info__ has been collected but before main class has been instanced.
				                         *  this is done so that the main class instancing call can check to see what extensions will work on given system
				                         *
				                         *  Extensions Cannot have listeners or other supporting functions
				                         *
				                         * Checks to see if extensions are available in module
				                         */
				                        if (isset($info['extensions'])) {
					                        $extensions = $info['extensions'];

					                        $this->_defineCore($info['name'] . "_" . $extensions['fileType'], $extensions['fileType']);

					                        //TODO Define sub-type extention with inherited permissions from parent type
					                        //$this->_defineCore("include", "inc", false, true);

					                        //TODO - Remove, Testing information from Sub-Module Data
					                        //print_r($extensions);
					                        print("<!-- - extensions found for " . $class{"name"} . " -->\n");

					                        // Loops through each registered file in the Extensions list and triggers registration.
					                        foreach ($info['extensions']['paths'] as $extensionName => $extensionPath) {
						                        print("<!--- -- Registering extension: ". $pathData['dirname'] . DIRECTORY_SEPARATOR . $extensionPath  . " -->\n");

						                        $this->__includeFile($pathData['dirname'] . DIRECTORY_SEPARATOR . $extensionPath, 'include');
					                        }
				                        }

				                        if (($incSettings->_canBeInit === true ) && ($info['bootSettings']['callInit'] === true)) {
                                            print("<!-- -- " . $class['name'] . ": can be Init -->\n");

					                        if (method_exists($name, '__Init__')) {
					                            // TODO Added errror correction
						                        // TODO use reflectionClass to remove Staatic and
						                        print("<!-- -- " . $class['name'] . ": Initializing -->\n");

						                        if (isset($settings[$settingsName])) { // if settings for class push setting to constructor
							                        $name::__Init__($settings[$settingsName]);
							                        print("<!-- -- " . $class['name'] . ": Init'd /w Settings -->\n");

						                        } else {
							                        $name::__Init__(); // no settings found make new class
							                        print("<!-- -- " . $class['name'] . ": Init'd w/o Settings -->\n");
						                        }
					                        } else {
						                        print("<!-- -- " . $class['name'] . ": no Init found '" . $name . "::__Init__()' -->\n");
					                        }

					                        if (isset($info['listen'])) {

                                                print("<!-- - listeners: " . $class['name'] . " has requested to join -->\n");

						                        $lis = new listener($this->$class['name'], $info['listen']);
						                        $this->addtoListeners($lis);
					                        }

					                        if (isset($info['dispatcher']) && ($info['dispatcher'] == true)) {

						                        print("<!-- - dispatchers: " . $class['name'] . " has requested to join -->\n");

						                        $this->registerDispatcher($class['name'], $name);
					                        }
				                        }

				                        /**
				                         * IF a new instance of the class should be created
				                         */
				                        if ($incSettings->_canBeCalledAsNew === true && $info['bootSettings']['callAsNew'] === true) {
					                        // if there are settings load with settings else just construct
					                        if (isset($settings[$settingsName])) { // if settings for class push setting to constructor
						                        print("<!-- -- " . $class['name'] . ": has created a new instance /w Settings -->\n");
						                        $this->$class['name'] = new $name($settings[$settingsName]);

					                        } else {
						                        $this->$class['name'] = new $name(); // no settings found make new class
						                        print("<!-- -- " . $class['name'] . ":  has created a new instance w/o Settings -->\n");
					                        }

					                        if (isset($info['listen'])) {
						                        print("<!-- - listeners: " . $class['name'] . " has requested to join -->\n");

						                        $lis = new listener($this->$class['name'], $info['listen']);
						                        $this->addtoListeners($lis);
					                        }

					                        if (isset($info['dispatcher']) && ($info['dispatcher'] == true)) {

						                        print("<!-- - dispatchers: " . $class['name'] . " has requested to join -->\n");

						                        $this->registerDispatcher($class['name'], $this->$class['name']);
					                        }
				                        }


			                        }

			                        if ($incSettings->_canStartSubclasses === true && $info['bootSettings']['StartSubclasses'] != true) {
				                        break 2;
			                        }
		                        }
	                        }
                        }
                    }
                }
            }
        }

        /** TODO - Working but further testing needs to be done and it should be added to Librarian;
         * TODO Add support for caching of $class details
         *
         * function to include files in applications working directory, ALL includes should be processed though here for security reasons.
         *
         * @param $path string | pathinfo object : path or path data from pathinfo to include;
         * @param $type string |  // type of include NOT filetype
         *
         * @return array | NULL $includeInfo : return information related to the file being added will return null if the file was skipped for any reason
         */
        public function __includeFile($path, $type) {
            print ("\n<!-- mew include request -->\n");
            if (is_string($path)) {
                print ("<!-- - converting to path information -->\n");
                $path = pathinfo($path);
                //print_r($path);
            }

            /**
             * Protection agains loading unknown code
             */
            if (array_key_exists($type, $this->_coreSettings['path'])) {
                print ("<!-- - Filetype found in coreSettings -->\n");
                // creates real paths of files and include directorys to test if a valid path can be created
                $realPath = stream_resolve_include_path($path['dirname'] . DIRECTORY_SEPARATOR . $path['basename']);

                $includeFrom = $this->_coreSettings['local']['path'] . DIRECTORY_SEPARATOR . $this->_coreSettings['path'][$type];

                    // tests to see if include path exists in expected paths by comparing subs-string
                if (substr($realPath, 0, strlen($includeFrom) != $includeFrom)) {

                    // Attemps to load core with cache data rather then reading class data from each included file
                    if(!$this->_coreCache->cacheLoaded()) { //Cache unavailable load class data from file
	                   // print("<!-- cache unavailable -->\n"); //TODO-Remove | Disabled as testing is not needed at the moment

                        $classDetails = classes_in_file($realPath);
                        $this->_coreCache->Set($realPath, $classDetails);

                    } else {
	                    //print("<!-- cache available -->\n"); //TODO-Remove | Disabled as testing is not needed at the moment
                        $classDetails = $this->_coreCache->Get($realPath);

                        if($classDetails == false) { // no data returned from cache load from file data
	                        //print("<!-- key not found in cache -->\n"); //TODO-Remove | Disabled as testing is not needed at the moment
                            $classDetails = classes_in_file($realPath);
                            $this->_coreCache->Set($realPath, $classDetails);
                        }
                    }

	                print ("<!-- - including $realPath -->\n");

                    //file is ok to include
                    include_once $realPath;


                    // return with class information
                    return $classDetails;
                }
            }else{

                print ("<!-- - Filetype: " . $path['extension'] . " | $type missing from coreSettings cannot include-->\n");
                //print_r($this->_coreSettings['path']);
            }
            return null;
        }

        /** TODO - Testing
         * Attaches moduleObject to main class. Returns module if module is already attached
         *
         * @param $moduleName
         * @param $moduleObject
         *
         * @return null
         */
        public static function attachModule($moduleName, $moduleObject) {
            // gets boostrap instance
            if (self::getInstance()) {
                // checks for existance of exisiting module
                if (!isset(self::$instance->$moduleName)) {
                    // if no module is present attaches new module
                    return self::$instance->$moduleName = $moduleObject;
                }

                // returns attached module
                return self::$instance->$moduleName;
            } else {
                // unable to locate core returns null preventing module form being randomly thrown into the void
                return null;
            }
        }

        /** TODO - testing
         *
         * Removes module from main class (will destroy any data )
         * @param $moduleName
         *
         * @return null
         */
        public static function removeModule($moduleName){
            // gets boostrap instance
            if (self::getInstance()) {
                // nulls out any module at space as we are destroying a module instance we don't really care if it existed
                return self::$instance->$moduleName = NULL;
            } else {
                return null;
            }
        }

        /** TODO may not be needed as we are using __autoload
         * Disabled for now as it appears to be unneeded - Jan 30, 2016
         * Overload function for calls when the method does not exist
         *
         * http://php.net/manual/en/language.oop5.overloading.php#object.call
         * @param array $args
         */
        /*public function __call($name, $args) {

        }*/

        /** TODO may not be needed as the librarian is serving both as autoloader and entry validation
         * Disabled for now as it appears to be unneeded - Jan 30, 2016
         *  Attempt to load undefined class
         * http://php.net/manual/en/function.autoload.php
         *
         * @param $class_name
         */

        /*public  function __autoload($class_name) {
            //print $class_name;
            //require_once('modules/' . $class_name . '/' . $class_name . '.module ');
            //class_exists
        }*/

        /** TODO
         * Destruction
         * http://php.net/manual/en/language.oop5.decon.php
         */
        function __destruct(){

        }
    }

    /** TODO cleanup and comment. also review for improvements
     * USEFULL because it allows access to namespace and class data with out including file but at the cost of needed to open file as reader and run through file as token data so not fast
     * should be called as little as possible (part of installer/updates but no every exec)
     *
     * https://stackoverflow.com/questions/928928/determining-what-classes-are-defined-in-a-php-class-file/11114724#11114724
     *
     * Looks what classes and namespaces are defined in that file and returns the first found
     * @param String $file Path to file
     * @return array $classes | NULL - Returns NULL if none is found or an array with namespaces and classes found in file
     ***/
    function classes_in_file($file, $includeAbstract = false, $includeInterface = false) {

        $classes = $nsPos = $final = array();
        $foundNS = FALSE;
        $ii = 0;

        if (!file_exists($file)) return NULL;

        $php_code = file_get_contents($file);
        $tokens = token_get_all($php_code);
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++)  {
            if(!$foundNS && $tokens[$i][0] == T_NAMESPACE) {
                $nsPos[$ii]['start'] = $i;
                $foundNS = TRUE;
            } elseif( $foundNS && ($tokens[$i] == ';' || $tokens[$i] == '{') ) {
                $nsPos[$ii]['end']= $i;
                $ii++;
                $foundNS = FALSE;
            } elseif ($i-2 >= 0 && $tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) {
                if($i-4 >=0 && $tokens[$i - 4][0] == T_ABSTRACT ) {
	                if($includeAbstract == true) {
		                $classes[$ii][] = array('name' => $tokens[$i][1], 'type' => 'ABSTRACT CLASS');
	                }
                } else {
                    $classes[$ii][] = array('name' => $tokens[$i][1], 'type' => 'CLASS');
                }
            } elseif (($includeInterface == true ) && ($i-2 >= 0 && $tokens[$i - 2][0] == T_INTERFACE && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING)) {
                $classes[$ii][] = array('name' => $tokens[$i][1], 'type' => 'INTERFACE');
            }
        }

        if (empty($classes)) return NULL;

        if(!empty($nsPos)) {
            foreach($nsPos as $k => $p) {
                $ns = '';
                for($i = $p['start'] + 1; $i < $p['end']; $i++)
                    $ns .= $tokens[$i][1];

                $ns = trim($ns);
                $final[$k] = array('namespace' => $ns, 'classes' => $classes[$k+1]);
            }
            $classes = $final;
        }
        return $classes;
    }

    /** TODO Rework as a permissions control system and allow __INFO__ to handle load
     *
     * Class coreSettings
     * Container for storing information about core includes
     *
     * @package core
     ***/
    class coreSetting {
        public $_coreType; // type of core often going to be fileType
        public $_fileExten; // file extention to use be allowed
        public $_canBeInit = false; // can this core call __Init__()
        public $_canBeCalledAsNew = false; // can this core be call with new
        public $_canStartSubclasses = false; // can this core request its subclasses be loaded
        public $_canRequestNew = false; // can this core request new files be loaded

        /** Working 7/13/2016
         *
         * @param $coreType
         * @param $fileExten
         * @param bool?false|true $_canBeInit
         * @param bool?false|true $canBeCalledAsNew
         * @param bool?false|true $canStartSubclasses
         * @param bool?false|true $canRequestNew
         ***/
        public function __construct($coreType, $fileExten, $canBeInit = false, $canBeCalledAsNew = false, $canStartSubclasses = false, $canRequestNew = false) {
            $this->_coreType = $coreType;
            $this->_fileExten = $fileExten;
            $this->_canBeInit = $canBeInit;
            $this->_canBeCalledAsNew = $canBeCalledAsNew;
            $this->_canStartSubclasses = $canStartSubclasses;
            $this->_canRequestNew = $canRequestNew;

        }

        /**
         *
         * public function __get($){
         *
         * */
    }

    /**
     *
     * storage class for module Cache
     *
     * Class listener
     * @package core
     ***/
    class cachedModule{
        private $_modulePath = null;
      //  private $_Listento = "";
        private $_Info = array();
        private $_Spaces = array();
        private $_LoadSettings = array(); // class object of coreSettings;

        /**
         *
         ***/
        public function __construct($modulePath, $info){
            $this->_modulePath = $modulePath;
           // $this->_Listento;
            $this->_Info = $info;
            //$this->_Spaces;
            //$this->_LoadSettings;
        }
    }

    class listener{
        private $_class = null;
        private $_Listento = "";

        /** Working 7/15/2016
         * @param $class
         * @param $Listento
         ***/
        public function __construct($class, $Listento){
            $this->_class = $class;
            $this->_Listento = $Listento;
        }

        /** Working 7/15/2016
         *
         * @return null
         ***/
        public function getClass(){
            return  $this->_class;
        }

        /** Working 7/15/2016
         *
         * @return string
         ***/
        public function listenTo(){
            return  $this->_Listento;
        }
    }
}