<?php

namespace core\query\clauses
{
    /**
     * Really is the tables constructor and should use the tables class/join
     * Class from_clause
     */
    class from extends \core\query\clauses
    {
        /** @var \core\query\components: table name  */
        private $_table;

        /** @var \core\query\components[]  */
        private $_joins = array();

        /**
         * @param \core\query\components $table
         */
        public function setTable(\core\query\components $table)
        {
            $this->_table = $table;
        }

        /**
         * @param \core\query\components $join
         */
        public function addJoin(\core\query\components $join)
        {
            $this->_joins[] = $join;
        }

        /**
         * @return string
         */
        public function output()
        {
            $result = " ";

            if ($this->_table) {
                $result .= $this->getFromOutput();
                $result .= $this->getTableOutput();
                $result .= $this->getJoinsOutput();
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
        private function getTableOutput()
        {
            $result = "";

            if ($this->_table) {
                $result .= $this->_table->output();
                $result .= " ";
            }

            return $result;
        }

        /**
         * @return string
         */
        private function getJoinsOutput()
        {
            $result = "";

            foreach ($this->_joins as $join) {
                $result .= $join->output();
                $result .= " ";
            }

            return $result;
        }
    }
}