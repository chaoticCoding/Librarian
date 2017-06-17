<?php

namespace core {

    /**
     * Lisener class for prepairing data to be haded off to rendering engine
     */
    class Render extends \core\coreObserver {
        private static $_Settings = [];
        /**
         * Public constructor will be called on creation
         *
         * @param array $settings
         ***/
        public static function __Init__( array $settings = null ) {
            if(isset($info)){ // TODO Add Error correction
                self::$_Settings = $settings;
            }
        }

        /**
         * Add new prepared content to list for rendering
         */
        public static function prepair($Content, $tpl){

        }



    }

}
