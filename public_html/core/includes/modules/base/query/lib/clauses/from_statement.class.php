<?php

/**
 * @package core\query
 */
namespace core\query\clauses {
    /**
     * Class from_statement_clause
     */
    class from_statement extends \core\query\clauses
    {
        /** @var \core\query\statements */
        private $_statement;

        /** @var string */
        private $_label;

        /**
         * from_statement_clause constructor.
         *
         * @param \core\query\statements $statement
         * @param string                  $label
         */
        public function __construct(\core\query\statements $statement, $label)
        {
            $this->_statement = $statement;
            $this->_label = $label;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "";

            if ($this->_statement) {
                $result .= $this->getFromOutput();
                $result .= $this->getStatementOutput();
            }

            return $result;
        }

        /**
         * @return string
         */
        private function getFromOutput()
        {
            $result = "FROM ";

            return $result;
        }

        /**
         * @return string
         */
        private function getStatementOutput()
        {
            $result = "";

            if ($this->_statement) {
                $result .= "(";
                $result .= $this->_statement->output();
                $result .= ") ";
                $result .= "AS ";
                $result .= $this->_label;
                $result .= " ";
            }

            return $result;
        }
    }
}