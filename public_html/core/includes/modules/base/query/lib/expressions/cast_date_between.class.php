<?php

/**
 * @package core\query
 */
namespace core\query\expressions
{
    /**
     * Class cast_date_between
     * @package core\query\expressions
     */
    class cast_date_between extends \core\query\expressions
    {
        /** @var string */
        private $_operator;

        /** @var \core\query\arguments */
        private $_firstArgument;

        /** @var \core\query\arguments */
        private $_secondArgument;

        /** @var \core\query\arguments */
        private $_thirdArgument;

        /**
         * cast_date_between_expression constructor.
         *
         * @param \query_argument $firstArgument
         * @param \query_argument $secondArgument
         * @param                 $operator
         * @param \query_argument $thirdArgument
         */
        public function __construct(\core\query\arguments $firstArgument, \core\query\arguments $secondArgument, $operator, \core\query\arguments $thirdArgument)
        {
            $this->setArgument_First($firstArgument);
            $this->set_Operator($operator);
            $this->setArgument_Second($secondArgument);
            $this->setArgument_Third($thirdArgument);
        }

        /**
         * @param $operator
         */
        public function set_Operator($operator)
        {
            $this->_operator = $operator;

        }

        /**
         * @param \core\query\arguments $firstArgument
         */
        public function setArgument_First(\core\query\arguments $firstArgument)
        {
            $this->_firstArgument = $firstArgument;
        }

        /**
         * @param \core\query\arguments $secondArgument
         */
        public function setArgument_Second(\core\query\arguments $secondArgument)
        {
            $this->_secondArgument = $secondArgument;
        }

        /**
         * @param \core\query\arguments $thirdArgument
         */
        public function setArgument_Third(\core\query\arguments $thirdArgument)
        {
            $this->_thirdArgument = $thirdArgument;
        }

        /**
         * @return \core\query\arguments
         */
        public function getArgument_First()
        {
            return $this->_firstArgument;
        }

        /**
         * @return string
         */
        public function getArgumentOutput_First()
        {
            $result = " ";
            $result .= $this->_firstArgument->output();
            $result .= " ";

            return $result;
        }

        /**
         * @return \core\query\arguments
         */
        public function getArgument_Second()
        {
            return $this->_secondArgument;
        }

        /**
         * @return string
         */
        public function getArgumentOutput_Second()
        {
            $result = " ";
            $result .= $this->_secondArgument->output();
            $result .= " ";

            return $result;
        }

        /**
         * @return \core\query\arguments
         */
        public function getArgument_Third()
        {
            return $this->_thirdArgument;
        }

        /**
         * @return string
         */
        public function getArgumentOutput_Third()
        {
            $result = " ";
            $result .= $this->_thirdArgument->output();
            $result .= " ";

            return $result;
        }


        /**
         * @return string
         */
        public function output()
        {
            $result = "(CAST(";
            $result .= $this->_firstArgument->output();
            $result .= " AS DATETIME) BETWEEN ";
            $result .= $this->_secondArgument->output();
            $result .= " ";
            $result .= $this->_operator;
            $result .= " ";
            $result .= $this->_thirdArgument->output();
            $result .= ")";

            return $result;
        }
    }
}