<?php

namespace core\modules\users
{
	class roles extends \core\eventAbstract
    {
		/** TODO Testing
		 * Module Info Must be present for use
		 * @return array
		 ***/
		public static function __Info__() {
			$info = [
			  'Name' => 'Roles',
			  'Desc' => 'Basic Roles Module',

				// path to use @ ?a= , Optional uf no path is set it will just be used at module's files basename
				//'path' => 'users/%', // TODO Extend to array for functions?

				// path to use @ ?a= , Optional uf no path is set it will just be used at module's files basename
			  'path' => [
				'_Default' => 'roles',
			  ],

			  'permissions' => [ // Permissions this module has

			  ],

			  'require' => [
				"users",
			  ]
			];

			return $info;
		}

		/** TODO
		 * How should installs be handled
		 ***/
		public static function __Install__() {

		}

		/**
		 *
		 ***/
		public function __construct() {
		}

	}
}