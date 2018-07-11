<?php

namespace core\query\expressions
{
    /**
     * Class concat_expression
     */
    class concat extends \core\query\expressions
    {
        /** @var \core\query\arguments  */
        private $_firstColumnArgument;

        /** @var \core\query\arguments  */
        private $_secondColumnArgument;

        /** @var \core\query\arguments  */
        private $_argument;

        /**
         * concat_expression constructor.
         *
         * @param \core\query\arguments $firstColumnArgument
         * @param \core\query\arguments $secondColumnArgument
         * @param \core\query\arguments $argument
         */
        public function __construct(\core\query\arguments $firstColumnArgument, \core\query\arguments $secondColumnArgument, \core\query\arguments $argument)
        {
            $this->_firstColumnArgument = $firstColumnArgument;
            $this->_secondColumnArgument = $secondColumnArgument;
            $this->_argument = $argument;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "(";
            $result .= "CONCAT(";
            $result .= $this->_firstColumnArgument->output();
            $result .= ", ' ',";
            $result .= $this->_secondColumnArgument->output();
            $result .= ") LIKE ";
            $result .= $this->_argument->output();
            $result .= ")";

            return $result;
        }
    }
}