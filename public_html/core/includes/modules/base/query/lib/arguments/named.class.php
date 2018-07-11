<?php

/**
 * @package core\query
 */
namespace core\query\arguments
{

    /**
     * Class named
     * @package core\query\arguments
     */
    class named extends \core\query\arguments
    {
        /** @var string */
        private $_argument;

        /**
         * named_argument constructor.
         *
         * @param string $argument
         */
        public function __construct($argument)
        {
            $this->_argument = $argument;
        }

        /**
         * @return string
         */
        public function output()
        {
            return $this->_argument;
        }
    }
}