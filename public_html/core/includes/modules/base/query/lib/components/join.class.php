<?php

namespace core\query\components
{
    /**
     * Class left_join_component
     */
    class join extends \core\query\components
    {
        /** @var \core\query\components */
        private $_table;

        /** @var \core\query\expressions */
        private $_on;

        /** @var string */
        private $_joinMethod;

        /**
         * left_join constructor.
         *
         * @param \core\query\components  $table
         * @param \core\query\expressions $onExpression
         * @param string                 $joinMethod
         ***/
        public function __construct ($table = null, $onExpression = null, $joinMethod = 'LEFT')
        {
            if(null !== $table) {
                $this->setTable($table);

            }

            if(null !== $onExpression) {
                $this->setOn($onExpression);
            }

            if(null !== $joinMethod) {
                $this->setJoinMethod($joinMethod);
            }

        }

        /**
         * @param \core\query\components $table
         */
        public function setTable(\core\query\components $table)
        {
            $this->_table = $table;
        }

        /**
         * @param \core\query\expressions $onExpression
         */
        public function setOn(\core\query\expressions $onExpression)
        {
            $this->_on = $onExpression;
        }

        /**
         * @param string $joinMethod
         */
        public function setJoinMethod($joinMethod = 'LEFT')
        {
            $this->_joinMethod = $joinMethod;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = $this->getLeftJoinOutput();
            $result .= $this->getJoinTableOutput();
            $result .= $this->getOnOutput();

            return $result;
        }

        /**
         * @return string
         */
        private function getLeftJoinOutput()
        {
            $result = "";
            if(trim($this->_joinMethod) != "") {
                $result = $this->_joinMethod . ' ';
            }
            $result .= "JOIN ";

            return $result;
        }

        /**
         * @return string
         */
        private function getJoinTableOutput()
        {
            $result = $this->_table->output();
            $result .= " ";

            return $result;
        }

        /**
         * @return string
         */
        private function getOnOutput()
        {
            $result = "ON ";
            $result .= $this->_on->output();

            return $result;
        }
    }
}