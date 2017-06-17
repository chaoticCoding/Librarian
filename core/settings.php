<?php
/**
 * Setting and Configurations Module names should always be lowercase!
 ***/
//Paths to Core and Modules
// Only paths added here will be allowed to be included
$settings['core']['path'] = [
		'include'  => 'core/includes',
		'module'   => 'core/modules',
		'template' => 'core/templates',
		'temp'	   => 'core/temp'
	];


//Database settings
$settings['database']['default'] = array (
	'database' => 'openDungeon',
	'username' => 'openDungeon',
	'password' => 'GVBDyZDrdAxdJzqm',
	'prefix' => '',
	'host' => '127.0.0.1',
	'port' => '3306',
);

// set to true to block client error reporting
$settings['errors']['supress'] = false;

// Updated 7/13/2016 now stores module path for librarian system
// Settings for librarian system to force states beyond the main application
$settings['librarian']['modules'] = 'core/modules';


/**
 * maps shortcuts of events to get operators
 * Alterations of this list will effect how the application is run
 * 'event name' => 'event path' || enable true | false
 *
 * 	'action' => 'a' == "?a=action/path
 *  'output' => 'o' == "?o=output/type" html || XML || json
 */
$settings['events'] = array (
		'install' => true,
		'update' => true,
		'inventory' => true,
		'start' => true,
		'input' => true,
		'action' => 'a',
		'output' => 'o',
		'render' => true
);

$settings['mcrypt']['default'] = array (
	'type' => 'MCRYPT_RIJNDAEL_128',
	'key' => '7e8fe454-5e15'
);