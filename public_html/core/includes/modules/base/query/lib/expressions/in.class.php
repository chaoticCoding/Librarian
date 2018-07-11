<?php

namespace core\query\expressions
{

    /**
     * Class in_expression
     */
    class in extends \core\query\expressions
    {
        /** @var \core\query\arguments */
        private $_firstArgument;

        /** @var \core\query\arguments\value */
        private $_secondArgument;

        /**
         * in_expression constructor.
         *
         * @param \core\query\arguments $firstArgument    : column to search
         * @param \core\query\arguments $secondArgument[] : values to check against
         */
        public function __construct(\core\query\arguments $firstArgument, \core\query\arguments $secondArgument)
        {
            $this->_firstArgument = $firstArgument;

            $this->_secondArgument = $secondArgument;
        }

        /**
         * @return string
         */
        public function output()
        {
            $val = $this->_secondArgument->getValue();

            $preparedValue = '';

            if(is_array($val)) {
                $preparedValue =  implode(',', $this->_secondArgument->getValue());

            } elseif (is_string($val)) {
                $preparedValue = trim($val, ',');
            }


            $result = sprintf("(%s IN (%s))", $this->_firstArgument->output(), $preparedValue);

            return $result;

        }
    }
}
