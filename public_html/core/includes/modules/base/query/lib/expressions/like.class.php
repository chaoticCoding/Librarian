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

        /**
         * concat_expression constructor.
         *
         * @param \core\query\arguments $firstColumnArgument
         * @param \core\query\arguments $secondColumnArgument
         */
        public function __construct(\core\query\arguments $firstColumnArgument, \core\query\arguments $secondColumnArgument)
        {
            $this->setArgument_First($firstColumnArgument);
            $this->setArgument_Second($secondColumnArgument);
        }

        /**
         * @param \core\query\arguments $firstColumnArgument
         */
        public function setArgument_First(\core\query\arguments $firstColumnArgument)
        {
            $this->_firstColumnArgument = $firstColumnArgument;
        }

        /**
         * @param \core\query\arguments $secondColumnArgument
         */
        public function setArgument_Second(\core\query\arguments $secondColumnArgument)
        {
            $this->_secondColumnArgument = $secondColumnArgument;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "(";
            $result .= "";
            $result .= $this->_firstColumnArgument->output();
            $result .= " LIKE ";
            $result .= $this->_secondColumnArgument->output();
            $result .= "";
            $result .= ")";

            return $result;
        }
    }
}