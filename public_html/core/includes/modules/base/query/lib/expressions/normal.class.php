<?php

/**
 * @package core\query
 */
namespace core\query\expressions
{

    /**
     * Class normal
     * @package core\query\expressions
     */
    class normal extends \core\query\expressions
    {
        /** @var string */
        private $_operator;

        /** @var \core\query\arguments  */
        private $_firstArgument;

        /** @var \core\query\arguments  */
        private $_secondArgument;

        /**
         * normal_expression constructor.
         *
         * @param \core\query\arguments $firstArgument
         * @param                       $operator
         * @param \core\query\arguments $secondArgument
         */
        public function __construct(\core\query\arguments $firstArgument, $operator, \core\query\arguments $secondArgument)
        {
            $this->_firstArgument = $firstArgument;
            $this->_operator = $operator;
            $this->_secondArgument = $secondArgument;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "(";
            $result .= $this->_firstArgument->output();
            $result .= " ";
            $result .= $this->_operator;
            $result .= " ";
            $result .= $this->_secondArgument->output();
            $result .= ")";

            return $result;
        }
    }
}