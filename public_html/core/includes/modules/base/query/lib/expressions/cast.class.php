<?php

namespace core\query\expressions
{
    /**
     * Class cast_date_between
     */
    class cast extends \core\query\expressions
    {
        /** @var string */
        private $_asType;

        /** @var \core\query\arguments */
        private $_firstArgument;

        /**
         * cast_date_between_expression constructor.
         *
         * @param \core\query\arguments $firstArgument
         * @param \string               $asType
         */
        public function __construct(\core\query\arguments $firstArgument, $asType)
        {
            $this->_firstArgument = $firstArgument;
            $this->_asType = $asType;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "( CAST(";
            $result .= $this->_firstArgument->output();
            $result .= " AS ";
            $result .= $this->_asType;
            $result .= ")";

            $result .= ")";

            return $result;
        }
    }
}