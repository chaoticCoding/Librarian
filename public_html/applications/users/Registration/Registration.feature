<?php

namespace core\modules\Game
{


	/** TODO
	 *
	 */
	class Registration extends \core\moduleAbstract
    {

		/**
		 *
		 * @return array
		 *
		 ***/
		public static function __Info__() {
			$info = [
				'Name' => 'Registration',
				'Desc' => 'Registration & Logins for game client',

				// path to use @ ?a= , Optional uf no path is set it will just be used at module's files basename
				'path' => [
					'Submit' => 'Registration/Submit',
					'Create' => 'Registration/Create',
				],

				'permissions' => [ // Permissions this module has

				],

				'require' => [

				]
			];

			return $info;
		}

        /**
         *
         */
		public function Create(){
			print "New User";
		}

        /**
         *
         */
		public function Submit(){
			print "testing User";
		}

        /**
         * @param $args
         */
		public static function Load($args) {
			//print "ID:" . $args['id'];
		}

		/** TODO
		 * How should installs be handled
		 ***/
		public static function __Install__() {

		}

        /**
         * Registration constructor.
         */
		public function __construct() {
			parent::__construct();

		}

	}
}

