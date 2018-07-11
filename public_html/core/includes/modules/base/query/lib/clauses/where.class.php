<?php

namespace core\query\clauses
{
    /**
     * Class where_clause
     */
    class where extends \core\query\clauses
    {
        /** @var \core\query\expressions[] */
        private $_andExpressions = array();

        /** @var \core\query\expressions[] */
        private $_orExpressions = array();

        /**
         * @param \core\query\expressions $expression
         */
        public function addANDExpression(\core\query\expressions $expression)
        {
            $this->_andExpressions[] = $expression;
        }

        /**
         * @param \core\query\expressions $expression
         */
        public function addORExpression(\core\query\expressions $expression)
        {
            $this->_orExpressions[] = $expression;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = "";

            if ($this->isEmpty()) {
                $result .= $this->getWhereOutput();
                $result .= $this->getANDExpressionOutput();
                $result .= $this->getORExpressionOutput();
            }

            return $result;
        }

        /**
         * @return bool
         */
        private function isEmpty()
        {
            return (count($this->_andExpressions) > 0
                || count($this->_orExpressions) > 0);
        }

        /**
         * @return string
         */
        private function getWhereOutput()
        {
            $result = "WHERE ";

            return $result;
        }

        /**
         * @return string
         */
        private function getANDExpressionOutput()
        {
            $result = $this->getExpressionsOutput($this->_andExpressions, " AND ");

            return $result;
        }

        /**
         * @return string
         */
        private function getORExpressionOutput()
        {
            $result = "";

            if (count($this->_andExpressions) > 0 && count($this->_orExpressions) > 0) {
                $result .= "AND ";
            }

            $result .= $this->getExpressionsOutput($this->_orExpressions, " OR ");

            return $result;
        }

        /**
         * @param \core\query\expressions[] $expressions
         * @param string                    $delimiter
         *
         * @return string
         */
        private function getExpressionsOutput($expressions, $delimiter)
        {
            $result = "";

            $count = 0;
            foreach ($expressions as $expression) {
                if ($count === 0) {
                    $result .= "(";
                } else {
                    $result .= $delimiter;
                }

                $result .= $expression->output();

                $count++;
            }

            if ($count > 0) {
                $result .= ") ";
            }

            return $result;
        }
    }
}