<?php

namespace core\query\components
{
    /**
     * Class left_join_component
     */
    class left_join extends \core\query\components
    {
        /** @var \core\query\components */
        private $_table;

        /** @var \core\query\expressions */
        private $_on;

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
            $result = "LEFT JOIN";
            $result .= " ";

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