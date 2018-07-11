<?php
/**
 *
 */
namespace core\errors
{
    /**
     * Class collection
     * @package util
     */
    class debugging
    {
        /**
         * \util\debugging::header()
         *
         * abstraction of the header function that will only be active if debugging_enabled is true, so it can be left in code
         *
         * @param string $string
         * @param bool $replace
         * @param null $http_response_code
         */
        public static function header($string, $replace = true , $http_response_code = NULL )
        {
            if(DEBUGGING_ENABLED == true) {
                \header($string, $replace, $http_response_code);
            }
        }
    }
}