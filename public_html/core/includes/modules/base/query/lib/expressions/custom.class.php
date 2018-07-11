<?php

namespace core\query\expressions
{
    /**
     * Class custom_expression
     */
    class custom extends \core\query\expressions
    {
        /** @var \core\query\arguments  */
        private $_argument;

        /**
         * custom_expression constructor.
         *
         * @param \core\query\arguments $argument
         */
        public function __construct(\core\query\arguments $argument)
        {
            $this->_argument = $argument;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "(";
            $result .= $this->_argument->output();
            $result .= ")";

            return $result;
        }
    }
}
